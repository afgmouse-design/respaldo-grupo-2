<?php

namespace App\Models\EstructuraAcademica\Maestros;

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
 * Modelo: Escala de Medición (tabla `escala_medicion`).
 *
 * Propósito: Define los esquemas y rangos de evaluación y calificación académica.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id`, `SoftDeletes`, `users.username`,
 *    eliminación de columna booleana `status` y corrección de filtro dinámico de estatus).
 *  - 2026-11-16: Añadido `eliminarConSoftDelete` para baja lógica en interfaz (`deleted_at` +
 *    `status_id = 2`) manteniendo el registro histórico en PostgreSQL.
 */
class EscalaMedicion extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'escala_medicion';
    protected $primaryKey = 'id_esm';
    protected $moduloAuditoria = 'EscalaMedicion';

    protected $fillable = [
        'nombre_esm',
        'nota_aprobatoria_esm',
        'nota_minima_esm',
        'nota_maxima_esm',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_esm'               => 'integer',
        'status_id'            => 'integer',
        'nota_aprobatoria_esm' => 'float',
        'nota_minima_esm'      => 'float',
        'nota_maxima_esm'      => 'float',
    ];

    public function setNombreEsmAttribute($value): void
    {
        $this->attributes['nombre_esm'] = trim((string) $value);
    }

    public function notas(): HasMany
    {
        return $this->hasMany(EscalaNota::class, 'esm_id', 'id_esm');
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

    public static function obtenerPaginados(array|string|null $busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $statusId = static::resolverFiltroStatusId(is_array($busqueda) ? ($busqueda['status'] ?? $busqueda['status_id'] ?? null) : null);
        $fechaInicio = is_array($busqueda) ? ($busqueda['fecha_inicio'] ?? null) : null;
        $fechaFin = is_array($busqueda) ? ($busqueda['fecha_fin'] ?? null) : null;

        $query = DB::table('escala_medicion')
            ->select([
                'id_esm',
                'nombre_esm',
                'nota_aprobatoria_esm',
                'nota_minima_esm',
                'nota_maxima_esm',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->whereNull('deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_esm', 'ilike', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('nota_aprobatoria_esm', '=', (float) $search)
                        ->orWhere('nota_minima_esm', '=', (float) $search)
                        ->orWhere('nota_maxima_esm', '=', (float) $search);
                }
            });
        }

        if (! empty($fechaInicio)) {
            $fechaFinFinal = ! empty($fechaFin) ? $fechaFin : now()->toDateTimeString();
            $query->whereBetween('created_at', [$fechaInicio, $fechaFinFinal]);
        }

        $paginator = $query->orderBy('id_esm', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_esm = (int) $item->id_esm;
            $item->status_id = (int) $item->status_id;
            $item->nota_aprobatoria_esm = (float) $item->nota_aprobatoria_esm;
            $item->nota_minima_esm = $item->nota_minima_esm !== null ? (float) $item->nota_minima_esm : null;
            $item->nota_maxima_esm = $item->nota_maxima_esm !== null ? (float) $item->nota_maxima_esm : null;

            return $item;
        });

        return $paginator;
    }

    public static function registrarEscalaMedicion(array $datos, ?int $userId = null): object
    {
        $now = now();
        $insertData = [
            'nombre_esm'           => trim((string) $datos['nombre_esm']),
            'nota_aprobatoria_esm' => (float) $datos['nota_aprobatoria_esm'],
            'nota_minima_esm'      => isset($datos['nota_minima_esm']) && $datos['nota_minima_esm'] !== '' ? (float) $datos['nota_minima_esm'] : 0.00,
            'nota_maxima_esm'      => isset($datos['nota_maxima_esm']) && $datos['nota_maxima_esm'] !== '' ? (float) $datos['nota_maxima_esm'] : 20.00,
            'status_id'            => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at'           => $now,
            'updated_at'           => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('escala_medicion')->insertGetId($insertData, 'id_esm');

        return static::obtenerPorId($id);
    }

    public static function crearEscalaMedicion(array $datos, ?int $userId = null): object
    {
        return static::registrarEscalaMedicion($datos, $userId);
    }

    public static function actualizarEscalaMedicion(int $id_esm, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (array_key_exists('nombre_esm', $datos)) {
            $updateData['nombre_esm'] = trim((string) $datos['nombre_esm']);
        }
        if (array_key_exists('nota_aprobatoria_esm', $datos)) {
            $updateData['nota_aprobatoria_esm'] = (float) $datos['nota_aprobatoria_esm'];
        }
        if (array_key_exists('nota_minima_esm', $datos)) {
            $updateData['nota_minima_esm'] = $datos['nota_minima_esm'] !== null && $datos['nota_minima_esm'] !== '' ? (float) $datos['nota_minima_esm'] : null;
        }
        if (array_key_exists('nota_maxima_esm', $datos)) {
            $updateData['nota_maxima_esm'] = $datos['nota_maxima_esm'] !== null && $datos['nota_maxima_esm'] !== '' ? (float) $datos['nota_maxima_esm'] : null;
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

        DB::table('escala_medicion')
            ->where('id_esm', $id_esm)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_esm);
    }

    public static function obtenerPorId(int $id_esm): object
    {
        $escala = DB::table('escala_medicion')
            ->select([
                'id_esm',
                'nombre_esm',
                'nota_aprobatoria_esm',
                'nota_minima_esm',
                'nota_maxima_esm',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->where('id_esm', $id_esm)
            ->whereNull('deleted_at')
            ->first();

        if (! $escala) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id_esm]);
        }

        $escala->id_esm = (int) $escala->id_esm;
        $escala->status_id = (int) $escala->status_id;
        $escala->nota_aprobatoria_esm = (float) $escala->nota_aprobatoria_esm;
        $escala->nota_minima_esm = $escala->nota_minima_esm !== null ? (float) $escala->nota_minima_esm : null;
        $escala->nota_maxima_esm = $escala->nota_maxima_esm !== null ? (float) $escala->nota_maxima_esm : null;

        return $escala;
    }

    public static function obtenerParaEditar(int $id_esm): object
    {
        return static::obtenerPorId($id_esm);
    }

    public static function obtenerConRelaciones(int $id_esm): object
    {
        $escala = static::obtenerPorId($id_esm);

        $escala->creador = $escala->created_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $escala->created_id)->first()
            : null;

        $escala->actualizador = $escala->updated_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $escala->updated_id)->first()
            : null;

        return $escala;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('escala_medicion')
            ->select([
                'id_esm',
                'nombre_esm',
                'nota_aprobatoria_esm',
                'nota_minima_esm',
                'nota_maxima_esm',
            ])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_esm', 'asc')
            ->get();
    }

    public static function alternarEstatus(int $id_esm, ?int $estado = null, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esm, $estado, $userId) {
            $escala = static::obtenerPorId($id_esm);

            $nuevoEstadoId = $estado !== null
                ? (int) $estado
                : (static::esStatusActivo($escala) ? self::STATUS_INACTIVO : self::STATUS_ACTIVO);
            $activar = ($nuevoEstadoId === self::STATUS_ACTIVO);
            $usuarioId = $userId ?? auth()->id();

            DB::table('escala_medicion')
                ->where('id_esm', $id_esm)
                ->update([
                    'status_id'  => $nuevoEstadoId,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $escala->nombre_esm,
                'estatus'   => $activar,
                'status_id' => $nuevoEstadoId,
            ];
        });
    }

    /**
     * Elimina lógicamente la escala de medición (`deleted_at` + `status_id = 2`)
     * preservando el registro histórico en PostgreSQL.
     */
    public static function eliminarConSoftDelete(int $id_esm, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esm, $userId) {
            $escala = static::find($id_esm);
            if (! $escala) {
                throw (new ModelNotFoundException)->setModel(static::class, [$id_esm]);
            }

            $nombre = $escala->nombre_esm;

            static::ejecutarBajaLogicaModelo(
                $escala,
                $userId,
                self::STATUS_INACTIVO
            );

            return [
                'nombre'    => $nombre,
                'estatus'   => false,
                'status_id' => self::STATUS_INACTIVO,
            ];
        });
    }
}
