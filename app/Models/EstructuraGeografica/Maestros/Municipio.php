<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Municipio (tabla `municipio`).
 *
 * Propósito: Representa una división municipal. Pertenece a un estado (`esd_id`)
 * y agrupa múltiples parroquias (`parroquia.mun_id`).
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('municipio') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Municipio extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'municipio';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_mun';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Municipio';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_mun',
        'esd_id',
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
        'est_mun',
        'puede_activarse',
    ];

    /**
     * Accesor para compatibilidad con convención est_mun.
     */
    public function getEstMunAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre eliminando números y aplicando Title Case.
     */
    public function setNombreMunAttribute($value): void
    {
        $this->attributes['nombre_mun'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Determina si el municipio puede activarse según el estatus de su estado y país ancestros.
     */
    public function getPuedeActivarseAttribute(): bool
    {
        $estado = DB::table('estado')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('estado.id_esd', $this->esd_id)
            ->select('estado.status as estado_status', 'pais.status as pais_status')
            ->first();

        if (!$estado) {
            return false;
        }

        return (bool) ($estado->estado_status && $estado->pais_status);
    }

    /**
     * Relación con Estado (Eloquent para compatibilidad externa).
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'esd_id', 'id_esd');
    }

    /**
     * Parroquias que pertenecen a este municipio (Relación Eloquent).
     */
    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class, 'mun_id', 'id_mun');
    }

    /**
     * Scope: solo municipios activos.
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
        $esd_id = $filtros['esd_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $query = DB::table('municipio')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'municipio.*',
                'estado.id_esd as estado_id_esd',
                'estado.nombre_esd as estado_nombre_esd',
                'estado.pai_id as estado_pai_id',
                'estado.status as estado_status',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status as pais_status'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM parroquia WHERE parroquia.mun_id = municipio.id_mun) as parroquias_count')
            ->when($status !== null && $status !== '' && $status !== 'all', function ($q) use ($status) {
                $q->where('municipio.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('municipio.nombre_mun', 'ilike', "%{$search}%")
                  ->orWhere('estado.nombre_esd', 'ilike', "%{$search}%")
                  ->orWhere('pais.nombre_pai', 'ilike', "%{$search}%");
            });
        }

        if (!empty($esd_id)) {
            $query->where('municipio.esd_id', (int) $esd_id);
        }

        if (!empty($pai_id)) {
            $query->where('estado.pai_id', (int) $pai_id);
        }

        $paginator = $query->orderBy('municipio.id_mun', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo y estructuración de datos para compatibilidad con props de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->status = (bool) $item->status;
            $item->est_mun = (bool) $item->status;
            $item->puede_activarse = (bool) (($item->estado_status ?? false) && ($item->pais_status ?? false));

            $item->estado = $item->estado_id_esd ? (object) [
                'id_esd'     => $item->estado_id_esd,
                'nombre_esd' => $item->estado_nombre_esd,
                'pai_id'     => $item->estado_pai_id,
                'status'     => (bool) $item->estado_status,
                'pais'       => $item->pais_id_pai ? (object) [
                    'id_pai'     => $item->pais_id_pai,
                    'nombre_pai' => $item->pais_nombre_pai,
                    'status'     => (bool) $item->pais_status,
                ] : null,
            ] : null;

            unset(
                $item->estado_id_esd,
                $item->estado_nombre_esd,
                $item->estado_pai_id,
                $item->estado_status,
                $item->pais_id_pai,
                $item->pais_nombre_pai,
                $item->pais_status
            );

            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra un nuevo municipio mediante Query Builder puro.
     */
    public static function registrarMunicipio(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_mun' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_mun'])), MB_CASE_TITLE, 'UTF-8'),
            'esd_id'     => (int) $datos['esd_id'],
            'status'     => true,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('municipio')->insertGetId($insertData, 'id_mun');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarMunicipio para compatibilidad.
     */
    public static function crearMunicipio(array $datos, ?int $userId = null): object
    {
        return static::registrarMunicipio($datos, $userId);
    }

    /**
     * 3. Actualiza un municipio existente mediante Query Builder puro.
     */
    public static function actualizarMunicipio(int $id_mun, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (isset($datos['nombre_mun'])) {
            $updateData['nombre_mun'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_mun'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['esd_id'])) {
            $updateData['esd_id'] = (int) $datos['esd_id'];
        }

        if (isset($datos['status'])) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('municipio')
            ->where('id_mun', $id_mun)
            ->update($updateData);

        return static::obtenerPorId($id_mun);
    }

    /**
     * 4. Busca un municipio por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_mun): object
    {
        $municipio = DB::table('municipio')->where('id_mun', $id_mun)->first();

        if (!$municipio) {
            abort(404, 'Municipio no encontrado.');
        }

        $municipio->status = (bool) $municipio->status;
        $municipio->est_mun = (bool) $municipio->status;

        return $municipio;
    }

    /**
     * Obtiene un municipio con su estado padre para la vista de edición.
     */
    public static function obtenerParaEditar(int $id_mun): object
    {
        $municipio = static::obtenerPorId($id_mun);

        $municipio->estado = DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'pai_id', 'status'])
            ->where('id_esd', $municipio->esd_id)
            ->first();

        return $municipio;
    }

    /**
     * Obtiene el detalle de un municipio con su estado, país y parroquias subordinadas mediante Query Builder puro.
     */
    public static function obtenerConRelaciones(int $id_mun): object
    {
        $municipio = static::obtenerPorId($id_mun);

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

        $municipio->parroquias = DB::table('parroquia')
            ->select('parroquia.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM localidad WHERE localidad.par_id = parroquia.id_par) as localidades_count')
            ->where('mun_id', $id_mun)
            ->orderBy('nombre_par', 'asc')
            ->get()
            ->map(function ($p) {
                $p->status = (bool) $p->status;
                return $p;
            });

        return $municipio;
    }

    /**
     * Obtiene municipios activos para llenar selectores dependientes de un estado mediante Query Builder puro.
     */
    public static function obtenerActivosPorEstado(int $esd_id): Collection
    {
        return DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id'])
            ->where('esd_id', $esd_id)
            ->where('status', true)
            ->orderBy('nombre_mun', 'asc')
            ->get();
    }

    /**
     * Obtiene todos los municipios activos para selectores con Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id'])
            ->where('status', true)
            ->orderBy('nombre_mun', 'asc')
            ->get();
    }

    /**
     * Alterna el estatus lógico del municipio y propaga en cascada hacia parroquias y localidades subordinadas.
     */
    public static function alternarEstatusConCascada(int $id_mun, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_mun, $userId) {
            $municipio = static::obtenerPorId($id_mun);
            $nuevoEstatus = !$municipio->status;
            $usuarioId = $userId ?? auth()->id();

            // Validación jerárquica: Si se intenta activar, verificar que el estado padre y el país estén activos
            if ($nuevoEstatus) {
                $estado = DB::table('estado')
                    ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
                    ->where('estado.id_esd', $municipio->esd_id)
                    ->select('estado.status as estado_status', 'pais.status as pais_status')
                    ->first();

                if (!$estado || !$estado->estado_status || !$estado->pais_status) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            // 1. Actualizar Municipio
            DB::table('municipio')
                ->where('id_mun', $id_mun)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener IDs de Parroquias dependientes
            $parroquiaIds = DB::table('parroquia')
                ->where('mun_id', $municipio->id_mun)
                ->pluck('id_par');

            if ($parroquiaIds->isNotEmpty()) {
                // Actualizar Parroquias
                DB::table('parroquia')
                    ->whereIn('id_par', $parroquiaIds)
                    ->update([
                        'status'     => $nuevoEstatus,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Actualizar Localidades dependientes de esas parroquias
                DB::table('localidad')
                    ->whereIn('par_id', $parroquiaIds)
                    ->update([
                        'status'     => $nuevoEstatus,
                        'updated_id' => $usuarioId,
                    ]);
            }

            return [
                'nombre'  => $municipio->nombre_mun,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Realiza la baja lógica del municipio apagando (status = false) en cascada a todos sus descendientes.
     * Cascada: Municipio -> Parroquias -> Localidades.
     */
    public static function desactivarConCascada(int $id_mun, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_mun, $userId) {
            $municipio = static::obtenerPorId($id_mun);
            $usuarioId = $userId ?? auth()->id();

            // 1. Apagar Municipio
            DB::table('municipio')
                ->where('id_mun', $id_mun)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener IDs de Parroquias dependientes
            $parroquiaIds = DB::table('parroquia')
                ->where('mun_id', $municipio->id_mun)
                ->pluck('id_par');

            if ($parroquiaIds->isNotEmpty()) {
                // Apagar Parroquias
                DB::table('parroquia')
                    ->whereIn('id_par', $parroquiaIds)
                    ->update([
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Apagar Localidades dependientes
                DB::table('localidad')
                    ->whereIn('par_id', $parroquiaIds)
                    ->update([
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);
            }

            return [
                'nombre'  => $municipio->nombre_mun,
                'estatus' => false,
            ];
        });
    }
}
