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
 * Modelo: Estado (tabla `estado`).
 *
 * Propósito: Representa una entidad federal perteneciente a un país (`pai_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `iso_esd`, `SoftDeletes`, sin columna `status`).
 *  - 2026-11-16: Añadido `eliminarConCascada` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    sin eliminar físicamente registros de PostgreSQL.
 */
class Estado extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'estado';
    protected $primaryKey = 'id_esd';
    protected $moduloAuditoria = 'Estado';

    /**
     * Elimina lógicamente el estado de la interfaz (status_id = 2 + deleted_at)
     * propagando la baja a municipios, parroquias y localidades dependientes.
     */
    public static function eliminarConCascada(int $id_esd, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esd, $userId) {
            $estado = static::find($id_esd);
            if (! $estado) {
                abort(404, 'Estado no encontrado.');
            }

            $nombre = $estado->nombre_esd;

            static::ejecutarBajaLogicaConHijos(
                $estado,
                array_slice(Pais::NIVELES_CASCADA_TERRITORIAL, 1),
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
        'pai_id',
        'nombre_esd',
        'iso_esd',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_esd'    => 'integer',
        'pai_id'    => 'integer',
        'status_id' => 'integer',
    ];

    protected $appends = [
        'puede_activarse',
    ];

    public function setNombreEsdAttribute($value): void
    {
        $this->attributes['nombre_esd'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function getPuedeActivarseAttribute(): bool
    {
        if ($this->relationLoaded('pais') && $this->pais) {
            return static::esStatusActivo($this->pais);
        }

        return (int) DB::table('pais')->where('id_pai', $this->pai_id)->value('status_id') === self::STATUS_ACTIVO;
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pai_id', 'id_pai');
    }

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'esd_id', 'id_esd');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $pai_id = $filtros['pai_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $paginator = DB::table('estado')
            ->leftJoin('pais', 'pais.id_pai', '=', 'estado.pai_id')
            ->select(
                'estado.*',
                'pais.id_pai as pais_id_pai',
                'pais.nombre_pai as pais_nombre_pai',
                'pais.status_id as pais_status_id'
            )
            ->selectRaw('(SELECT COUNT(*)::int FROM municipio WHERE municipio.esd_id = estado.id_esd AND municipio.deleted_at IS NULL) as municipios_count')
            ->whereNull('estado.deleted_at')
            ->when($statusId !== null, fn ($query) => $query->where('estado.status_id', $statusId))
            ->when(! empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('estado.nombre_esd', 'ilike', "%{$search}%")
                        ->orWhere('estado.iso_esd', 'ilike', "%{$search}%")
                        ->orWhere('pais.nombre_pai', 'ilike', "%{$search}%");
                });
            })
            ->when(! empty($pai_id), fn ($query) => $query->where('estado.pai_id', (int) $pai_id))
            ->orderBy('estado.id_esd', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->puede_activarse = ((int) ($item->pais_status_id ?? self::STATUS_INACTIVO)) === self::STATUS_ACTIVO;
            $item->pais = $item->pais_id_pai ? (object) [
                'id_pai'     => $item->pais_id_pai,
                'nombre_pai' => $item->pais_nombre_pai,
                'status_id'  => (int) $item->pais_status_id,
            ] : null;

            unset($item->pais_id_pai, $item->pais_nombre_pai, $item->pais_status_id);

            return $item;
        });

        return $paginator;
    }

    public static function registrarEstado(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_esd' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_esd'])), MB_CASE_TITLE, 'UTF-8'),
            'pai_id'     => (int) $datos['pai_id'],
            'iso_esd'    => isset($datos['iso_esd']) ? strtoupper(trim((string) $datos['iso_esd'])) : null,
            'status_id'  => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('estado')->insertGetId($insertData, 'id_esd');

        return static::obtenerPorId($id);
    }

    public static function crearEstado(array $datos, ?int $userId = null): object
    {
        return static::registrarEstado($datos, $userId);
    }

    public static function actualizarEstado(int $id_esd, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (isset($datos['nombre_esd'])) {
            $updateData['nombre_esd'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_esd'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['pai_id'])) {
            $updateData['pai_id'] = (int) $datos['pai_id'];
        }

        if (array_key_exists('iso_esd', $datos)) {
            $updateData['iso_esd'] = $datos['iso_esd'] !== null ? strtoupper(trim((string) $datos['iso_esd'])) : null;
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

        DB::table('estado')
            ->where('id_esd', $id_esd)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_esd);
    }

    public static function obtenerPorId(int $id_esd): object
    {
        $estado = DB::table('estado')
            ->where('id_esd', $id_esd)
            ->whereNull('deleted_at')
            ->first();

        if (! $estado) {
            abort(404, 'Estado no encontrado.');
        }

        return $estado;
    }

    public static function obtenerConRelaciones(int $id_esd): object
    {
        $estado = static::obtenerPorId($id_esd);

        $estado->pais = DB::table('pais')
            ->select(['id_pai', 'nombre_pai', 'status_id'])
            ->where('id_pai', $estado->pai_id)
            ->first();

        $estado->municipios = DB::table('municipio')
            ->select('municipio.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM parroquia WHERE parroquia.mun_id = municipio.id_mun AND parroquia.deleted_at IS NULL) as parroquias_count')
            ->where('esd_id', $id_esd)
            ->whereNull('deleted_at')
            ->orderBy('nombre_mun', 'asc')
            ->get();

        return $estado;
    }

    public static function obtenerActivosPorPais(int $pai_id): Collection
    {
        return DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'iso_esd', 'pai_id'])
            ->where('pai_id', $pai_id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_esd', 'asc')
            ->get();
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('estado')
            ->select(['id_esd', 'nombre_esd', 'iso_esd', 'pai_id'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_esd', 'asc')
            ->get();
    }

    public static function alternarEstatusConCascada(int $id_esd, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esd, $userId) {
            $estado = static::obtenerPorId($id_esd);
            $activar = ! static::esStatusActivo($estado);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();

            if ($activar) {
                $paisStatusId = (int) DB::table('pais')->where('id_pai', $estado->pai_id)->value('status_id');
                if ($paisStatusId !== self::STATUS_ACTIVO) {
                    throw new \DomainException('No se puede activar este registro porque su entidad superior se encuentra inactiva.');
                }
            }

            static::aplicarStatusCascadaEstado($id_esd, $nuevoStatusId, $usuarioId);

            return [
                'nombre'    => $estado->nombre_esd,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function desactivarConCascada(int $id_esd, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esd, $userId) {
            $estado = static::obtenerPorId($id_esd);
            $usuarioId = $userId ?? auth()->id();

            static::aplicarStatusCascadaEstado($id_esd, self::STATUS_INACTIVO, $usuarioId);

            return [
                'nombre'    => $estado->nombre_esd,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }

    private static function aplicarStatusCascadaEstado(int $id_esd, int $nuevoStatusId, ?int $usuarioId): void
    {
        $now = now();
        $payload = ['status_id' => $nuevoStatusId, 'updated_id' => $usuarioId, 'updated_at' => $now];

        DB::table('estado')->where('id_esd', $id_esd)->update($payload);

        $municipioIds = DB::table('municipio')->where('esd_id', $id_esd)->pluck('id_mun');
        if ($municipioIds->isEmpty()) {
            return;
        }
        DB::table('municipio')->whereIn('id_mun', $municipioIds)->update($payload);

        $parroquiaIds = DB::table('parroquia')->whereIn('mun_id', $municipioIds)->pluck('id_par');
        if ($parroquiaIds->isEmpty()) {
            return;
        }
        DB::table('parroquia')->whereIn('id_par', $parroquiaIds)->update($payload);
        DB::table('localidad')->whereIn('par_id', $parroquiaIds)->update($payload);
    }
}
