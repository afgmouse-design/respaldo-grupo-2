<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Localidad (tabla `localidad`).
 *
 * Propósito: Último nivel de la Estructura Geográfica (ciudad, caserío o sector).
 * Pertenece a una parroquia (`par_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, sin columna `status`).
 *  - 2026-11-16: Añadido `eliminarConCascada` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    sin eliminar físicamente registros de PostgreSQL.
 */
class Localidad extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'localidad';
    protected $primaryKey = 'id_loc';
    protected $moduloAuditoria = 'Localidad';

    /**
     * Elimina lógicamente la localidad de la interfaz (status_id = 2 + deleted_at)
     * preservando el registro histórico en PostgreSQL.
     */
    public static function eliminarConCascada(int $id_loc, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_loc, $userId) {
            $localidad = static::find($id_loc);
            if (! $localidad) {
                abort(404, 'Localidad no encontrada.');
            }

            $nombre = $localidad->nombre_loc;

            static::ejecutarBajaLogicaModelo(
                $localidad,
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

    protected $fillable = [
        'nombre_loc',
        'par_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_loc'    => 'integer',
        'par_id'    => 'integer',
        'status_id' => 'integer',
    ];

    protected $appends = [
        'puede_activarse',
    ];

    public function setNombreLocAttribute($value): void
    {
        $this->attributes['nombre_loc'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function getPuedeActivarseAttribute(): bool
    {
        $ancestro = static::consultarEstatusAncestros((int) $this->par_id);

        return $ancestro
            && (int) $ancestro->parroquia_status_id === self::STATUS_ACTIVO
            && (int) $ancestro->municipio_status_id === self::STATUS_ACTIVO
            && (int) $ancestro->estado_status_id === self::STATUS_ACTIVO
            && (int) $ancestro->pais_status_id === self::STATUS_ACTIVO;
    }

    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(Parroquia::class, 'par_id', 'id_par');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $mun_id = $filtros['mun_id'] ?? null;
        $par_id = $filtros['par_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

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
                'parroquia.status_id as parroquia_status_id',
                'municipio.id_mun as municipio_id_mun',
                'municipio.nombre_mun as municipio_nombre_mun',
                'municipio.status_id as municipio_status_id',
                'estado.status_id as estado_status_id',
                'pais.status_id as pais_status_id'
            )
            ->whereNull('localidad.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('localidad.status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('localidad.nombre_loc', 'ilike', "%{$search}%")
                    ->orWhere('parroquia.nombre_par', 'ilike', "%{$search}%")
                    ->orWhere('municipio.nombre_mun', 'ilike', "%{$search}%");
            });
        }

        if (! empty($par_id)) {
            $query->where('localidad.par_id', (int) $par_id);
        }

        if (! empty($mun_id)) {
            $query->where('parroquia.mun_id', (int) $mun_id);
        }

        $paginator = $query->orderBy('localidad.id_loc', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->puede_activarse = ((int) ($item->parroquia_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->municipio_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->estado_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->pais_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO;

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
                $item->parroquia_status_id,
                $item->municipio_id_mun,
                $item->municipio_nombre_mun,
                $item->municipio_status_id,
                $item->estado_status_id,
                $item->pais_status_id
            );

            return $item;
        });

        return $paginator;
    }

    public static function registrarLocalidad(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_loc' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_loc'])), MB_CASE_TITLE, 'UTF-8'),
            'par_id'     => (int) $datos['par_id'],
            'status_id'  => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('localidad')->insertGetId($insertData, 'id_loc');

        return static::obtenerPorId($id);
    }

    public static function crearLocalidad(array $datos, ?int $userId = null): object
    {
        return static::registrarLocalidad($datos, $userId);
    }

    public static function actualizarLocalidad(int $id_loc, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (isset($datos['nombre_loc'])) {
            $updateData['nombre_loc'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_loc'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['par_id'])) {
            $updateData['par_id'] = (int) $datos['par_id'];
        }

        if (isset($datos['status_id'])) {
            $updateData['status_id'] = (int) $datos['status_id'];
        } elseif (isset($datos['status'])) {
            $updateData['status_id'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN)
                ? self::STATUS_ACTIVO
                : self::STATUS_INACTIVO;
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('localidad')
            ->where('id_loc', $id_loc)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_loc);
    }

    public static function obtenerPorId(int $id_loc): object
    {
        $localidad = DB::table('localidad')
            ->where('id_loc', $id_loc)
            ->whereNull('deleted_at')
            ->first();

        if (! $localidad) {
            abort(404, 'Localidad no encontrada.');
        }

        return $localidad;
    }

    public static function obtenerParaEditar(int $id_loc): object
    {
        $localidad = static::obtenerPorId($id_loc);

        $localidad->parroquia = DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id', 'status_id'])
            ->where('id_par', $localidad->par_id)
            ->first();

        return $localidad;
    }

    public static function obtenerConRelaciones(int $id_loc): object
    {
        $localidad = static::obtenerPorId($id_loc);

        $parroquia = DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id', 'status_id'])
            ->where('id_par', $localidad->par_id)
            ->first();

        if ($parroquia) {
            $municipio = DB::table('municipio')
                ->select(['id_mun', 'nombre_mun', 'esd_id', 'status_id'])
                ->where('id_mun', $parroquia->mun_id)
                ->first();

            if ($municipio) {
                $estado = DB::table('estado')
                    ->select(['id_esd', 'nombre_esd', 'pai_id', 'status_id'])
                    ->where('id_esd', $municipio->esd_id)
                    ->first();

                if ($estado) {
                    $estado->pais = DB::table('pais')
                        ->select(['id_pai', 'nombre_pai', 'status_id'])
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

    public static function obtenerActivosPorParroquia(int $par_id): Collection
    {
        return DB::table('localidad')
            ->select(['id_loc', 'nombre_loc', 'par_id'])
            ->where('par_id', $par_id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_loc', 'asc')
            ->get();
    }

    public static function obtenerActivosConJerarquia(): Collection
    {
        $localidades = DB::table('localidad')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('localidad.status_id', self::STATUS_ACTIVO)
            ->whereNull('localidad.deleted_at')
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

    public static function alternarEstatus(int $id_loc, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_loc, $userId) {
            $localidad = static::obtenerPorId($id_loc);
            $activar = ! static::esStatusActivo($localidad);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();

            if ($activar) {
                $ancestro = static::consultarEstatusAncestros((int) $localidad->par_id);
                if (
                    ! $ancestro
                    || (int) $ancestro->parroquia_status_id !== self::STATUS_ACTIVO
                    || (int) $ancestro->municipio_status_id !== self::STATUS_ACTIVO
                    || (int) $ancestro->estado_status_id !== self::STATUS_ACTIVO
                    || (int) $ancestro->pais_status_id !== self::STATUS_ACTIVO
                ) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            DB::table('localidad')
                ->where('id_loc', $id_loc)
                ->update([
                    'status_id'  => $nuevoStatusId,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $localidad->nombre_loc,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function alternarEstatusConCascada(int $id_loc, ?int $userId = null): array
    {
        return static::alternarEstatus($id_loc, $userId);
    }

    public static function desactivar(int $id_loc, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_loc, $userId) {
            $localidad = static::obtenerPorId($id_loc);
            $usuarioId = $userId ?? auth()->id();

            DB::table('localidad')
                ->where('id_loc', $id_loc)
                ->update([
                    'status_id'  => self::STATUS_INACTIVO,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $localidad->nombre_loc,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }

    public static function desactivarConCascada(int $id_loc, ?int $userId = null): array
    {
        return static::desactivar($id_loc, $userId);
    }

    private static function consultarEstatusAncestros(int $par_id): ?object
    {
        return DB::table('parroquia')
            ->join('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('parroquia.id_par', $par_id)
            ->select(
                'parroquia.status_id as parroquia_status_id',
                'municipio.status_id as municipio_status_id',
                'estado.status_id as estado_status_id',
                'pais.status_id as pais_status_id'
            )
            ->first();
    }
}
