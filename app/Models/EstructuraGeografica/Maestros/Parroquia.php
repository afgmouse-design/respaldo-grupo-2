<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Parroquia (tabla `parroquia`).
 *
 * Propósito: Representa una división parroquial perteneciente a un municipio (`mun_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, sin columna `status`).
 *  - 2026-11-16: Añadido `eliminarConCascada` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    sin eliminar físicamente registros de PostgreSQL.
 */
class Parroquia extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'parroquia';
    protected $primaryKey = 'id_par';
    protected $moduloAuditoria = 'Parroquia';

    /**
     * Elimina lógicamente la parroquia de la interfaz (status_id = 2 + deleted_at)
     * propagando la baja a las localidades dependientes.
     */
    public static function eliminarConCascada(int $id_par, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_par, $userId) {
            $parroquia = static::find($id_par);
            if (! $parroquia) {
                abort(404, 'Parroquia no encontrada.');
            }

            $nombre = $parroquia->nombre_par;

            static::ejecutarBajaLogicaConHijos(
                $parroquia,
                array_slice(Pais::NIVELES_CASCADA_TERRITORIAL, 3),
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
        'nombre_par',
        'mun_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_par'    => 'integer',
        'mun_id'    => 'integer',
        'status_id' => 'integer',
    ];

    protected $appends = [
        'puede_activarse',
    ];

    public function setNombreParAttribute($value): void
    {
        $this->attributes['nombre_par'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function getPuedeActivarseAttribute(): bool
    {
        $municipio = DB::table('municipio')
            ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('municipio.id_mun', $this->mun_id)
            ->select(
                'municipio.status_id as municipio_status_id',
                'estado.status_id as estado_status_id',
                'pais.status_id as pais_status_id'
            )
            ->first();

        return $municipio
            && (int) $municipio->municipio_status_id === self::STATUS_ACTIVO
            && (int) $municipio->estado_status_id === self::STATUS_ACTIVO
            && (int) $municipio->pais_status_id === self::STATUS_ACTIVO;
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'mun_id', 'id_mun');
    }

    public function localidades(): HasMany
    {
        return $this->hasMany(Localidad::class, 'par_id', 'id_par');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $esd_id = $filtros['esd_id'] ?? null;
        $mun_id = $filtros['mun_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $query = DB::table('parroquia')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'parroquia.*',
                'municipio.id_mun as municipio_id_mun',
                'municipio.nombre_mun as municipio_nombre_mun',
                'municipio.esd_id as municipio_esd_id',
                'municipio.status_id as municipio_status_id',
                'estado.id_esd as estado_id_esd',
                'estado.nombre_esd as estado_nombre_esd',
                'estado.status_id as estado_status_id',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status_id as pais_status_id'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM localidad WHERE localidad.par_id = parroquia.id_par AND localidad.deleted_at IS NULL) as localidades_count')
            ->whereNull('parroquia.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('parroquia.status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('parroquia.nombre_par', 'ilike', "%{$search}%")
                    ->orWhere('municipio.nombre_mun', 'ilike', "%{$search}%")
                    ->orWhere('estado.nombre_esd', 'ilike', "%{$search}%");
            });
        }

        if (! empty($mun_id)) {
            $query->where('parroquia.mun_id', (int) $mun_id);
        }

        if (! empty($esd_id)) {
            $query->where('municipio.esd_id', (int) $esd_id);
        }

        $paginator = $query->orderBy('parroquia.id_par', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->puede_activarse = ((int) ($item->municipio_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->estado_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->pais_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO;

            $item->municipio = $item->municipio_id_mun ? (object) [
                'id_mun'     => $item->municipio_id_mun,
                'nombre_mun' => $item->municipio_nombre_mun,
                'esd_id'     => $item->municipio_esd_id,
                'status_id'  => (int) $item->municipio_status_id,
                'estado'     => $item->estado_id_esd ? (object) [
                    'id_esd'     => $item->estado_id_esd,
                    'nombre_esd' => $item->estado_nombre_esd,
                    'status_id'  => (int) $item->estado_status_id,
                ] : null,
            ] : null;

            unset(
                $item->municipio_id_mun,
                $item->municipio_nombre_mun,
                $item->municipio_esd_id,
                $item->municipio_status_id,
                $item->estado_id_esd,
                $item->estado_nombre_esd,
                $item->estado_status_id,
                $item->pais_id_pai,
                $item->pais_nombre_pai,
                $item->pais_status_id
            );

            return $item;
        });

        return $paginator;
    }

    public static function registrarParroquia(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_par' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_par'])), MB_CASE_TITLE, 'UTF-8'),
            'mun_id'     => (int) $datos['mun_id'],
            'status_id'  => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('parroquia')->insertGetId($insertData, 'id_par');

        return static::obtenerPorId($id);
    }

    public static function crearParroquia(array $datos, ?int $userId = null): object
    {
        return static::registrarParroquia($datos, $userId);
    }

    public static function actualizarParroquia(int $id_par, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (isset($datos['nombre_par'])) {
            $updateData['nombre_par'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_par'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['mun_id'])) {
            $updateData['mun_id'] = (int) $datos['mun_id'];
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

        DB::table('parroquia')
            ->where('id_par', $id_par)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_par);
    }

    public static function obtenerPorId(int $id_par): object
    {
        $parroquia = DB::table('parroquia')
            ->where('id_par', $id_par)
            ->whereNull('deleted_at')
            ->first();

        if (! $parroquia) {
            abort(404, 'Parroquia no encontrada.');
        }

        return $parroquia;
    }

    public static function obtenerParaEditar(int $id_par): object
    {
        $parroquia = static::obtenerPorId($id_par);

        $parroquia->municipio = DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id', 'status_id'])
            ->where('id_mun', $parroquia->mun_id)
            ->first();

        return $parroquia;
    }

    public static function obtenerConRelaciones(int $id_par): object
    {
        $parroquia = static::obtenerPorId($id_par);

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

        $parroquia->localidades = DB::table('localidad')
            ->select('localidad.*')
            ->where('par_id', $id_par)
            ->whereNull('deleted_at')
            ->orderBy('nombre_loc', 'asc')
            ->get();

        return $parroquia;
    }

    public static function obtenerActivosPorMunicipio(int $mun_id): Collection
    {
        return DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id'])
            ->where('mun_id', $mun_id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_par', 'asc')
            ->get();
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('parroquia')
            ->select(['id_par', 'nombre_par', 'mun_id'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_par', 'asc')
            ->get();
    }

    public static function alternarEstatusConCascada(int $id_par, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_par, $userId) {
            $parroquia = static::obtenerPorId($id_par);
            $activar = ! static::esStatusActivo($parroquia);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();

            if ($activar) {
                $municipio = DB::table('municipio')
                    ->join('estado', 'estado.id_esd', '=', 'municipio.esd_id')
                    ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
                    ->where('municipio.id_mun', $parroquia->mun_id)
                    ->select(
                        'municipio.status_id as municipio_status_id',
                        'estado.status_id as estado_status_id',
                        'pais.status_id as pais_status_id'
                    )
                    ->first();

                if (
                    ! $municipio
                    || (int) $municipio->municipio_status_id !== self::STATUS_ACTIVO
                    || (int) $municipio->estado_status_id !== self::STATUS_ACTIVO
                    || (int) $municipio->pais_status_id !== self::STATUS_ACTIVO
                ) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            static::aplicarStatusCascadaParroquia($id_par, $nuevoStatusId, $usuarioId);

            return [
                'nombre'    => $parroquia->nombre_par,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function desactivarConCascada(int $id_par, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_par, $userId) {
            $parroquia = static::obtenerPorId($id_par);
            $usuarioId = $userId ?? auth()->id();

            static::aplicarStatusCascadaParroquia($id_par, self::STATUS_INACTIVO, $usuarioId);

            return [
                'nombre'    => $parroquia->nombre_par,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }

    private static function aplicarStatusCascadaParroquia(int $id_par, int $nuevoStatusId, ?int $usuarioId): void
    {
        $now = now();
        $payload = ['status_id' => $nuevoStatusId, 'updated_id' => $usuarioId, 'updated_at' => $now];

        DB::table('parroquia')->where('id_par', $id_par)->update($payload);
        DB::table('localidad')->where('par_id', $id_par)->update($payload);
    }
}
