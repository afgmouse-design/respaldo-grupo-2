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
 * Modelo: Municipio (tabla `municipio`).
 *
 * Propósito: Representa una división municipal perteneciente a un estado (`esd_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, sin columna `status`).
 *  - 2026-11-16: Añadido `eliminarConCascada` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    sin eliminar físicamente registros de PostgreSQL.
 */
class Municipio extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'municipio';
    protected $primaryKey = 'id_mun';
    protected $moduloAuditoria = 'Municipio';

    /**
     * Elimina lógicamente el municipio de la interfaz (status_id = 2 + deleted_at)
     * propagando la baja a parroquias y localidades dependientes.
     */
    public static function eliminarConCascada(int $id_mun, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_mun, $userId) {
            $municipio = static::find($id_mun);
            if (! $municipio) {
                abort(404, 'Municipio no encontrado.');
            }

            $nombre = $municipio->nombre_mun;

            static::ejecutarBajaLogicaConHijos(
                $municipio,
                array_slice(Pais::NIVELES_CASCADA_TERRITORIAL, 2),
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
        'nombre_mun',
        'esd_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_mun'    => 'integer',
        'esd_id'    => 'integer',
        'status_id' => 'integer',
    ];

    protected $appends = [
        'puede_activarse',
    ];

    public function setNombreMunAttribute($value): void
    {
        $this->attributes['nombre_mun'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function getPuedeActivarseAttribute(): bool
    {
        $estado = DB::table('estado')
            ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->where('estado.id_esd', $this->esd_id)
            ->select('estado.status_id as estado_status_id', 'pais.status_id as pais_status_id')
            ->first();

        return $estado
            && (int) $estado->estado_status_id === self::STATUS_ACTIVO
            && (int) $estado->pais_status_id === self::STATUS_ACTIVO;
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'esd_id', 'id_esd');
    }

    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class, 'mun_id', 'id_mun');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $pai_id = $filtros['pai_id'] ?? null;
        $esd_id = $filtros['esd_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $query = DB::table('municipio')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'municipio.*',
                'estado.id_esd as estado_id_esd',
                'estado.nombre_esd as estado_nombre_esd',
                'estado.pai_id as estado_pai_id',
                'estado.status_id as estado_status_id',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status_id as pais_status_id'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM parroquia WHERE parroquia.mun_id = municipio.id_mun AND parroquia.deleted_at IS NULL) as parroquias_count')
            ->whereNull('municipio.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('municipio.status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('municipio.nombre_mun', 'ilike', "%{$search}%")
                    ->orWhere('estado.nombre_esd', 'ilike', "%{$search}%")
                    ->orWhere('pais.nombre_pai', 'ilike', "%{$search}%");
            });
        }

        if (! empty($esd_id)) {
            $query->where('municipio.esd_id', (int) $esd_id);
        }

        if (! empty($pai_id)) {
            $query->where('estado.pai_id', (int) $pai_id);
        }

        $paginator = $query->orderBy('municipio.id_mun', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->puede_activarse = ((int) ($item->estado_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO
                && ((int) ($item->pais_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO;

            $item->estado = $item->estado_id_esd ? (object) [
                'id_esd'     => $item->estado_id_esd,
                'nombre_esd' => $item->estado_nombre_esd,
                'pai_id'     => $item->estado_pai_id,
                'status_id'  => (int) $item->estado_status_id,
                'pais'       => $item->pais_id_pai ? (object) [
                    'id_pai'     => $item->pais_id_pai,
                    'nombre_pai' => $item->pais_nombre_pai,
                    'status_id'  => (int) $item->pais_status_id,
                ] : null,
            ] : null;

            unset(
                $item->estado_id_esd,
                $item->estado_nombre_esd,
                $item->estado_pai_id,
                $item->estado_status_id,
                $item->pais_id_pai,
                $item->pais_nombre_pai,
                $item->pais_status_id
            );

            return $item;
        });

        return $paginator;
    }

    public static function registrarMunicipio(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_mun' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_mun'])), MB_CASE_TITLE, 'UTF-8'),
            'esd_id'     => (int) $datos['esd_id'],
            'status_id'  => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('municipio')->insertGetId($insertData, 'id_mun');

        return static::obtenerPorId($id);
    }

    public static function crearMunicipio(array $datos, ?int $userId = null): object
    {
        return static::registrarMunicipio($datos, $userId);
    }

    public static function actualizarMunicipio(int $id_mun, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (isset($datos['nombre_mun'])) {
            $updateData['nombre_mun'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_mun'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['esd_id'])) {
            $updateData['esd_id'] = (int) $datos['esd_id'];
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

        DB::table('municipio')
            ->where('id_mun', $id_mun)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_mun);
    }

    public static function obtenerPorId(int $id_mun): object
    {
        $municipio = DB::table('municipio')
            ->where('id_mun', $id_mun)
            ->whereNull('deleted_at')
            ->first();

        if (! $municipio) {
            abort(404, 'Municipio no encontrado.');
        }

        return $municipio;
    }

    public static function obtenerParaEditar(int $id_mun): object
    {
        $municipio = static::obtenerPorId($id_mun);

        $municipio->estado = DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'pai_id', 'status_id'])
            ->where('id_esd', $municipio->esd_id)
            ->first();

        return $municipio;
    }

    public static function obtenerConRelaciones(int $id_mun): object
    {
        $municipio = static::obtenerPorId($id_mun);

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

        $municipio->parroquias = DB::table('parroquia')
            ->select('parroquia.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM localidad WHERE localidad.par_id = parroquia.id_par AND localidad.deleted_at IS NULL) as localidades_count')
            ->where('mun_id', $id_mun)
            ->whereNull('deleted_at')
            ->orderBy('nombre_par', 'asc')
            ->get();

        return $municipio;
    }

    public static function obtenerActivosPorEstado(int $esd_id): Collection
    {
        return DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id'])
            ->where('esd_id', $esd_id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_mun', 'asc')
            ->get();
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('municipio')
            ->select(['id_mun', 'nombre_mun', 'esd_id'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_mun', 'asc')
            ->get();
    }

    public static function alternarEstatusConCascada(int $id_mun, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_mun, $userId) {
            $municipio = static::obtenerPorId($id_mun);
            $activar = ! static::esStatusActivo($municipio);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();

            if ($activar) {
                $estado = DB::table('estado')
                    ->join('pais', 'pais.id_pai', '=', 'estado.pai_id')
                    ->where('estado.id_esd', $municipio->esd_id)
                    ->select('estado.status_id as estado_status_id', 'pais.status_id as pais_status_id')
                    ->first();

                if (! $estado || (int) $estado->estado_status_id !== self::STATUS_ACTIVO || (int) $estado->pais_status_id !== self::STATUS_ACTIVO) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            static::aplicarStatusCascadaMunicipio($id_mun, $nuevoStatusId, $usuarioId);

            return [
                'nombre'    => $municipio->nombre_mun,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function desactivarConCascada(int $id_mun, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_mun, $userId) {
            $municipio = static::obtenerPorId($id_mun);
            $usuarioId = $userId ?? auth()->id();

            static::aplicarStatusCascadaMunicipio($id_mun, self::STATUS_INACTIVO, $usuarioId);

            return [
                'nombre'    => $municipio->nombre_mun,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }

    private static function aplicarStatusCascadaMunicipio(int $id_mun, int $nuevoStatusId, ?int $usuarioId): void
    {
        $now = now();
        $payload = ['status_id' => $nuevoStatusId, 'updated_id' => $usuarioId, 'updated_at' => $now];

        DB::table('municipio')->where('id_mun', $id_mun)->update($payload);

        $parroquiaIds = DB::table('parroquia')->where('mun_id', $id_mun)->pluck('id_par');
        if ($parroquiaIds->isNotEmpty()) {
            DB::table('parroquia')->whereIn('id_par', $parroquiaIds)->update($payload);
            DB::table('localidad')->whereIn('par_id', $parroquiaIds)->update($payload);
        }
    }
}
