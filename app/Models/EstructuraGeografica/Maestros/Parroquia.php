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
 * Modelo: Parroquia (tabla `parroquia`).
 *
 * Propósito: Representa una parroquia (división político-territorial menor).
 * Pertenece a un municipio (`mun_id`) y agrupa múltiples localidades (`localidad.par_id`).
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('parroquia') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Parroquia extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'parroquia';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_par';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Parroquia';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_par',
        'mun_id',
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
        'est_par',
        'puede_activarse',
    ];

    /**
     * Accesor para compatibilidad con convención est_par.
     */
    public function getEstParAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre eliminando números y aplicando Title Case.
     */
    public function setNombreParAttribute($value): void
    {
        $this->attributes['nombre_par'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Determina si la parroquia puede activarse según el estatus de sus ancestros (Municipio, Estado, País).
     */
    public function getPuedeActivarseAttribute(): bool
    {
        $municipio = DB::table('municipio')
            ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('municipio.id_mun', $this->mun_id)
            ->select('municipio.status as municipio_status', 'estado.status as estado_status', 'pais.status as pais_status')
            ->first();

        if (!$municipio) {
            return false;
        }

        return (bool) ($municipio->municipio_status && $municipio->estado_status && $municipio->pais_status);
    }

    /**
     * Municipio al que pertenece esta parroquia (Relación Eloquent).
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'mun_id', 'id_mun');
    }

    /**
     * Localidades que pertenecen a esta parroquia (Relación Eloquent).
     */
    public function localidades(): HasMany
    {
        return $this->hasMany(Localidad::class, 'par_id', 'id_par');
    }

    /**
     * Scope: solo parroquias activas.
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
        $esd_id = $filtros['esd_id'] ?? null;
        $mun_id = $filtros['mun_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $query = DB::table('parroquia')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'parroquia.*',
                'municipio.id_mun as municipio_id_mun',
                'municipio.nombre_mun as municipio_nombre_mun',
                'municipio.esd_id as municipio_esd_id',
                'municipio.status as municipio_status',
                'estado.id_esd as estado_id_esd',
                'estado.nombre_esd as estado_nombre_esd',
                'estado.status as estado_status',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status as pais_status'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM localidad WHERE localidad.par_id = parroquia.id_par) as localidades_count')
            ->when($status !== null && $status !== '' && $status !== 'all', function ($q) use ($status) {
                $q->where('parroquia.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('parroquia.nombre_par', 'ilike', "%{$search}%")
                  ->orWhere('municipio.nombre_mun', 'ilike', "%{$search}%")
                  ->orWhere('estado.nombre_esd', 'ilike', "%{$search}%");
            });
        }

        if (!empty($mun_id)) {
            $query->where('parroquia.mun_id', (int) $mun_id);
        }

        if (!empty($esd_id)) {
            $query->where('municipio.esd_id', (int) $esd_id);
        }

        $paginator = $query->orderBy('parroquia.id_par', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo y estructuración de datos para compatibilidad con props de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->status = (bool) $item->status;
            $item->est_par = (bool) $item->status;
            $item->puede_activarse = (bool) (($item->municipio_status ?? false) && ($item->estado_status ?? false) && ($item->pais_status ?? false));

            $item->municipio = $item->municipio_id_mun ? (object) [
                'id_mun'     => $item->municipio_id_mun,
                'nombre_mun' => $item->municipio_nombre_mun,
                'esd_id'     => $item->municipio_esd_id,
                'status'     => (bool) $item->municipio_status,
                'estado'     => $item->estado_id_esd ? (object) [
                    'id_esd'     => $item->estado_id_esd,
                    'nombre_esd' => $item->estado_nombre_esd,
                    'status'     => (bool) $item->estado_status,
                ] : null,
            ] : null;

            unset(
                $item->municipio_id_mun,
                $item->municipio_nombre_mun,
                $item->municipio_esd_id,
                $item->municipio_status,
                $item->estado_id_esd,
                $item->estado_nombre_esd,
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
     * 2. Registra una nueva parroquia mediante Query Builder puro.
     */
    public static function registrarParroquia(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_par' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_par'])), MB_CASE_TITLE, 'UTF-8'),
            'mun_id'     => (int) $datos['mun_id'],
            'status'     => true,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('parroquia')->insertGetId($insertData, 'id_par');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarParroquia para compatibilidad.
     */
    public static function crearParroquia(array $datos, ?int $userId = null): object
    {
        return static::registrarParroquia($datos, $userId);
    }

    /**
     * 3. Actualiza una parroquia existente mediante Query Builder puro.
     */
    public static function actualizarParroquia(int $id_par, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (isset($datos['nombre_par'])) {
            $updateData['nombre_par'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_par'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['mun_id'])) {
            $updateData['mun_id'] = (int) $datos['mun_id'];
        }

        if (isset($datos['status'])) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('parroquia')
            ->where('id_par', $id_par)
            ->update($updateData);

        return static::obtenerPorId($id_par);
    }

    /**
     * 4. Busca una parroquia por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_par): object
    {
        $parroquia = DB::table('parroquia')->where('id_par', $id_par)->first();

        if (!$parroquia) {
            abort(404, 'Parroquia no encontrada.');
        }

        $parroquia->status = (bool) $parroquia->status;
        $parroquia->est_par = (bool) $parroquia->status;

        return $parroquia;
    }

    /**
     * Obtiene una parroquia con su municipio padre para la vista de edición.
     */
    public static function obtenerParaEditar(int $id_par): object
    {
        $parroquia = static::obtenerPorId($id_par);

        $parroquia->municipio = DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id', 'status'])
            ->where('id_mun', $parroquia->mun_id)
            ->first();

        return $parroquia;
    }

    /**
     * Obtiene el detalle de una parroquia con su municipio, estado, país y localidades subordinadas mediante Query Builder puro.
     */
    public static function obtenerConRelaciones(int $id_par): object
    {
        $parroquia = static::obtenerPorId($id_par);

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

        $parroquia->localidades = DB::table('localidad')
            ->select('localidad.*')
            ->where('par_id', $id_par)
            ->orderBy('nombre_loc', 'asc')
            ->get()
            ->map(function ($l) {
                $l->status = (bool) $l->status;
                return $l;
            });

        return $parroquia;
    }

    /**
     * Obtiene parroquias activas para llenar selectores dependientes de un municipio mediante Query Builder puro.
     */
    public static function obtenerActivosPorMunicipio(int $mun_id): Collection
    {
        return DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id'])
            ->where('mun_id', $mun_id)
            ->where('status', true)
            ->orderBy('nombre_par', 'asc')
            ->get();
    }

    /**
     * Obtiene todas las parroquias activas para selectores con Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id'])
            ->where('status', true)
            ->orderBy('nombre_par', 'asc')
            ->get();
    }

    /**
     * Alterna el estatus lógico de la parroquia y propaga en cascada hacia sus localidades subordinadas.
     */
    public static function alternarEstatusConCascada(int $id_par, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_par, $userId) {
            $parroquia = static::obtenerPorId($id_par);
            $nuevoEstatus = !$parroquia->status;
            $usuarioId = $userId ?? auth()->id();

            // Validación jerárquica con JOIN: Si se intenta activar, verificar que el municipio padre esté activo
            if ($nuevoEstatus) {
                $municipio = DB::table('municipio')
                    ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
                    ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
                    ->where('municipio.id_mun', $parroquia->mun_id)
                    ->select('municipio.status as municipio_status', 'estado.status as estado_status', 'pais.status as pais_status')
                    ->first();

                if (!$municipio || !$municipio->municipio_status || !$municipio->estado_status || !$municipio->pais_status) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            // 1. Actualizar Parroquia
            DB::table('parroquia')
                ->where('id_par', $id_par)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Actualizar Localidades dependientes en cascada
            DB::table('localidad')
                ->where('par_id', $parroquia->id_par)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            return [
                'nombre'  => $parroquia->nombre_par,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Realiza la baja lógica de la parroquia apagando (status = false) en cascada a sus localidades subordinadas.
     * Cascada: Parroquia -> Localidades.
     */
    public static function desactivarConCascada(int $id_par, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_par, $userId) {
            $parroquia = static::obtenerPorId($id_par);
            $usuarioId = $userId ?? auth()->id();

            // 1. Apagar Parroquia
            DB::table('parroquia')
                ->where('id_par', $id_par)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Apagar Localidades dependientes
            DB::table('localidad')
                ->where('par_id', $id_par)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            return [
                'nombre'  => $parroquia->nombre_par,
                'estatus' => false,
            ];
        });
    }
}
