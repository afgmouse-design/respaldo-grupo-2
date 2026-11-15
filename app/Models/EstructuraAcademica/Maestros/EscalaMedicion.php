<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Escala de Medición (tabla `escala_medicion`).
 *
 * Propósito: Define los esquemas y rangos de evaluación y calificación académica
 * (ej. Escala 1 al 20, Cualitativa, Porcentual 1 al 100), con sus límites y nota aprobatoria.
 *
 * Reglas de Arquitectura Estricta:
 *  - Sin trait SoftDeletes (manejado mediante estatus lógico booleano 'status').
 *  - Desactivación de timestamps automáticos ($timestamps = false).
 *  - Columna de estado lógico booleano 'status' (true = Activo, false = Inactivo).
 *  - Query Builder puro con DB::table('escala_medicion') para consultas y persistencia.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class EscalaMedicion extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'escala_medicion';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_esm';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'EscalaMedicion';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_esm',
        'nota_aprobatoria_esm',
        'nota_minima_esm',
        'nota_maxima_esm',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_esm'               => 'integer',
        'status'               => 'boolean',
        'status_id'            => 'integer',
        'nota_aprobatoria_esm' => 'float',
        'nota_minima_esm'      => 'float',
        'nota_maxima_esm'      => 'float',
    ];

    /** Atributos dinámicos anexados al objeto. */
    protected $appends = [
        'est_esm',
    ];

    /**
     * Accesor para compatibilidad con convención de estatus est_esm.
     */
    public function getEstEsmAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Limpia y normaliza el nombre de la escala.
     */
    public function setNombreEsmAttribute($value): void
    {
        $this->attributes['nombre_esm'] = trim((string) $value);
    }

    /**
     * Relación Eloquent: Usuario creador del registro.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    /**
     * Relación Eloquent: Usuario actualizador del registro.
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    /**
     * Scope: Filtra únicamente registros activos.
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
     *
     * Actualización: Inclusión obligatoria del filtro where('status_id', 1) para listar únicamente registros activos.
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $status = is_array($busqueda) ? ($busqueda['status'] ?? null) : null;
        $fechaInicio = is_array($busqueda) ? ($busqueda['fecha_inicio'] ?? null) : null;
        $fechaFin = is_array($busqueda) ? ($busqueda['fecha_fin'] ?? null) : null;

        $query = DB::table('escala_medicion')
            ->select([
                'id_esm',
                'nombre_esm',
                'nota_aprobatoria_esm',
                'nota_minima_esm',
                'nota_maxima_esm',
                'status',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->where('status_id', 1);

        // Filtro de búsqueda por texto o valores de nota
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_esm', 'ilike', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('nota_aprobatoria_esm', '=', (float) $search)
                      ->orWhere('nota_minima_esm', '=', (float) $search)
                      ->orWhere('nota_maxima_esm', '=', (float) $search);
                }
            });
        }

        // Filtro por estatus lógico
        if ($status !== null && $status !== '' && $status !== 'all') {
            $statusBool = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($statusBool !== null) {
                $query->where('status', $statusBool);
            }
        }

        // Filtro por rango de fechas de creación
        if (!empty($fechaInicio)) {
            $fechaFinFinal = !empty($fechaFin) ? $fechaFin : now()->toDateTimeString();
            $query->whereBetween('created_at', [$fechaInicio, $fechaFinFinal]);
        }

        $paginator = $query->orderBy('id_esm', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        // Mapeo para reactividad de Inertia/Vue
        $paginator->getCollection()->transform(function ($item) {
            $item->id_esm = (int) $item->id_esm;
            $item->status = (bool) $item->status;
            $item->est_esm = (bool) $item->status;
            $item->nota_aprobatoria_esm = (float) $item->nota_aprobatoria_esm;
            $item->nota_minima_esm = $item->nota_minima_esm !== null ? (float) $item->nota_minima_esm : null;
            $item->nota_maxima_esm = $item->nota_maxima_esm !== null ? (float) $item->nota_maxima_esm : null;
            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra una nueva escala de medición mediante Query Builder puro.
     */
    public static function registrarEscalaMedicion(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_esm'           => trim((string) $datos['nombre_esm']),
            'nota_aprobatoria_esm' => (float) $datos['nota_aprobatoria_esm'],
            'nota_minima_esm'      => isset($datos['nota_minima_esm']) && $datos['nota_minima_esm'] !== '' ? (float) $datos['nota_minima_esm'] : 0.00,
            'nota_maxima_esm'      => isset($datos['nota_maxima_esm']) && $datos['nota_maxima_esm'] !== '' ? (float) $datos['nota_maxima_esm'] : 20.00,
            'status'               => true,
            'status_id'            => 1,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('escala_medicion')->insertGetId($insertData, 'id_esm');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarEscalaMedicion para compatibilidad.
     */
    public static function crearEscalaMedicion(array $datos, ?int $userId = null): object
    {
        return static::registrarEscalaMedicion($datos, $userId);
    }

    /**
     * 3. Actualiza una escala de medición existente mediante Query Builder puro.
     */
    public static function actualizarEscalaMedicion(int $id_esm, array $datos, ?int $userId = null): object
    {
        $updateData = [];

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
        if (array_key_exists('status', $datos)) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
            $updateData['status_id'] = $updateData['status'] ? 1 : 2;
        }

        $updateData['updated_at'] = now();

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('escala_medicion')
            ->where('id_esm', $id_esm)
            ->update($updateData);

        return static::obtenerPorId($id_esm);
    }

    /**
     * 4. Busca una escala de medición por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_esm): object
    {
        $escala = DB::table('escala_medicion')
            ->select([
                'id_esm',
                'nombre_esm',
                'nota_aprobatoria_esm',
                'nota_minima_esm',
                'nota_maxima_esm',
                'status',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ])
            ->where('id_esm', $id_esm)
            ->first();

        if (!$escala) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id_esm]);
        }

        $escala->id_esm = (int) $escala->id_esm;
        $escala->status = (bool) $escala->status;
        $escala->est_esm = (bool) $escala->status;
        $escala->nota_aprobatoria_esm = (float) $escala->nota_aprobatoria_esm;
        $escala->nota_minima_esm = $escala->nota_minima_esm !== null ? (float) $escala->nota_minima_esm : null;
        $escala->nota_maxima_esm = $escala->nota_maxima_esm !== null ? (float) $escala->nota_maxima_esm : null;

        return $escala;
    }

    /**
     * 5. Obtiene el registro para poblar el formulario de edición.
     */
    public static function obtenerParaEditar(int $id_esm): object
    {
        return static::obtenerPorId($id_esm);
    }

    /**
     * 6. Obtiene el registro con datos de auditoría (creador y actualizador).
     */
    public static function obtenerConRelaciones(int $id_esm): object
    {
        $escala = static::obtenerPorId($id_esm);

        $escala->creador = $escala->created_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $escala->created_id)->first()
            : null;

        $escala->actualizador = $escala->updated_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $escala->updated_id)->first()
            : null;

        return $escala;
    }

    /**
     * 7. Obtiene escalas activas para poblar selectores desplegables.
     */
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
            ->where('status', true)
            ->orderBy('nombre_esm', 'asc')
            ->get();
    }

    /**
     * 8. Alterna o asigna el estatus lógico de la escala de medición mediante status_id.
     *
     * Actualización: Soporte para asignar directamente el valor de status_id ($estado = 1 activo, 2 inactivo).
     *
     * @param int $id_esm Identificador de la escala
     * @param int|null $estado Valor específico de status_id (null para invertir el valor actual)
     * @param int|null $userId Usuario autenticado que realiza el cambio
     */
    public static function alternarEstatus(int $id_esm, ?int $estado = null, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_esm, $estado, $userId) {
            $escala = static::obtenerPorId($id_esm);

            if ($estado !== null) {
                $nuevoEstadoId = (int) $estado;
                $nuevoEstatus = ($nuevoEstadoId === 1);
            } else {
                $nuevoEstatus = !$escala->status;
                $nuevoEstadoId = $nuevoEstatus ? 1 : 2;
            }

            $usuarioId = $userId ?? auth()->id();

            DB::table('escala_medicion')
                ->where('id_esm', $id_esm)
                ->update([
                    'status'     => $nuevoEstatus,
                    'status_id'  => $nuevoEstadoId,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'    => $escala->nombre_esm,
                'estatus'   => $nuevoEstatus,
                'status_id' => $nuevoEstadoId,
            ];
        });
    }
}

