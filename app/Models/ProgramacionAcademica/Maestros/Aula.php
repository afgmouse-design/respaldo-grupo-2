<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Aula (tabla `aula`).
 *
 * Propósito: ambiente físico de una sede (código, capacidad, tipo de espacio), clasificado por TipoAula.
 * Baja lógica mediante `status` (bool) y `status_id` (1 activo, 2 inactivo).
 */
class Aula extends Model
{
    use RegistraEnBitacora;

    protected $table = 'aula';

    protected $primaryKey = 'id_aul';

    protected $moduloAuditoria = 'Aula';

    protected $fillable = [
        'codigo_aula_aul',
        'capacidad_maxima_aul',
        'tipo_espacio_aul',
        'sed_id',
        'tia_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_aul'               => 'integer',
        'capacidad_maxima_aul' => 'integer',
        'sed_id'               => 'integer',
        'tia_id'               => 'integer',
        'status'               => 'boolean',
        'status_id'            => 'integer',
    ];

    protected $appends = [
        'nombre_aul',
        'capacidad_aul',
    ];

    /**
     * Alias para compatibilidad de vistas y reportes.
     */
    public function getNombreAulAttribute(): ?string
    {
        return $this->codigo_aula_aul;
    }

    public function getCapacidadAulAttribute(): ?int
    {
        return $this->capacidad_maxima_aul;
    }

    /**
     * Relación con la Sede física del aula.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    /**
     * Relación con el Tipo de Aula (clasificación física).
     */
    public function tipoAula(): BelongsTo
    {
        return $this->belongsTo(TipoAula::class, 'tia_id', 'id_tia');
    }

    /**
     * Listado paginado con filtros (search, status, sed_id, tia_id). Por defecto solo activos.
     * Expone `id` (alias de id_aul), `nombre_sed` y `nombre_tia`.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? null;
        $sedId  = $filtros['sed_id'] ?? null;
        $tiaId  = $filtros['tia_id'] ?? null;

        return DB::table('aula')
            ->leftJoin('sede', 'sede.id_sed', '=', 'aula.sed_id')
            ->leftJoin('tipo_aula', 'tipo_aula.id_tia', '=', 'aula.tia_id')
            ->select('aula.*', 'aula.id_aul as id', 'sede.nombre_sed', 'tipo_aula.nombre_tia')
            ->whereNull('aula.deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', fn ($q) => $q->where('aula.status', filter_var($status, FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($sedId) && $sedId !== 'all', fn ($q) => $q->where('aula.sed_id', $sedId))
            ->when(!empty($tiaId) && $tiaId !== 'all', fn ($q) => $q->where('aula.tia_id', $tiaId))
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('aula.codigo_aula_aul', 'ilike', "%{$search}%")
                      ->orWhere('aula.tipo_espacio_aul', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('aula.id_aul', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija status_id (1 = activo, 2 = inactivo) y sincroniza `status`.
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = static::query()->where('id_aul', $id)->firstOrFail();

        return $registro->update([
            'status_id'  => $estado,
            'status'     => $estado === 1,
            'updated_id' => $userId ?? auth()->id(),
        ]);
    }
}

