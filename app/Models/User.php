<?php

namespace App\Models;

use App\Models\Concerns\UsesStatusCatalog;
use App\Models\Personas\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Modelo: Usuario del Sistema (tabla `users`).
 *
 * Propósito: Autenticación multicapa (Laravel Fortify), control de acceso por contexto
 * (`rol_usuario_contexto`, `usuario_permiso`) y vinculación 1:1 con `persona` (`use_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` reemplazando `name` por `username`, agregando `SoftDeletes`,
 *    campos de seguridad/bloqueo (`status_id`, `estatus`, `intentos_fallidos`, `bloqueado_hasta`),
 *    relación `persona()` y verificación RBAC sobre `rol_usuario_contexto` y `usuario_permiso`.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes, UsesStatusCatalog;

    protected $table = 'users';

    /**
     * Atributos asignables masivamente según `sogac_mio.sql`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'estatus',
        'status_id',
        'intentos_fallidos',
        'bloqueado_hasta',
        'ultimo_login',
        'debe_cambiar_password',
        'password_temporal_expira_en',
        'created_id',
        'updated_id',
    ];

    /**
     * Atributos ocultos en serialización.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'two_factor_confirmed_at'     => 'datetime',
            'bloqueado_hasta'             => 'datetime',
            'ultimo_login'                => 'datetime',
            'password_temporal_expira_en' => 'datetime',
            'estatus'                     => 'boolean',
            'debe_cambiar_password'       => 'boolean',
            'intentos_fallidos'           => 'integer',
            'status_id'                   => 'integer',
        ];
    }

    /**
     * Persona asociada a la cuenta de usuario (`persona.use_id` -> `users.id`).
     */
    public function persona(): HasOne
    {
        return $this->hasOne(Persona::class, 'use_id', 'id');
    }

    /**
     * Verifica que la cuenta no esté inactiva, eliminada ni bloqueada temporalmente.
     */
    public function estaActivaYDesbloqueada(): bool
    {
        if ((int) ($this->status_id ?? self::STATUS_ACTIVO) !== self::STATUS_ACTIVO) {
            return false;
        }

        if ($this->estatus === false) {
            return false;
        }

        if ($this->deleted_at !== null) {
            return false;
        }

        if ($this->bloqueado_hasta !== null && $this->bloqueado_hasta->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Determina si el usuario posee al menos un rol Super Administrador activo en `rol_usuario_contexto`.
     */
    public function isSuperAdmin(): bool
    {
        if (! $this->estaActivaYDesbloqueada()) {
            return false;
        }

        $hoy = now()->toDateString();

        return DB::table('rol_usuario_contexto as ruc')
            ->join('rol as r', 'r.id_rol', '=', 'ruc.rol_id')
            ->where('ruc.use_id', $this->id)
            ->where('ruc.status_id', self::STATUS_ACTIVO)
            ->whereNull('ruc.deleted_at')
            ->where(fn ($q) => $q->whereNull('ruc.fecha_inicio_ruc')->orWhere('ruc.fecha_inicio_ruc', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('ruc.fecha_fin_ruc')->orWhere('ruc.fecha_fin_ruc', '>=', $hoy))
            ->where('r.status_id', self::STATUS_ACTIVO)
            ->whereNull('r.deleted_at')
            ->where('r.es_super_admin_rol', true)
            ->exists();
    }

    /**
     * Evalúa autorización jerárquica: cuenta activa -> SuperAdmin -> revokes directos -> grants directos -> roles en contexto.
     */
    public function hasPermissionTo(string $clavePermiso): bool
    {
        if (! $this->estaActivaYDesbloqueada()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        $hoy = now()->toDateString();

        $permisoId = DB::table('permiso')
            ->where('clave_pmi', $clavePermiso)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->value('id_pmi');

        if (! $permisoId) {
            return false;
        }

        $tieneRevoke = DB::table('usuario_permiso')
            ->where('use_id', $this->id)
            ->where('pmi_id', $permisoId)
            ->where('tipo_usp', 'revoke')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('fecha_inicio_usp')->orWhere('fecha_inicio_usp', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('fecha_fin_usp')->orWhere('fecha_fin_usp', '>=', $hoy))
            ->exists();

        if ($tieneRevoke) {
            return false;
        }

        $tieneGrant = DB::table('usuario_permiso')
            ->where('use_id', $this->id)
            ->where('pmi_id', $permisoId)
            ->where('tipo_usp', 'grant')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('fecha_inicio_usp')->orWhere('fecha_inicio_usp', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('fecha_fin_usp')->orWhere('fecha_fin_usp', '>=', $hoy))
            ->exists();

        if ($tieneGrant) {
            return true;
        }

        return DB::table('rol_usuario_contexto as ruc')
            ->join('rol as r', 'r.id_rol', '=', 'ruc.rol_id')
            ->join('rol_permiso as rp', 'rp.rol_id', '=', 'r.id_rol')
            ->where('ruc.use_id', $this->id)
            ->where('ruc.status_id', self::STATUS_ACTIVO)
            ->whereNull('ruc.deleted_at')
            ->where(fn ($q) => $q->whereNull('ruc.fecha_inicio_ruc')->orWhere('ruc.fecha_inicio_ruc', '<=', $hoy))
            ->where(fn ($q) => $q->whereNull('ruc.fecha_fin_ruc')->orWhere('ruc.fecha_fin_ruc', '>=', $hoy))
            ->where('r.status_id', self::STATUS_ACTIVO)
            ->whereNull('r.deleted_at')
            ->where('rp.pmi_id', $permisoId)
            ->where('rp.status_id', self::STATUS_ACTIVO)
            ->whereNull('rp.deleted_at')
            ->exists();
    }

    /**
     * Evalúa si el usuario posee al menos uno de los permisos indicados.
     *
     * @param  array<int, string>|string  ...$permisos
     */
    public function hasAnyPermission(array|string ...$permisos): bool
    {
        $lista = count($permisos) === 1 && is_array($permisos[0]) ? $permisos[0] : $permisos;

        foreach ($lista as $permiso) {
            if (is_string($permiso) && $this->hasPermissionTo($permiso)) {
                return true;
            }
        }

        return false;
    }
}
