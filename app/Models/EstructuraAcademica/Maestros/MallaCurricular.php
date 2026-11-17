<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Malla Curricular (tabla `malla_curricular`).
 *
 * Propósito: Gestiona los diseños curriculares asociados a un Programa (`prg_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`prg_id -> programa.id_prg`, totales `total_credito_*`,
 *    `total_horas_*`, `total_unidades_*`, extensiones aditivas `version_pensum_mac` y `observacion_mac`,
 *    `status_id` y `SoftDeletes`, sin `pnf_id` ni `status` booleano).
 *  - 2026-11-16: Añadido `eliminarConSoftDelete` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    hacia `malla_curricular_detalle`.
 */
class MallaCurricular extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'malla_curricular';
    protected $primaryKey = 'id_mac';
    protected $moduloAuditoria = 'MallaCurricular';

    protected $fillable = [
        'prg_id',
        'tra_inicio_id',
        'salida_tsu_sem_id',
        'salida_ing_sem_id',
        'nombre_mac',
        'codigo_gaceta_mac',
        'fecha_aprobacion_mac',
        'version_pensum_mac',
        'observacion_mac',
        'total_credito_tsu_mac',
        'total_credito_ing_mac',
        'total_horas_tsu_mac',
        'total_horas_ing_mac',
        'total_unidades_tsu_mac',
        'total_unidades_ing_mac',
        'total_credito_prosecucion_mac',
        'total_horas_prosecucion_mac',
        'total_unidades_prosecucion_mac',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_mac'                         => 'integer',
        'prg_id'                         => 'integer',
        'tra_inicio_id'                  => 'integer',
        'salida_tsu_sem_id'              => 'integer',
        'salida_ing_sem_id'              => 'integer',
        'fecha_aprobacion_mac'           => 'date',
        'total_credito_tsu_mac'          => 'integer',
        'total_credito_ing_mac'          => 'integer',
        'total_horas_tsu_mac'            => 'integer',
        'total_horas_ing_mac'            => 'integer',
        'total_unidades_tsu_mac'         => 'integer',
        'total_unidades_ing_mac'         => 'integer',
        'total_credito_prosecucion_mac'  => 'integer',
        'total_horas_prosecucion_mac'    => 'integer',
        'total_unidades_prosecucion_mac' => 'integer',
        'status_id'                      => 'integer',
    ];

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'prg_id', 'id_prg');
    }

    public function trayectoInicio(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class, 'tra_inicio_id', 'id_tra');
    }

    public function salidaTsuSemestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'salida_tsu_sem_id', 'id_sem');
    }

    public function salidaIngSemestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'salida_ing_sem_id', 'id_sem');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(MallaCurricularDetalle::class, 'mac_id', 'id_mac');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'mac_id', 'id_mac');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array|string|null $busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $statusId = static::resolverFiltroStatusId(is_array($busqueda) ? ($busqueda['status'] ?? $busqueda['status_id'] ?? null) : null);

        $query = static::with('programa')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_mac', 'ilike', "%{$search}%")
                    ->orWhere('codigo_gaceta_mac', 'ilike', "%{$search}%")
                    ->orWhereHas('programa', function ($qp) use ($search) {
                        $qp->where('nombre_prg', 'ilike', "%{$search}%")
                            ->orWhere('codigo_prg', 'ilike', "%{$search}%")
                            ->orWhere('codigo_ministerio_prg', 'ilike', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('id_mac', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO): bool
    {
        $registro = static::where('id_mac', $id)->first();
        if (! $registro) {
            return false;
        }

        if ($estado === self::STATUS_ACTIVO) {
            $programa = Programa::where('id_prg', $registro->prg_id)->first();
            if ($programa && (int) $programa->status_id !== self::STATUS_ACTIVO) {
                throw new \DomainException("No se puede activar esta malla curricular porque su programa '{$programa->nombre_prg}' se encuentra inactivo.");
            }
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'updated_id' => auth()->id(),
        ]);
    }

    /**
     * Elimina lógicamente la malla curricular y sus detalles (`deleted_at` + `status_id = 2`)
     * preservando los registros en PostgreSQL.
     */
    public static function eliminarConSoftDelete(int $id, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id, $userId) {
            $malla = static::where('id_mac', $id)->firstOrFail();
            $nombre = $malla->nombre_mac;

            static::ejecutarBajaLogicaConHijos(
                $malla,
                [
                    ['table' => 'malla_curricular_detalle', 'pk' => 'id_mcd', 'fk' => 'mac_id'],
                ],
                $userId,
                self::STATUS_INACTIVO
            );

            return [
                'nombre'    => $nombre,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }
}
