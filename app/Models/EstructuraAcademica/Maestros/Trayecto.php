<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Academico\TipoLapso;
use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Trayecto / Nivel Académico (tabla `trayecto`).
 *
 * Propósito: Define los trayectos o tramos académicos pertenecientes a un tipo de lapso (`tla_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`tla_id -> tipo_lapso.id_tla`, `status_id`,
 *    `SoftDeletes`, `users.username`, sin `tpd_id` ni `status` booleano).
 *  - 2026-11-16: Añadido `eliminarConSoftDelete` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    sin eliminar registros físicos de PostgreSQL.
 */
class Trayecto extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'trayecto';
    protected $primaryKey = 'id_tra';
    protected $moduloAuditoria = 'Trayecto';

    protected $fillable = [
        'codigo_tra',
        'nombre_tra',
        'numero_orden_tra',
        'tla_id',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_tra'           => 'integer',
        'numero_orden_tra' => 'integer',
        'tla_id'           => 'integer',
        'status_id'        => 'integer',
    ];

    public function setNombreTraAttribute($value): void
    {
        $this->attributes['nombre_tra'] = trim((string) $value);
    }

    public function setCodigoTraAttribute($value): void
    {
        $this->attributes['codigo_tra'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function tipoLapso(): BelongsTo
    {
        return $this->belongsTo(TipoLapso::class, 'tla_id', 'id_tla');
    }

    public function semestres(): HasMany
    {
        return $this->hasMany(Semestre::class, 'tra_id', 'id_tra');
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

    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $tlaId = $filtros['tla_id'] ?? $filtros['tpd_id'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $query = DB::table('trayecto')
            ->leftJoin('tipo_lapso', 'tipo_lapso.id_tla', '=', 'trayecto.tla_id')
            ->select([
                'trayecto.id_tra',
                'trayecto.codigo_tra',
                'trayecto.nombre_tra',
                'trayecto.numero_orden_tra',
                'trayecto.tla_id',
                'trayecto.status_id',
                'trayecto.created_id',
                'trayecto.updated_id',
                'trayecto.created_at',
                'trayecto.updated_at',
                'tipo_lapso.nombre_tla',
                'tipo_lapso.codigo_tla',
                'tipo_lapso.status_id as tipo_lapso_status_id',
            ])
            ->whereNull('trayecto.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('trayecto.status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('trayecto.nombre_tra', 'ilike', "%{$search}%")
                    ->orWhere('trayecto.codigo_tra', 'ilike', "%{$search}%")
                    ->orWhere('tipo_lapso.nombre_tla', 'ilike', "%{$search}%");
            });
        }

        if (! empty($tlaId) && $tlaId !== 'all') {
            $query->where('trayecto.tla_id', (int) $tlaId);
        }

        $paginator = $query->orderBy('trayecto.id_tra', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_tra = (int) $item->id_tra;
            $item->numero_orden_tra = (int) $item->numero_orden_tra;
            $item->tla_id = (int) $item->tla_id;
            $item->status_id = (int) $item->status_id;

            $item->tipo_lapso = $item->tla_id ? (object) [
                'id_tla'     => (int) $item->tla_id,
                'nombre_tla' => $item->nombre_tla,
                'codigo_tla' => $item->codigo_tla,
                'status_id'  => (int) $item->tipo_lapso_status_id,
            ] : null;

            return $item;
        });

        return $paginator;
    }

    public static function registrarTrayecto(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_tra'       => trim((string) $datos['nombre_tra']),
            'codigo_tra'       => ! empty($datos['codigo_tra']) ? mb_strtoupper(trim((string) $datos['codigo_tra']), 'UTF-8') : null,
            'numero_orden_tra' => isset($datos['numero_orden_tra']) ? (int) $datos['numero_orden_tra'] : 1,
            'tla_id'           => (int) ($datos['tla_id'] ?? $datos['tpd_id']),
            'status_id'        => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('trayecto')->insertGetId($insertData, 'id_tra');

        return static::obtenerPorId($id);
    }

    public static function crearTrayecto(array $datos, ?int $userId = null): object
    {
        return static::registrarTrayecto($datos, $userId);
    }

    public static function actualizarTrayecto(int $id_tra, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (array_key_exists('nombre_tra', $datos)) {
            $updateData['nombre_tra'] = trim((string) $datos['nombre_tra']);
        }
        if (array_key_exists('codigo_tra', $datos)) {
            $updateData['codigo_tra'] = ! empty($datos['codigo_tra']) ? mb_strtoupper(trim((string) $datos['codigo_tra']), 'UTF-8') : null;
        }
        if (array_key_exists('numero_orden_tra', $datos)) {
            $updateData['numero_orden_tra'] = (int) $datos['numero_orden_tra'];
        }
        if (array_key_exists('tla_id', $datos) || array_key_exists('tpd_id', $datos)) {
            $updateData['tla_id'] = (int) ($datos['tla_id'] ?? $datos['tpd_id']);
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

        DB::table('trayecto')
            ->where('id_tra', $id_tra)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_tra);
    }

    public static function obtenerPorId(int $id_tra): object
    {
        $trayecto = DB::table('trayecto')
            ->leftJoin('tipo_lapso', 'tipo_lapso.id_tla', '=', 'trayecto.tla_id')
            ->select([
                'trayecto.id_tra',
                'trayecto.codigo_tra',
                'trayecto.nombre_tra',
                'trayecto.numero_orden_tra',
                'trayecto.tla_id',
                'trayecto.status_id',
                'trayecto.created_id',
                'trayecto.updated_id',
                'trayecto.created_at',
                'trayecto.updated_at',
                'tipo_lapso.nombre_tla',
                'tipo_lapso.codigo_tla',
                'tipo_lapso.status_id as tipo_lapso_status_id',
            ])
            ->where('trayecto.id_tra', $id_tra)
            ->whereNull('trayecto.deleted_at')
            ->first();

        if (! $trayecto) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id_tra]);
        }

        $trayecto->id_tra = (int) $trayecto->id_tra;
        $trayecto->numero_orden_tra = (int) $trayecto->numero_orden_tra;
        $trayecto->tla_id = (int) $trayecto->tla_id;
        $trayecto->status_id = (int) $trayecto->status_id;

        $trayecto->tipo_lapso = $trayecto->tla_id ? (object) [
            'id_tla'     => (int) $trayecto->tla_id,
            'nombre_tla' => $trayecto->nombre_tla,
            'codigo_tla' => $trayecto->codigo_tla,
            'status_id'  => (int) $trayecto->tipo_lapso_status_id,
        ] : null;

        return $trayecto;
    }

    public static function obtenerParaEditar(int $id_tra): object
    {
        return static::obtenerPorId($id_tra);
    }

    public static function obtenerConRelaciones(int $id_tra): object
    {
        $trayecto = static::obtenerPorId($id_tra);

        $trayecto->creador = $trayecto->created_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $trayecto->created_id)->first()
            : null;

        $trayecto->actualizador = $trayecto->updated_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $trayecto->updated_id)->first()
            : null;

        return $trayecto;
    }

    public static function obtenerActivosParaSelect(?int $tlaId = null): Collection
    {
        $query = DB::table('trayecto')
            ->select([
                'id_tra',
                'nombre_tra',
                'codigo_tra',
                'numero_orden_tra',
                'tla_id',
            ])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at');

        if ($tlaId) {
            $query->where('tla_id', $tlaId);
        }

        return $query->orderBy('numero_orden_tra', 'asc')
            ->orderBy('nombre_tra', 'asc')
            ->get();
    }

    public static function alternarEstatus(int $id_tra, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_tra, $userId) {
            $trayecto = DB::table('trayecto')
                ->leftJoin('tipo_lapso', 'tipo_lapso.id_tla', '=', 'trayecto.tla_id')
                ->select([
                    'trayecto.id_tra',
                    'trayecto.nombre_tra',
                    'trayecto.status_id',
                    'trayecto.tla_id',
                    'tipo_lapso.nombre_tla',
                    'tipo_lapso.status_id as tipo_lapso_status_id',
                ])
                ->where('trayecto.id_tra', $id_tra)
                ->whereNull('trayecto.deleted_at')
                ->first();

            if (! $trayecto) {
                throw (new ModelNotFoundException)->setModel(static::class, [$id_tra]);
            }

            $activar = ! static::esStatusActivo($trayecto);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;

            if ($activar && $trayecto->tla_id && (int) $trayecto->tipo_lapso_status_id !== self::STATUS_ACTIVO) {
                $nombrePadre = $trayecto->nombre_tla ?? 'asociada';
                throw new \DomainException(
                    "No se puede activar el trayecto '{$trayecto->nombre_tra}' porque su tipo de lapso '{$nombrePadre}' está inactivo."
                );
            }

            DB::table('trayecto')
                ->where('id_tra', $id_tra)
                ->update([
                    'status_id'  => $nuevoStatusId,
                    'updated_id' => $userId ?? auth()->id(),
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $trayecto->nombre_tra,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    /**
     * Elimina lógicamente el trayecto de la interfaz (`deleted_at` + `status_id = 2`)
     * preservando el registro histórico en PostgreSQL.
     */
    public static function eliminarConSoftDelete(int $id_tra, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_tra, $userId) {
            $trayecto = static::find($id_tra);
            if (! $trayecto) {
                throw (new ModelNotFoundException)->setModel(static::class, [$id_tra]);
            }

            $nombre = $trayecto->nombre_tra;

            static::ejecutarBajaLogicaModelo(
                $trayecto,
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
