<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Semestre (tabla `semestre`).
 *
 * Propósito: Subdivisión temporal perteneciente a un trayecto (`tra_id`) en `sogac_mio.sql`.
 *
 * Historial:
 *  - 2026-11-14: Creación reemplazando el modelo legacy `Semestres` (`semestres`).
 *  - 2026-11-16: Incorporado `HandlesSoftDeleteWithStatus` para eliminación lógica en interfaz.
 */
class Semestre extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'semestre';
    protected $primaryKey = 'id_sem';
    protected $moduloAuditoria = 'Semestre';

    protected $fillable = [
        'tra_id',
        'codigo_sem',
        'nombre_sem',
        'numero_sem',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sem'     => 'integer',
        'tra_id'     => 'integer',
        'numero_sem' => 'integer',
        'status_id'  => 'integer',
    ];

    public function trayecto(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class, 'tra_id', 'id_tra');
    }

    public function mallasSalidaTsu(): HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'salida_tsu_sem_id', 'id_sem');
    }

    public function mallasSalidaIng(): HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'salida_ing_sem_id', 'id_sem');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerActivosParaSelect(?int $traId = null): Collection
    {
        return DB::table('semestre')
            ->select(['id_sem', 'tra_id', 'codigo_sem', 'nombre_sem', 'numero_sem'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->when($traId !== null, fn ($q) => $q->where('tra_id', $traId))
            ->orderBy('numero_sem', 'asc')
            ->get();
    }
}
