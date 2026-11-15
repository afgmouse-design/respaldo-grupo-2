<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Universidad (tabla `universidad`).
 *
 * Propósito: Entidad institucional matriz que representa la institución
 * de educación universitaria rectora y sus datos normativos y de contacto.
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true/false).
 *  - Query Builder puro con DB::table('universidad') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 *
 * Historial de Modificaciones:
 * - [Ajuste de Flujo Institucional]: Incorporación de método estático obtenerRegistroMatriz() para
 *   detectar la institución matriz existente y habilitar redirección directa a su formulario.
 */
class Universidad extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'universidad';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_uni';

    /** Módulo con el que se registran las operaciones en la bitácora. */
    protected $moduloAuditoria = 'Universidad';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'codigo_ministerio_uni',
        'nombre_uni',
        'siglas_uni',
        'rif_uni',
        'gaceta_uni',
        'fecha_gaceta_uni',
        'decreto_uni',
        'fecha_decreto_uni',
        'telefono_uni',
        'correo_uni',
        'sitio_web_uni',
        'logo_uni',
        'fecha_inicio_uni',
        'fecha_fin_uni',
        'loc_id',
        'dpi_id',
        'cdn_id',
        'status',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos. */
    protected $casts = [
        'status' => 'boolean',
        'fecha_gaceta_uni' => 'date',
        'fecha_decreto_uni' => 'date',
        'fecha_inicio_uni' => 'date',
        'fecha_fin_uni' => 'date',
    ];

    /** Atributos adicionales anexados al array / json. */
    protected $appends = [
        'est_uni',
    ];

    /**
     * Accesor para compatibilidad con convención est_uni.
     */
    public function getEstUniAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Estandariza el nombre legal eliminando números y aplicando Title Case.
     */
    public function setNombreUniAttribute($value): void
    {
        $this->attributes['nombre_uni'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Mutador: Limpia y formatea las siglas a mayúsculas.
     */
    public function setSiglasUniAttribute($value): void
    {
        $this->attributes['siglas_uni'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    /**
     * Mutador: Estandariza el formato del RIF institucional a mayúsculas.
     */
    public function setRifUniAttribute($value): void
    {
        $this->attributes['rif_uni'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    /**
     * Localidad donde se asienta la sede rectoral (Relación Eloquent).
     */
    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'loc_id', 'id_loc');
    }

    /**
     * Sedes asociadas a la universidad (Relación Eloquent).
     */
    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'uni_id', 'id_uni');
    }

    /**
     * Usuario creador del registro (Relación Eloquent).
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    /**
     * Usuario actualizador del registro (Relación Eloquent).
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    /**
     * Scope: solo universidades activas.
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
        $status = $filtros['status'] ?? null;

        $query = DB::table('universidad')
            ->leftJoin('localidad', 'localidad.id_loc', '=', 'universidad.loc_id')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->select(
                'universidad.*',
                'localidad.id_loc as loc_id_loc',
                'localidad.nombre_loc as loc_nombre_loc',
                'parroquia.id_par as loc_id_par',
                'parroquia.nombre_par as loc_nombre_par',
                'municipio.id_mun as loc_id_mun',
                'municipio.nombre_mun as loc_nombre_mun',
                'estado.id_esd as loc_id_esd',
                'estado.nombre_esd as loc_nombre_esd'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('universidad.nombre_uni', 'ilike', "%{$search}%")
                  ->orWhere('universidad.siglas_uni', 'ilike', "%{$search}%")
                  ->orWhere('universidad.rif_uni', 'ilike', "%{$search}%")
                  ->orWhere('universidad.codigo_ministerio_uni', 'ilike', "%{$search}%");
            });
        }

        if (!empty($status) || $status === '0' || $status === false) {
            if ($status !== 'all') {
                $query->where('universidad.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            }
        }

        $paginator = $query->orderBy('universidad.id_uni', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo y estructuración de datos para compatibilidad con props de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->status = (bool) $item->status;
            $item->est_uni = (bool) $item->status;

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

            unset(
                $item->loc_id_loc,
                $item->loc_nombre_loc,
                $item->loc_id_par,
                $item->loc_nombre_par,
                $item->loc_id_mun,
                $item->loc_nombre_mun,
                $item->loc_id_esd,
                $item->loc_nombre_esd
            );

            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra una nueva universidad mediante Query Builder puro.
     */
    public static function registrarUniversidad(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_uni'            => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_uni'])), MB_CASE_TITLE, 'UTF-8'),
            'codigo_ministerio_uni' => !empty($datos['codigo_ministerio_uni']) ? trim((string) $datos['codigo_ministerio_uni']) : null,
            'siglas_uni'            => !empty($datos['siglas_uni']) ? mb_strtoupper(trim((string) $datos['siglas_uni']), 'UTF-8') : null,
            'rif_uni'               => !empty($datos['rif_uni']) ? mb_strtoupper(trim((string) $datos['rif_uni']), 'UTF-8') : null,
            'telefono_uni'          => !empty($datos['telefono_uni']) ? trim((string) $datos['telefono_uni']) : null,
            'correo_uni'            => !empty($datos['correo_uni']) ? trim((string) $datos['correo_uni']) : null,
            'sitio_web_uni'         => !empty($datos['sitio_web_uni']) ? trim((string) $datos['sitio_web_uni']) : null,
            'gaceta_uni'            => !empty($datos['gaceta_uni']) ? trim((string) $datos['gaceta_uni']) : null,
            'fecha_gaceta_uni'      => !empty($datos['fecha_gaceta_uni']) ? $datos['fecha_gaceta_uni'] : null,
            'decreto_uni'           => !empty($datos['decreto_uni']) ? trim((string) $datos['decreto_uni']) : null,
            'fecha_decreto_uni'     => !empty($datos['fecha_decreto_uni']) ? $datos['fecha_decreto_uni'] : null,
            'fecha_inicio_uni'      => !empty($datos['fecha_inicio_uni']) ? $datos['fecha_inicio_uni'] : null,
            'fecha_fin_uni'         => !empty($datos['fecha_fin_uni']) ? $datos['fecha_fin_uni'] : null,
            'loc_id'                => !empty($datos['loc_id']) ? (int) $datos['loc_id'] : null,
            'status'                => true,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('universidad')->insertGetId($insertData, 'id_uni');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarUniversidad para compatibilidad.
     */
    public static function crearUniversidad(array $datos, ?int $userId = null): object
    {
        return static::registrarUniversidad($datos, $userId);
    }

    /**
     * 3. Actualiza una universidad existente mediante Query Builder puro.
     */
    public static function actualizarUniversidad(int $id_uni, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (array_key_exists('nombre_uni', $datos)) {
            $updateData['nombre_uni'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_uni'])), MB_CASE_TITLE, 'UTF-8');
        }
        if (array_key_exists('codigo_ministerio_uni', $datos)) {
            $updateData['codigo_ministerio_uni'] = !empty($datos['codigo_ministerio_uni']) ? trim((string) $datos['codigo_ministerio_uni']) : null;
        }
        if (array_key_exists('siglas_uni', $datos)) {
            $updateData['siglas_uni'] = !empty($datos['siglas_uni']) ? mb_strtoupper(trim((string) $datos['siglas_uni']), 'UTF-8') : null;
        }
        if (array_key_exists('rif_uni', $datos)) {
            $updateData['rif_uni'] = !empty($datos['rif_uni']) ? mb_strtoupper(trim((string) $datos['rif_uni']), 'UTF-8') : null;
        }
        if (array_key_exists('telefono_uni', $datos)) {
            $updateData['telefono_uni'] = !empty($datos['telefono_uni']) ? trim((string) $datos['telefono_uni']) : null;
        }
        if (array_key_exists('correo_uni', $datos)) {
            $updateData['correo_uni'] = !empty($datos['correo_uni']) ? trim((string) $datos['correo_uni']) : null;
        }
        if (array_key_exists('sitio_web_uni', $datos)) {
            $updateData['sitio_web_uni'] = !empty($datos['sitio_web_uni']) ? trim((string) $datos['sitio_web_uni']) : null;
        }
        if (array_key_exists('gaceta_uni', $datos)) {
            $updateData['gaceta_uni'] = !empty($datos['gaceta_uni']) ? trim((string) $datos['gaceta_uni']) : null;
        }
        if (array_key_exists('fecha_gaceta_uni', $datos)) {
            $updateData['fecha_gaceta_uni'] = !empty($datos['fecha_gaceta_uni']) ? $datos['fecha_gaceta_uni'] : null;
        }
        if (array_key_exists('decreto_uni', $datos)) {
            $updateData['decreto_uni'] = !empty($datos['decreto_uni']) ? trim((string) $datos['decreto_uni']) : null;
        }
        if (array_key_exists('fecha_decreto_uni', $datos)) {
            $updateData['fecha_decreto_uni'] = !empty($datos['fecha_decreto_uni']) ? $datos['fecha_decreto_uni'] : null;
        }
        if (array_key_exists('fecha_inicio_uni', $datos)) {
            $updateData['fecha_inicio_uni'] = !empty($datos['fecha_inicio_uni']) ? $datos['fecha_inicio_uni'] : null;
        }
        if (array_key_exists('fecha_fin_uni', $datos)) {
            $updateData['fecha_fin_uni'] = !empty($datos['fecha_fin_uni']) ? $datos['fecha_fin_uni'] : null;
        }
        if (array_key_exists('loc_id', $datos)) {
            $updateData['loc_id'] = !empty($datos['loc_id']) ? (int) $datos['loc_id'] : null;
        }
        if (array_key_exists('status', $datos)) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('universidad')
            ->where('id_uni', $id_uni)
            ->update($updateData);

        return static::obtenerPorId($id_uni);
    }

    /**
     * 4. Busca una universidad por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_uni): object
    {
        $universidad = DB::table('universidad')->where('id_uni', $id_uni)->first();

        if (!$universidad) {
            abort(404, 'Universidad no encontrada.');
        }

        $universidad->status = (bool) $universidad->status;
        $universidad->est_uni = (bool) $universidad->status;

        return $universidad;
    }

    /**
     * Obtiene una universidad para la vista de edición.
     */
    public static function obtenerParaEditar(int $id_uni): object
    {
        return static::obtenerPorId($id_uni);
    }

    /**
     * Obtiene el detalle de una universidad con su localidad, jerarquía territorial y usuarios auditores.
     */
    public static function obtenerConRelaciones(int $id_uni): object
    {
        $universidad = static::obtenerPorId($id_uni);

        if ($universidad->loc_id) {
            $localidad = DB::table('localidad')
                ->select(['id_loc', 'nombre_loc', 'par_id'])
                ->where('id_loc', $universidad->loc_id)
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

            $universidad->localidad = $localidad;
        } else {
            $universidad->localidad = null;
        }

        // Creador y Actualizador
        $universidad->creador = $universidad->created_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $universidad->created_id)->first()
            : null;

        $universidad->actualizador = $universidad->updated_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $universidad->updated_id)->first()
            : null;

        return $universidad;
    }

    /**
     * Obtiene universidades activas para llenar selectores dependientes mediante Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('universidad')
            ->select(['id_uni', 'nombre_uni', 'siglas_uni'])
            ->where('status', true)
            ->orderBy('nombre_uni', 'asc')
            ->get();
    }

    /**
     * Alterna el estatus lógico de la universidad y propaga en cascada hacia sus sedes.
     */
    public static function alternarEstatusConCascada(int $id_uni, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_uni, $userId) {
            $universidad = static::obtenerPorId($id_uni);
            $nuevoEstatus = !$universidad->status;
            $usuarioId = $userId ?? auth()->id();

            // 1. Actualizar Universidad
            DB::table('universidad')
                ->where('id_uni', $id_uni)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Si se desactiva, propagar en cascada desactivación a sus sedes
            if (!$nuevoEstatus) {
                DB::table('sede')
                    ->where('uni_id', $id_uni)
                    ->update([
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);
            }

            return [
                'nombre'  => $universidad->nombre_uni,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Alias de alternarEstatusConCascada.
     */
    public static function alternarEstatus(int $id_uni, ?int $userId = null): array
    {
        return static::alternarEstatusConCascada($id_uni, $userId);
    }

    /**
     * Obtiene la institución universitaria matriz registrada en el sistema.
     */
    public static function obtenerRegistroMatriz(): ?object
    {
        return DB::table('universidad')->orderBy('id_uni', 'asc')->first();
    }
}
