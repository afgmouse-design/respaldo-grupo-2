<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Sede (tabla `sede`).
 *
 * Propósito: Representa los núcleos, extensiones físicas y campus pertenecientes
 * a la institución universitaria matriz en el SOGAC.
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes en Eloquent (manejo mediante status booleano).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('sede') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Sede extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'sede';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_sed';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Sede';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_sed',
        'siglas_sed',
        'codigo_ministerio_sed',
        'direccion_sed',
        'telefono_sed',
        'correo_sed',
        'gaceta_sed',
        'fecha_gaceta_sed',
        'resolucion_sed',
        'fecha_resolucion_sed',
        'fecha_inicio_sed',
        'fecha_fin_sed',
        'uni_id',
        'loc_id',
        'cdn_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos. */
    protected $casts = [
        'status'               => 'boolean',
        'status_id'            => 'integer',
        'fecha_gaceta_sed'     => 'date',
        'fecha_resolucion_sed' => 'date',
        'fecha_inicio_sed'     => 'date',
        'fecha_fin_sed'        => 'date',
    ];

    /** Atributos adicionales anexados automáticamente al array / json. */
    protected $appends = [
        'est_sed',
    ];

    /**
     * Accesor para compatibilidad con convención est_sed.
     */
    public function getEstSedAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre eliminando números y aplicando Title Case.
     */
    public function setNombreSedAttribute($value): void
    {
        $this->attributes['nombre_sed'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    /**
     * Mutador: Estandariza las siglas a mayúsculas y sin espacios.
     */
    public function setSiglasSedAttribute($value): void
    {
        $this->attributes['siglas_sed'] = $value
            ? mb_strtoupper(preg_replace('/\s+/', '', (string) $value), 'UTF-8')
            : null;
    }

    /**
     * Universidad a la que pertenece esta sede (Relación Eloquent).
     */
    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'uni_id', 'id_uni');
    }

    /**
     * Localidad geográfica donde se ubica la sede (Relación Eloquent).
     */
    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'loc_id', 'id_loc');
    }

    /**
     * Condición institucional o tipología de la sede (Relación Eloquent).
     */
    public function condicion(): BelongsTo
    {
        return $this->belongsTo(Condicion::class, 'cdn_id', 'id_cdn');
    }

    /**
     * Usuario que registró la sede en el sistema (Relación Eloquent).
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    /**
     * Usuario que realizó la última actualización (Relación Eloquent).
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    /**
     * Scope: solo sedes activas.
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
        $uni_id = $filtros['uni_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $query = DB::table('sede')
            ->leftJoin('universidad', 'universidad.id_uni', '=', 'sede.uni_id')
            ->leftJoin('localidad', 'localidad.id_loc', '=', 'sede.loc_id')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('condicion', 'condicion.id_cdn', '=', 'sede.cdn_id')
            ->whereNull('sede.deleted_at')
            ->when($status !== null && $status !== '' && $status !== 'all', function ($q) use ($status) {
                $q->where('sede.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->select(
                'sede.*',
                'universidad.id_uni as uni_id_uni',
                'universidad.nombre_uni as uni_nombre_uni',
                'universidad.siglas_uni as uni_siglas_uni',
                'localidad.id_loc as loc_id_loc',
                'localidad.nombre_loc as loc_nombre_loc',
                'parroquia.id_par as loc_id_par',
                'parroquia.nombre_par as loc_nombre_par',
                'municipio.id_mun as loc_id_mun',
                'municipio.nombre_mun as loc_nombre_mun',
                'estado.id_esd as loc_id_esd',
                'estado.nombre_esd as loc_nombre_esd',
                'condicion.id_cdn as cdn_id_cdn',
                'condicion.nombre_cdn as cdn_nombre_cdn'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('sede.nombre_sed', 'ilike', "%{$search}%")
                  ->orWhere('sede.siglas_sed', 'ilike', "%{$search}%")
                  ->orWhere('sede.codigo_ministerio_sed', 'ilike', "%{$search}%");
            });
        }

        if (!empty($uni_id) && $uni_id !== 'all') {
            $query->where('sede.uni_id', (int) $uni_id);
        }

        $paginator = $query->orderBy('sede.id_sed', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo y estructuración de datos para compatibilidad con props de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->status = (bool) $item->status;
            $item->est_sed = (bool) $item->status;

            $item->universidad = $item->uni_id_uni ? (object) [
                'id_uni'     => $item->uni_id_uni,
                'nombre_uni' => $item->uni_nombre_uni,
                'siglas_uni' => $item->uni_siglas_uni,
            ] : null;

            $item->localidad = $item->loc_id_loc ? (object) [
                'id_loc'     => $item->loc_id_loc,
                'nombre_loc' => $item->loc_nombre_loc,
                'parroquia'  => $item->loc_id_par ? (object) [
                    'id_par'     => $item->loc_id_par,
                    'nombre_par' => $item->loc_nombre_par,
                    'municipio'  => $item->loc_id_mun ? (object) [
                        'id_mun'     => $item->loc_id_mun,
                        'nombre_mun' => $item->loc_nombre_mun,
                        'estado'     => $item->loc_id_esd ? (object) [
                            'id_esd'     => $item->loc_id_esd,
                            'nombre_esd' => $item->loc_nombre_esd,
                        ] : null,
                    ] : null,
                ] : null,
            ] : null;

            $item->condicion = $item->cdn_id_cdn ? (object) [
                'id_cdn'     => $item->cdn_id_cdn,
                'nombre_cdn' => $item->cdn_nombre_cdn,
            ] : null;

            unset(
                $item->uni_id_uni,
                $item->uni_nombre_uni,
                $item->uni_siglas_uni,
                $item->loc_id_loc,
                $item->loc_nombre_loc,
                $item->loc_id_par,
                $item->loc_nombre_par,
                $item->loc_id_mun,
                $item->loc_nombre_mun,
                $item->loc_id_esd,
                $item->loc_nombre_esd,
                $item->cdn_id_cdn,
                $item->cdn_nombre_cdn
            );

            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra una nueva sede mediante Query Builder puro.
     */
    public static function registrarSede(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_sed'            => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_sed'])), MB_CASE_TITLE, 'UTF-8'),
            'siglas_sed'            => !empty($datos['siglas_sed']) ? mb_strtoupper(preg_replace('/\s+/', '', (string) $datos['siglas_sed']), 'UTF-8') : null,
            'codigo_ministerio_sed' => !empty($datos['codigo_ministerio_sed']) ? trim((string) $datos['codigo_ministerio_sed']) : null,
            'direccion_sed'         => !empty($datos['direccion_sed']) ? trim((string) $datos['direccion_sed']) : null,
            'telefono_sed'          => !empty($datos['telefono_sed']) ? trim((string) $datos['telefono_sed']) : null,
            'correo_sed'            => !empty($datos['correo_sed']) ? trim((string) $datos['correo_sed']) : null,
            'gaceta_sed'            => !empty($datos['gaceta_sed']) ? trim((string) $datos['gaceta_sed']) : null,
            'fecha_gaceta_sed'      => !empty($datos['fecha_gaceta_sed']) ? $datos['fecha_gaceta_sed'] : null,
            'resolucion_sed'        => !empty($datos['resolucion_sed']) ? trim((string) $datos['resolucion_sed']) : null,
            'fecha_resolucion_sed'  => !empty($datos['fecha_resolucion_sed']) ? $datos['fecha_resolucion_sed'] : null,
            'fecha_inicio_sed'      => !empty($datos['fecha_inicio_sed']) ? $datos['fecha_inicio_sed'] : null,
            'fecha_fin_sed'         => !empty($datos['fecha_fin_sed']) ? $datos['fecha_fin_sed'] : null,
            'uni_id'                => (int) $datos['uni_id'],
            'loc_id'                => (int) $datos['loc_id'],
            'cdn_id'                => !empty($datos['cdn_id']) ? (int) $datos['cdn_id'] : null,
            'status'                => true,
            'status_id'             => 1,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('sede')->insertGetId($insertData, 'id_sed');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarSede para compatibilidad.
     */
    public static function crearSede(array $datos, ?int $userId = null): object
    {
        return static::registrarSede($datos, $userId);
    }

    /**
     * 3. Actualiza una sede existente mediante Query Builder puro.
     */
    public static function actualizarSede(int $id_sed, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (array_key_exists('nombre_sed', $datos)) {
            $updateData['nombre_sed'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_sed'])), MB_CASE_TITLE, 'UTF-8');
        }
        if (array_key_exists('siglas_sed', $datos)) {
            $updateData['siglas_sed'] = !empty($datos['siglas_sed']) ? mb_strtoupper(preg_replace('/\s+/', '', (string) $datos['siglas_sed']), 'UTF-8') : null;
        }
        if (array_key_exists('codigo_ministerio_sed', $datos)) {
            $updateData['codigo_ministerio_sed'] = !empty($datos['codigo_ministerio_sed']) ? trim((string) $datos['codigo_ministerio_sed']) : null;
        }
        if (array_key_exists('direccion_sed', $datos)) {
            $updateData['direccion_sed'] = !empty($datos['direccion_sed']) ? trim((string) $datos['direccion_sed']) : null;
        }
        if (array_key_exists('telefono_sed', $datos)) {
            $updateData['telefono_sed'] = !empty($datos['telefono_sed']) ? trim((string) $datos['telefono_sed']) : null;
        }
        if (array_key_exists('correo_sed', $datos)) {
            $updateData['correo_sed'] = !empty($datos['correo_sed']) ? trim((string) $datos['correo_sed']) : null;
        }
        if (array_key_exists('gaceta_sed', $datos)) {
            $updateData['gaceta_sed'] = !empty($datos['gaceta_sed']) ? trim((string) $datos['gaceta_sed']) : null;
        }
        if (array_key_exists('fecha_gaceta_sed', $datos)) {
            $updateData['fecha_gaceta_sed'] = !empty($datos['fecha_gaceta_sed']) ? $datos['fecha_gaceta_sed'] : null;
        }
        if (array_key_exists('resolucion_sed', $datos)) {
            $updateData['resolucion_sed'] = !empty($datos['resolucion_sed']) ? trim((string) $datos['resolucion_sed']) : null;
        }
        if (array_key_exists('fecha_resolucion_sed', $datos)) {
            $updateData['fecha_resolucion_sed'] = !empty($datos['fecha_resolucion_sed']) ? $datos['fecha_resolucion_sed'] : null;
        }
        if (array_key_exists('fecha_inicio_sed', $datos)) {
            $updateData['fecha_inicio_sed'] = !empty($datos['fecha_inicio_sed']) ? $datos['fecha_inicio_sed'] : null;
        }
        if (array_key_exists('fecha_fin_sed', $datos)) {
            $updateData['fecha_fin_sed'] = !empty($datos['fecha_fin_sed']) ? $datos['fecha_fin_sed'] : null;
        }
        if (array_key_exists('uni_id', $datos)) {
            $updateData['uni_id'] = (int) $datos['uni_id'];
        }
        if (array_key_exists('loc_id', $datos)) {
            $updateData['loc_id'] = (int) $datos['loc_id'];
        }
        if (array_key_exists('cdn_id', $datos)) {
            $updateData['cdn_id'] = !empty($datos['cdn_id']) ? (int) $datos['cdn_id'] : null;
        }
        if (array_key_exists('status', $datos)) {
            $val = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
            $updateData['status'] = $val;
            $updateData['status_id'] = $val ? 1 : 2;
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('sede')
            ->where('id_sed', $id_sed)
            ->update($updateData);

        return static::obtenerPorId($id_sed);
    }

    /**
     * 4. Busca una sede por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_sed): object
    {
        $sede = DB::table('sede')
            ->where('id_sed', $id_sed)
            ->whereNull('deleted_at')
            ->first();

        if (!$sede) {
            abort(404, 'Sede no encontrada.');
        }

        $sede->status = (bool) $sede->status;
        $sede->est_sed = (bool) $sede->status;

        return $sede;
    }

    /**
     * Obtiene una sede para la vista de edición.
     */
    public static function obtenerParaEditar(int $id_sed): object
    {
        return static::obtenerPorId($id_sed);
    }

    /**
     * Obtiene el detalle de una sede con su universidad, localidad, condición y usuarios auditores.
     */
    public static function obtenerConRelaciones(int $id_sed): object
    {
        $sede = static::obtenerPorId($id_sed);

        // Universidad
        $sede->universidad = DB::table('universidad')
            ->select(['id_uni', 'nombre_uni', 'siglas_uni', 'status'])
            ->where('id_uni', $sede->uni_id)
            ->first();

        // Condición
        $sede->condicion = $sede->cdn_id
            ? DB::table('condicion')->select(['id_cdn', 'nombre_cdn'])->where('id_cdn', $sede->cdn_id)->first()
            : null;

        // Localidad con jerarquía territorial completa
        if ($sede->loc_id) {
            $localidad = DB::table('localidad')
                ->select(['id_loc', 'nombre_loc', 'par_id'])
                ->where('id_loc', $sede->loc_id)
                ->first();

            if ($localidad) {
                $parroquia = DB::table('parroquia')
                    ->select(['id_par', 'nombre_par', 'mun_id'])
                    ->where('id_par', $localidad->par_id)
                    ->first();

                if ($parroquia) {
                    $municipio = DB::table('municipio')
                        ->select(['id_mun', 'nombre_mun', 'esd_id'])
                        ->where('id_mun', $parroquia->mun_id)
                        ->first();

                    if ($municipio) {
                        $estado = DB::table('estado')
                            ->select(['id_esd', 'nombre_esd', 'pai_id'])
                            ->where('id_esd', $municipio->esd_id)
                            ->first();

                        if ($estado) {
                            $estado->pais = DB::table('pais')
                                ->select(['id_pai', 'nombre_pai'])
                                ->where('id_pai', $estado->pai_id)
                                ->first();
                        }

                        $municipio->estado = $estado;
                    }

                    $parroquia->municipio = $municipio;
                }

                $localidad->parroquia = $parroquia;
            }

            $sede->localidad = $localidad;
        } else {
            $sede->localidad = null;
        }

        // Creador y Actualizador
        $sede->creador = $sede->created_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $sede->created_id)->first()
            : null;

        $sede->actualizador = $sede->updated_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $sede->updated_id)->first()
            : null;

        return $sede;
    }

    /**
     * Alterna el estatus lógico de la sede (Activo / Inactivo).
     * Si se activa, valida que la universidad matriz se encuentre activa.
     */
    public static function alternarEstatus(int $id_sed, $userId = null, ?bool $forzarEstatus = null): array
    {
        return DB::transaction(function () use ($id_sed, $userId, $forzarEstatus) {
            $sede = static::obtenerPorId($id_sed);

            $targetStatus = null;
            $usuarioId = null;

            if (is_bool($userId)) {
                $targetStatus = $userId;
                $usuarioId = auth()->id();
            } else {
                $usuarioId = $userId ?? auth()->id();
                $targetStatus = $forzarEstatus;
            }

            $nuevoEstatus = $targetStatus !== null ? $targetStatus : !$sede->status;

            // Validación jerárquica: Si se intenta activar, verificar que la universidad esté activa
            if ($nuevoEstatus) {
                $uniActiva = DB::table('universidad')->where('id_uni', $sede->uni_id)->value('status');
                if (!$uniActiva) {
                    throw new \DomainException('No se puede activar esta sede porque la universidad matriz se encuentra inactiva.');
                }
            }

            DB::table('sede')
                ->where('id_sed', $id_sed)
                ->update([
                    'status'     => $nuevoEstatus,
                    'status_id'  => $nuevoEstatus ? 1 : 2,
                    'updated_id' => $usuarioId,
                ]);

            return [
                'nombre'  => $sede->nombre_sed,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Alias de alternarEstatus.
     */
    public static function alternarEstatusConCascada(int $id_sed, ?int $userId = null): array
    {
        return static::alternarEstatus($id_sed, $userId);
    }
}
