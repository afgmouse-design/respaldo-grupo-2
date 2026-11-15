<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Estado (tabla `estado`).
 *
 * Propósito: Representa una entidad federal (estado/provincia).
 * Pertenece a un país (`pai_id`) y agrupa múltiples municipios (`municipio.esd_id`).
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('estado') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Estado extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'estado';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_esd';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Estado';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_esd',
        'pai_id',
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
        'est_esd',
        'puede_activarse',
    ];

    /**
     * Accesor para compatibilidad con convención est_esd.
     */
    public function getEstEsdAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre eliminando números y aplicando Title Case.
     */
    public function setNombreEsdAttribute($value): void
    {
        $this->attributes['nombre_esd'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Determina si el estado puede activarse según el estatus de su país padre.
     */
    public function getPuedeActivarseAttribute(): bool
    {
        if ($this->relationLoaded('pais') && $this->pais) {
            return (bool) $this->pais->status;
        }

        return (bool) DB::table('pais')->where('id_pai', $this->pai_id)->value('status');
    }

    /**
     * Relación con País (Eloquent para compatibilidad externa).
     */
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pai_id', 'id_pai');
    }

    /**
     * Municipios que pertenecen a este estado (Relación Eloquent).
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'esd_id', 'id_esd');
    }

    /**
     * Scope: solo estados activos.
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
        $pai_id = $filtros['pai_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $paginator = DB::table('estado')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'estado.*',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status as pais_status'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM municipio WHERE municipio.esd_id = estado.id_esd) as municipios_count')
            ->when($status !== null && $status !== '' && $status !== 'all', function ($query) use ($status) {
                $query->where('estado.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('estado.nombre_esd', 'ilike', "%{$search}%")
                      ->orWhere('pais.nombre_pai', 'ilike', "%{$search}%");
                });
            })
            ->when(!empty($pai_id), function ($query) use ($pai_id) {
                $query->where('estado.pai_id', (int) $pai_id);
            })
            ->orderBy('estado.id_esd', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo de datos para compatibilidad con props de Inertia en el frontend
        $paginator->getCollection()->transform(function ($item) {
            $item->est_esd = (bool) $item->status;
            $item->puede_activarse = (bool) ($item->pais_status ?? false);
            $item->pais = $item->pais_id_pai ? (object) [
                'id_pai'     => $item->pais_id_pai,
                'nombre_pai' => $item->pais_nombre_pai,
                'status'     => (bool) $item->pais_status,
            ] : null;

            unset($item->pais_id_pai, $item->pais_nombre_pai, $item->pais_status);
            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra un nuevo estado mediante Query Builder puro.
     */
    public static function registrarEstado(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_esd' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_esd'])), MB_CASE_TITLE, 'UTF-8'),
            'pai_id'     => (int) $datos['pai_id'],
            'status'     => true,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('estado')->insertGetId($insertData, 'id_esd');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarEstado para compatibilidad.
     */
    public static function crearEstado(array $datos, ?int $userId = null): object
    {
        return static::registrarEstado($datos, $userId);
    }

    /**
     * 3. Actualiza un estado existente mediante Query Builder puro.
     */
    public static function actualizarEstado(int $id_esd, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (isset($datos['nombre_esd'])) {
            $updateData['nombre_esd'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_esd'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['pai_id'])) {
            $updateData['pai_id'] = (int) $datos['pai_id'];
        }

        if (isset($datos['status'])) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('estado')
            ->where('id_esd', $id_esd)
            ->update($updateData);

        return static::obtenerPorId($id_esd);
    }

    /**
     * 4. Busca un estado por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_esd): object
    {
        $estado = DB::table('estado')->where('id_esd', $id_esd)->first();

        if (!$estado) {
            abort(404, 'Estado no encontrado.');
        }

        return $estado;
    }

    /**
     * Obtiene el detalle de un estado con su país y municipios subordinados mediante Query Builder puro.
     */
    public static function obtenerConRelaciones(int $id_esd): object
    {
        $estado = static::obtenerPorId($id_esd);

        $estado->pais = DB::table('pais')
            ->select(['id_pai', 'nombre_pai'])
            ->where('id_pai', $estado->pai_id)
            ->first();

        $estado->municipios = DB::table('municipio')
            ->select('municipio.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM parroquia WHERE parroquia.mun_id = municipio.id_mun) as parroquias_count')
            ->where('esd_id', $id_esd)
            ->orderBy('nombre_mun', 'asc')
            ->get();

        return $estado;
    }

    /**
     * Obtiene estados activos para llenar selectores dependientes de un país mediante Query Builder puro.
     */
    public static function obtenerActivosPorPais(int $pai_id): \Illuminate\Support\Collection
    {
        return DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'pai_id'])
            ->where('pai_id', $pai_id)
            ->where('status', true)
            ->orderBy('nombre_esd', 'asc')
            ->get();
    }

    /**
     * Obtiene todos los estados activos para selectores con Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): \Illuminate\Support\Collection
    {
        return DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'pai_id'])
            ->where('status', true)
            ->orderBy('nombre_esd', 'asc')
            ->get();
    }

    /**
     * Alterna el estatus lógico del estado y propaga en cascada mediante Query Builder puro.
     */
    public static function alternarEstatusConCascada(int $id_esd, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esd, $userId) {
            $estado = static::obtenerPorId($id_esd);
            $nuevoEstatus = !$estado->status;
            $usuarioId = $userId ?? auth()->id();

            // Validación jerárquica: Si se intenta activar, verificar que el país padre esté activo
            if ($nuevoEstatus) {
                $paisActivo = DB::table('pais')->where('id_pai', $estado->pai_id)->value('status');
                if (!$paisActivo) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            // 1. Actualizar Estado
            DB::table('estado')
                ->where('id_esd', $id_esd)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener IDs de Municipios dependientes
            $municipioIds = DB::table('municipio')
                ->where('esd_id', $estado->id_esd)
                ->pluck('id_mun');

            if ($municipioIds->isNotEmpty()) {
                // Actualizar Municipios
                DB::table('municipio')
                    ->whereIn('id_mun', $municipioIds)
                    ->update([
                        'status'     => $nuevoEstatus,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Obtener IDs de Parroquias dependientes
                $parroquiaIds = DB::table('parroquia')
                    ->whereIn('mun_id', $municipioIds)
                    ->pluck('id_par');

                if ($parroquiaIds->isNotEmpty()) {
                    // Actualizar Parroquias
                    DB::table('parroquia')
                        ->whereIn('id_par', $parroquiaIds)
                        ->update([
                            'status'     => $nuevoEstatus,
                            'updated_id' => $usuarioId,
                        ]);

                    // 4. Actualizar Localidades dependientes
                    DB::table('localidad')
                        ->whereIn('par_id', $parroquiaIds)
                        ->update([
                            'status'     => $nuevoEstatus,
                            'updated_id' => $usuarioId,
                        ]);
                }
            }

            return [
                'nombre'  => $estado->nombre_esd,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Realiza la baja lógica del estado apagando (status = false) en cascada a todos sus descendientes.
     * Cascada: Estado -> Municipios -> Parroquias -> Localidades.
     */
    public static function desactivarConCascada(int $id_esd, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esd, $userId) {
            $estado = static::obtenerPorId($id_esd);
            $usuarioId = $userId ?? auth()->id();

            // 1. Apagar Estado
            DB::table('estado')
                ->where('id_esd', $id_esd)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener IDs de Municipios dependientes
            $municipioIds = DB::table('municipio')
                ->where('esd_id', $estado->id_esd)
                ->pluck('id_mun');

            if ($municipioIds->isNotEmpty()) {
                // Apagar Municipios
                DB::table('municipio')
                    ->whereIn('id_mun', $municipioIds)
                    ->update([
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Obtener IDs de Parroquias dependientes
                $parroquiaIds = DB::table('parroquia')
                    ->whereIn('mun_id', $municipioIds)
                    ->pluck('id_par');

                if ($parroquiaIds->isNotEmpty()) {
                    // Apagar Parroquias
                    DB::table('parroquia')
                        ->whereIn('id_par', $parroquiaIds)
                        ->update([
                            'status'     => false,
                            'updated_id' => $usuarioId,
                        ]);

                    // 4. Apagar Localidades dependientes
                    DB::table('localidad')
                        ->whereIn('par_id', $parroquiaIds)
                        ->update([
                            'status'     => false,
                            'updated_id' => $usuarioId,
                        ]);
                }
            }

            return [
                'nombre'  => $estado->nombre_esd,
                'estatus' => false,
            ];
        });
    }
}
