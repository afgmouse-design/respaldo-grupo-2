<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Sede (tabla `sede`).
 *
 * Propósito: Representa los núcleos, extensiones físicas y campus pertenecientes
 * a la institución universitaria matriz en el SOGAC.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`cse_id -> condicion_sede.id_cse`, `status_id`,
 *    `SoftDeletes`, `fax_sed`, `users.username`, sin columna booleana `status`).
 *  - 2026-11-16: Añadido `eliminarConSoftDelete` para baja lógica en interfaz (`deleted_at` +
 *    `status_id = 2`) conservando el registro en PostgreSQL.
 */
class Sede extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'sede';
    protected $primaryKey = 'id_sed';
    protected $moduloAuditoria = 'Sede';

    protected $fillable = [
        'uni_id',
        'cse_id',
        'loc_id',
        'codigo_ministerio_sed',
        'nombre_sed',
        'siglas_sed',
        'telefono_sed',
        'fax_sed',
        'correo_sed',
        'direccion_sed',
        'gaceta_sed',
        'resolucion_sed',
        'fecha_gaceta_sed',
        'fecha_resolucion_sed',
        'fecha_inicio_sed',
        'fecha_fin_sed',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_sed'               => 'integer',
        'uni_id'               => 'integer',
        'cse_id'               => 'integer',
        'loc_id'               => 'integer',
        'gaceta_sed'           => 'integer',
        'resolucion_sed'       => 'integer',
        'status_id'            => 'integer',
        'fecha_gaceta_sed'     => 'date',
        'fecha_resolucion_sed' => 'date',
        'fecha_inicio_sed'     => 'date',
        'fecha_fin_sed'        => 'date',
    ];

    public function setNombreSedAttribute($value): void
    {
        $this->attributes['nombre_sed'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function setSiglasSedAttribute($value): void
    {
        $this->attributes['siglas_sed'] = $value
            ? substr(mb_strtoupper(preg_replace('/\s+/', '', (string) $value), 'UTF-8'), 0, 3)
            : null;
    }

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'uni_id', 'id_uni');
    }

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'loc_id', 'id_loc');
    }

    public function condicionSede(): BelongsTo
    {
        return $this->belongsTo(CondicionSede::class, 'cse_id', 'id_cse');
    }

    public function condicion(): BelongsTo
    {
        return $this->condicionSede();
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

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $uni_id = $filtros['uni_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $query = DB::table('sede')
            ->leftJoin('universidad', 'universidad.id_uni', '=', 'sede.uni_id')
            ->leftJoin('localidad', 'localidad.id_loc', '=', 'sede.loc_id')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('condicion_sede', 'condicion_sede.id_cse', '=', 'sede.cse_id')
            ->whereNull('sede.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('sede.status_id', $statusId))
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
                'condicion_sede.id_cse as cse_id_cse',
                'condicion_sede.nombre_cse as cse_nombre_cse'
            );

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('sede.nombre_sed', 'ilike', "%{$search}%")
                    ->orWhere('sede.siglas_sed', 'ilike', "%{$search}%")
                    ->orWhere('sede.codigo_ministerio_sed', 'ilike', "%{$search}%");
            });
        }

        if (! empty($uni_id) && $uni_id !== 'all') {
            $query->where('sede.uni_id', (int) $uni_id);
        }

        $paginator = $query->orderBy('sede.id_sed', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
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

            $item->condicion_sede = $item->cse_id_cse ? (object) [
                'id_cse'     => $item->cse_id_cse,
                'nombre_cse' => $item->cse_nombre_cse,
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
                $item->cse_id_cse,
                $item->cse_nombre_cse
            );

            return $item;
        });

        return $paginator;
    }

    public static function registrarSede(array $datos, ?int $userId = null): object
    {
        $now = now();
        $cseId = $datos['cse_id'] ?? $datos['cdn_id'] ?? null;

        $insertData = [
            'nombre_sed'            => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_sed'])), MB_CASE_TITLE, 'UTF-8'),
            'siglas_sed'            => ! empty($datos['siglas_sed']) ? substr(mb_strtoupper(preg_replace('/\s+/', '', (string) $datos['siglas_sed']), 'UTF-8'), 0, 3) : null,
            'codigo_ministerio_sed' => ! empty($datos['codigo_ministerio_sed']) ? substr(trim((string) $datos['codigo_ministerio_sed']), 0, 10) : null,
            'direccion_sed'         => ! empty($datos['direccion_sed']) ? trim((string) $datos['direccion_sed']) : null,
            'telefono_sed'          => ! empty($datos['telefono_sed']) ? substr(trim((string) $datos['telefono_sed']), 0, 12) : null,
            'fax_sed'               => ! empty($datos['fax_sed']) ? substr(trim((string) $datos['fax_sed']), 0, 12) : null,
            'correo_sed'            => ! empty($datos['correo_sed']) ? trim((string) $datos['correo_sed']) : null,
            'gaceta_sed'            => isset($datos['gaceta_sed']) && $datos['gaceta_sed'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['gaceta_sed']) : null,
            'fecha_gaceta_sed'      => ! empty($datos['fecha_gaceta_sed']) ? $datos['fecha_gaceta_sed'] : null,
            'resolucion_sed'        => isset($datos['resolucion_sed']) && $datos['resolucion_sed'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['resolucion_sed']) : null,
            'fecha_resolucion_sed'  => ! empty($datos['fecha_resolucion_sed']) ? $datos['fecha_resolucion_sed'] : null,
            'fecha_inicio_sed'      => ! empty($datos['fecha_inicio_sed']) ? $datos['fecha_inicio_sed'] : null,
            'fecha_fin_sed'         => ! empty($datos['fecha_fin_sed']) ? $datos['fecha_fin_sed'] : null,
            'uni_id'                => (int) $datos['uni_id'],
            'loc_id'                => ! empty($datos['loc_id']) ? (int) $datos['loc_id'] : null,
            'cse_id'                => ! empty($cseId) ? (int) $cseId : null,
            'status_id'             => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at'            => $now,
            'updated_at'            => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('sede')->insertGetId($insertData, 'id_sed');

        return static::obtenerPorId($id);
    }

    public static function crearSede(array $datos, ?int $userId = null): object
    {
        return static::registrarSede($datos, $userId);
    }

    public static function actualizarSede(int $id_sed, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (array_key_exists('nombre_sed', $datos)) {
            $updateData['nombre_sed'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_sed'])), MB_CASE_TITLE, 'UTF-8');
        }
        if (array_key_exists('siglas_sed', $datos)) {
            $updateData['siglas_sed'] = ! empty($datos['siglas_sed']) ? substr(mb_strtoupper(preg_replace('/\s+/', '', (string) $datos['siglas_sed']), 'UTF-8'), 0, 3) : null;
        }
        if (array_key_exists('codigo_ministerio_sed', $datos)) {
            $updateData['codigo_ministerio_sed'] = ! empty($datos['codigo_ministerio_sed']) ? substr(trim((string) $datos['codigo_ministerio_sed']), 0, 10) : null;
        }
        if (array_key_exists('direccion_sed', $datos)) {
            $updateData['direccion_sed'] = ! empty($datos['direccion_sed']) ? trim((string) $datos['direccion_sed']) : null;
        }
        if (array_key_exists('telefono_sed', $datos)) {
            $updateData['telefono_sed'] = ! empty($datos['telefono_sed']) ? substr(trim((string) $datos['telefono_sed']), 0, 12) : null;
        }
        if (array_key_exists('fax_sed', $datos)) {
            $updateData['fax_sed'] = ! empty($datos['fax_sed']) ? substr(trim((string) $datos['fax_sed']), 0, 12) : null;
        }
        if (array_key_exists('correo_sed', $datos)) {
            $updateData['correo_sed'] = ! empty($datos['correo_sed']) ? trim((string) $datos['correo_sed']) : null;
        }
        if (array_key_exists('gaceta_sed', $datos)) {
            $updateData['gaceta_sed'] = $datos['gaceta_sed'] !== null && $datos['gaceta_sed'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['gaceta_sed']) : null;
        }
        if (array_key_exists('fecha_gaceta_sed', $datos)) {
            $updateData['fecha_gaceta_sed'] = ! empty($datos['fecha_gaceta_sed']) ? $datos['fecha_gaceta_sed'] : null;
        }
        if (array_key_exists('resolucion_sed', $datos)) {
            $updateData['resolucion_sed'] = $datos['resolucion_sed'] !== null && $datos['resolucion_sed'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['resolucion_sed']) : null;
        }
        if (array_key_exists('fecha_resolucion_sed', $datos)) {
            $updateData['fecha_resolucion_sed'] = ! empty($datos['fecha_resolucion_sed']) ? $datos['fecha_resolucion_sed'] : null;
        }
        if (array_key_exists('fecha_inicio_sed', $datos)) {
            $updateData['fecha_inicio_sed'] = ! empty($datos['fecha_inicio_sed']) ? $datos['fecha_inicio_sed'] : null;
        }
        if (array_key_exists('fecha_fin_sed', $datos)) {
            $updateData['fecha_fin_sed'] = ! empty($datos['fecha_fin_sed']) ? $datos['fecha_fin_sed'] : null;
        }
        if (array_key_exists('uni_id', $datos)) {
            $updateData['uni_id'] = (int) $datos['uni_id'];
        }
        if (array_key_exists('loc_id', $datos)) {
            $updateData['loc_id'] = ! empty($datos['loc_id']) ? (int) $datos['loc_id'] : null;
        }
        if (array_key_exists('cse_id', $datos) || array_key_exists('cdn_id', $datos)) {
            $cseId = $datos['cse_id'] ?? $datos['cdn_id'] ?? null;
            $updateData['cse_id'] = ! empty($cseId) ? (int) $cseId : null;
        }
        if (array_key_exists('status_id', $datos)) {
            $updateData['status_id'] = (int) $datos['status_id'];
        } elseif (array_key_exists('status', $datos)) {
            $updateData['status_id'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN)
                ? self::STATUS_ACTIVO
                : self::STATUS_INACTIVO;
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('sede')
            ->where('id_sed', $id_sed)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_sed);
    }

    public static function obtenerPorId(int $id_sed): object
    {
        $sede = DB::table('sede')
            ->where('id_sed', $id_sed)
            ->whereNull('deleted_at')
            ->first();

        if (! $sede) {
            abort(404, 'Sede no encontrada.');
        }

        return $sede;
    }

    public static function obtenerParaEditar(int $id_sed): object
    {
        return static::obtenerPorId($id_sed);
    }

    public static function obtenerConRelaciones(int $id_sed): object
    {
        $sede = static::obtenerPorId($id_sed);

        $sede->universidad = DB::table('universidad')
            ->select(['id_uni', 'nombre_uni', 'siglas_uni', 'status_id'])
            ->where('id_uni', $sede->uni_id)
            ->first();

        $sede->condicion_sede = $sede->cse_id
            ? DB::table('condicion_sede')->select(['id_cse', 'nombre_cse'])->where('id_cse', $sede->cse_id)->first()
            : null;

        if ($sede->loc_id) {
            $localidad = DB::table('localidad')->select(['id_loc', 'nombre_loc', 'par_id'])->where('id_loc', $sede->loc_id)->first();

            if ($localidad) {
                $parroquia = DB::table('parroquia')->select(['id_par', 'nombre_par', 'mun_id'])->where('id_par', $localidad->par_id)->first();
                if ($parroquia) {
                    $municipio = DB::table('municipio')->select(['id_mun', 'nombre_mun', 'esd_id'])->where('id_mun', $parroquia->mun_id)->first();
                    if ($municipio) {
                        $estado = DB::table('estado')->select(['id_esd', 'nombre_esd', 'pai_id'])->where('id_esd', $municipio->esd_id)->first();
                        if ($estado) {
                            $estado->pais = DB::table('pais')->select(['id_pai', 'nombre_pai'])->where('id_pai', $estado->pai_id)->first();
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

        $sede->creador = $sede->created_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $sede->created_id)->first()
            : null;

        $sede->actualizador = $sede->updated_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $sede->updated_id)->first()
            : null;

        return $sede;
    }

    public static function alternarEstatus(int $id_sed, mixed $userId = null, ?bool $forzarEstatus = null): array
    {
        return DB::transaction(function () use ($id_sed, $userId, $forzarEstatus) {
            $sede = static::obtenerPorId($id_sed);

            if (is_bool($userId)) {
                $targetStatus = $userId;
                $usuarioId = auth()->id();
            } else {
                $usuarioId = $userId ?? auth()->id();
                $targetStatus = $forzarEstatus;
            }

            $activar = $targetStatus !== null ? $targetStatus : ! static::esStatusActivo($sede);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;

            if ($activar) {
                $uniStatusId = (int) DB::table('universidad')->where('id_uni', $sede->uni_id)->value('status_id');
                if ($uniStatusId !== self::STATUS_ACTIVO) {
                    throw new \DomainException('No se puede activar esta sede porque la universidad matriz se encuentra inactiva.');
                }
            }

            DB::table('sede')
                ->where('id_sed', $id_sed)
                ->update([
                    'status_id'  => $nuevoStatusId,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $sede->nombre_sed,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function alternarEstatusConCascada(int $id_sed, ?int $userId = null): array
    {
        return static::alternarEstatus($id_sed, $userId);
    }

    /**
     * Elimina lógicamente la sede de la interfaz (`deleted_at` + `status_id = 2`)
     * conservando el registro histórico en PostgreSQL.
     */
    public static function eliminarConSoftDelete(int $id_sed, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_sed, $userId) {
            $sede = static::find($id_sed);
            if (! $sede) {
                abort(404, 'Sede no encontrada.');
            }

            $nombre = $sede->nombre_sed;

            static::ejecutarBajaLogicaModelo(
                $sede,
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
