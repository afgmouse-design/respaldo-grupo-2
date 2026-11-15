<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Localidad (tabla `localidad`).
 *
 * Propósito: Último nivel de la Estructura Geográfica (ciudad, caserío o sector).
 * Pertenece a una parroquia (`par_id`).
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('localidad') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Localidad extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'localidad';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_loc';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Localidad';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_loc',
        'par_id',
        'status',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos. */
    protected $casts = [
        'status' => 'boolean',
    ];

    /** Atributos adicionales anexados al array / json. */
    protected $appends = [
        'est_loc',
        'puede_activarse',
    ];

    /**
     * Accesor para compatibilidad con convención est_loc.
     */
    public function getEstLocAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre eliminando números y aplicando Title Case.
     */
    public function setNombreLocAttribute($value): void
    {
        $this->attributes['nombre_loc'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Determina si la localidad puede activarse según el estatus de sus ancestros (Parroquia, Municipio, Estado, País).
     */
    public function getPuedeActivarseAttribute(): bool
    {
        $ancestro = DB::table('parroquia')
            ->join('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('parroquia.id_par', $this->par_id)
            ->select(
                'parroquia.status as parroquia_status',
                'municipio.status as municipio_status',
                'estado.status as estado_status',
                'pais.status as pais_status'
            )
            ->first();

        if (!$ancestro) {
            return false;
        }

        return (bool) ($ancestro->parroquia_status && $ancestro->municipio_status && $ancestro->estado_status && $ancestro->pais_status);
    }

    /**
     * Parroquia a la que pertenece esta localidad (Relación Eloquent).
     */
    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(Parroquia::class, 'par_id', 'id_par');
    }

    /**
     * Scope: solo localidades activas.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS DE QUERY BUILDER PURO (DB::table)
    // =========================================================================

    /**
     * 1. Consulta paginada con filtros dinámicos mediante Query Builder puro.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $mun_id = $filtros['mun_id'] ?? null;
        $par_id = $filtros['par_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $query = DB::table('localidad')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'localidad.*',
                'parroquia.id_par as parroquia_id_par',
                'parroquia.nombre_par as parroquia_nombre_par',
                'parroquia.mun_id as parroquia_mun_id',
                'parroquia.status as parroquia_status',
                'municipio.id_mun as municipio_id_mun',
                'municipio.nombre_mun as municipio_nombre_mun',
                'municipio.status as municipio_status',
                'estado.status as estado_status',
                'pais.status as pais_status'
            )
            ->when($status !== null && $status !== '' && $status !== 'all', function ($q) use ($status) {
                $q->where('localidad.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('localidad.nombre_loc', 'ilike', "%{$search}%")
                  ->orWhere('parroquia.nombre_par', 'ilike', "%{$search}%")
                  ->orWhere('municipio.nombre_mun', 'ilike', "%{$search}%");
            });
        }

        if (!empty($par_id)) {
            $query->where('localidad.par_id', (int) $par_id);
        }

        if (!empty($mun_id)) {
            $query->where('parroquia.mun_id', (int) $mun_id);
        }

        $paginator = $query->orderBy('localidad.id_loc', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo y estructuración de datos para compatibilidad con props de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->status = (bool) $item->status;
            $item->est_loc = (bool) $item->status;
            $item->puede_activarse = (bool) (
                ($item->parroquia_status ?? false) &&
                ($item->municipio_status ?? false) &&
                ($item->estado_status ?? false) &&
                ($item->pais_status ?? false)
            );

            $item->parroquia = $item->parroquia_id_par ? (object) [
                'id_par'     => $item->parroquia_id_par,
                'nombre_par' => $item->parroquia_nombre_par,
                'mun_id'     => $item->parroquia_mun_id,
                'municipio'  => $item->municipio_id_mun ? (object) [
                    'id_mun'     => $item->municipio_id_mun,
                    'nombre_mun' => $item->municipio_nombre_mun,
                ] : null,
            ] : null;

            unset(
                $item->parroquia_id_par,
                $item->parroquia_nombre_par,
                $item->parroquia_mun_id,
                $item->parroquia_status,
                $item->municipio_id_mun,
                $item->municipio_nombre_mun,
                $item->municipio_status,
                $item->estado_status,
                $item->pais_status
            );

            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra una nueva localidad mediante Query Builder puro.
     */
    public static function registrarLocalidad(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_loc' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_loc'])), MB_CASE_TITLE, 'UTF-8'),
            'par_id'     => (int) $datos['par_id'],
            'status'     => true,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('localidad')->insertGetId($insertData, 'id_loc');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarLocalidad para compatibilidad.
     */
    public static function crearLocalidad(array $datos, ?int $userId = null): object
    {
        return static::registrarLocalidad($datos, $userId);
    }

    /**
     * 3. Actualiza una localidad existente mediante Query Builder puro.
     */
    public static function actualizarLocalidad(int $id_loc, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (isset($datos['nombre_loc'])) {
            $updateData['nombre_loc'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_loc'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['par_id'])) {
            $updateData['par_id'] = (int) $datos['par_id'];
        }

        if (isset($datos['status'])) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('localidad')
            ->where('id_loc', $id_loc)
            ->update($updateData);

        return static::obtenerPorId($id_loc);
    }

    /**
     * 4. Busca una localidad por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_loc): object
    {
        $localidad = DB::table('localidad')->where('id_loc', $id_loc)->first();

        if (!$localidad) {
            abort(404, 'Localidad no encontrada.');
        }

        $localidad->status = (bool) $localidad->status;
        $localidad->est_loc = (bool) $localidad->status;

        return $localidad;
    }

    /**
     * Obtiene una localidad con su parroquia padre para la vista de edición.
     */
    public static function obtenerParaEditar(int $id_loc): object
    {
        $localidad = static::obtenerPorId($id_loc);

        $localidad->parroquia = DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id', 'status'])
            ->where('id_par', $localidad->par_id)
            ->first();

        return $localidad;
    }

    /**
     * Obtiene el detalle de una localidad con su jerarquía ascendente completa mediante Query Builder puro.
     */
    public static function obtenerConRelaciones(int $id_loc): object
    {
        $localidad = static::obtenerPorId($id_loc);

        $parroquia = DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id', 'status'])
            ->where('id_par', $localidad->par_id)
            ->first();

        if ($parroquia) {
            $municipio = DB::table('municipio')
                ->select(['id_mun', 'nombre_mun', 'esd_id', 'status'])
                ->where('id_mun', $parroquia->mun_id)
                ->first();

            if ($municipio) {
                $estado = DB::table('estado')
                    ->select(['id_esd', 'nombre_esd', 'pai_id', 'status'])
                    ->where('id_esd', $municipio->esd_id)
                    ->first();

                if ($estado) {
                    $estado->pais = DB::table('pais')
                        ->select(['id_pai', 'nombre_pai', 'status'])
                        ->where('id_pai', $estado->pai_id)
                        ->first();
                }

                $municipio->estado = $estado;
            }

            $parroquia->municipio = $municipio;
        }

        $localidad->parroquia = $parroquia;

        return $localidad;
    }

    /**
     * Obtiene localidades activas para llenar selectores dependientes de una parroquia mediante Query Builder puro.
     */
    public static function obtenerActivosPorParroquia(int $par_id): Collection
    {
        return DB::table('localidad')
            ->select(['id_loc', 'nombre_loc', 'par_id'])
            ->where('par_id', $par_id)
            ->where('status', true)
            ->orderBy('nombre_loc', 'asc')
            ->get();
    }

    /**
     * Obtiene todas las localidades activas con su jerarquía territorial mediante Query Builder puro.
     */
    public static function obtenerActivosConJerarquia(): Collection
    {
        $localidades = DB::table('localidad')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('localidad.status', true)
            ->select(
                'localidad.id_loc',
                'localidad.nombre_loc',
                'localidad.par_id',
                'parroquia.id_par as parroquia_id_par',
                'parroquia.nombre_par as parroquia_nombre_par',
                'parroquia.mun_id as parroquia_mun_id',
                'municipio.id_mun as municipio_id_mun',
                'municipio.nombre_mun as municipio_nombre_mun',
                'municipio.esd_id as municipio_esd_id',
                'estado.id_esd as estado_id_esd',
                'estado.nombre_esd as estado_nombre_esd',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai'
            )
            ->orderBy('localidad.nombre_loc', 'asc')
            ->get();

        return $localidades->map(function ($item) {
            return (object) [
                'id_loc'     => $item->id_loc,
                'nombre_loc' => $item->nombre_loc,
                'par_id'     => $item->par_id,
                'parroquia'  => $item->parroquia_id_par ? (object) [
                    'id_par'     => $item->parroquia_id_par,
                    'nombre_par' => $item->parroquia_nombre_par,
                    'mun_id'     => $item->parroquia_mun_id,
                    'municipio'  => $item->municipio_id_mun ? (object) [
                        'id_mun'     => $item->municipio_id_mun,
                        'nombre_mun' => $item->municipio_nombre_mun,
                        'esd_id'     => $item->municipio_esd_id,
                        'estado'     => $item->estado_id_esd ? (object) [
                            'id_esd'     => $item->estado_id_esd,
                            'nombre_esd' => $item->estado_nombre_esd,
                            'pais'       => $item->pais_id_pai ? (object) [
                                'id_pai'     => $item->pais_id_pai,
                                'nombre_pai' => $item->pais_nombre_pai,
                            ] : null,
                        ] : null,
                    ] : null,
                ] : null,
            ];
        });
    }

    /**
     * Alterna el estatus lógico de la localidad.
     * Al desactivar: no hay cascada descendente (es el último nivel).
     * Al activar: valida con JOINs que Parroquia, Municipio, Estado y País estén activos.
     */
    public static function alternarEstatus(int $id_loc, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_loc, $userId) {
            $localidad = static::obtenerPorId($id_loc);
            $nuevoEstatus = !$localidad->status;
            $usuarioId = $userId ?? auth()->id();

            // Validación jerárquica con JOIN: Si se intenta activar, verificar toda la cadena de ancestros
            if ($nuevoEstatus) {
                $ancestro = DB::table('parroquia')
                    ->join('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
                    ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
                    ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
                    ->where('parroquia.id_par', $localidad->par_id)
                    ->select(
                        'parroquia.status as parroquia_status',
                        'municipio.status as municipio_status',
                        'estado.status as estado_status',
                        'pais.status as pais_status'
                    )
                    ->first();

                if (!$ancestro || !$ancestro->parroquia_status || !$ancestro->municipio_status || !$ancestro->estado_status || !$ancestro->pais_status) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            DB::table('localidad')
                ->where('id_loc', $id_loc)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            return [
                'nombre'  => $localidad->nombre_loc,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Alias de alternarEstatus para consistencia con los demás modelos.
     */
    public static function alternarEstatusConCascada(int $id_loc, ?int $userId = null): array
    {
        return static::alternarEstatus($id_loc, $userId);
    }

    /**
     * Realiza la baja lógica de la localidad apagando su estatus (status = false).
     * Como es el nivel hoja final, no tiene entidades subordinadas.
     */
    public static function desactivar(int $id_loc, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_loc, $userId) {
            $localidad = static::obtenerPorId($id_loc);
            $usuarioId = $userId ?? auth()->id();

            DB::table('localidad')
                ->where('id_loc', $id_loc)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            return [
                'nombre'  => $localidad->nombre_loc,
                'estatus' => false,
            ];
        });
    }

    /**
     * Alias de desactivar para consistencia con los otros 4 modelos.
     */
    public static function desactivarConCascada(int $id_loc, ?int $userId = null): array
    {
        return static::desactivar($id_loc, $userId);
    }
}
