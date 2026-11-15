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
 * Modelo: Trayecto / Nivel Académico (tabla `trayecto`).
 *
 * Propósito: Define los trayectos, semestres o tramos académicos pertenecientes
 * a una modalidad o tipo de período (ej. Trayecto Inicial, Trayecto I, Trayecto II).
 *
 * Actualización 2026-10-22 (Orden DESC y soporte para mostrar registros inactivos):
 * Se eliminó el filtro rígido que ocultaba registros inactivos en obtenerPaginados() y
 * se configuró el ordenamiento descendente (DESC) por id_tra según estándar SOGAC.
 *
 * Reglas de Arquitectura Estricta:
 *  - Sin trait SoftDeletes (se descarta deleted_at de Eloquent y se maneja status booleano;
 *    consultas puras con ->whereNull('deleted_at')).
 *  - Desactivación de timestamps automáticos ($timestamps = false).
 *  - Estatus lógico booleano 'status' (true = Activo, false = Inactivo).
 *  - Query Builder puro con DB::table('trayecto') para consultas y persistencia.
 *  - Prohibido el uso de self::query(), create(), update(), save() o delete().
 */
class Trayecto extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'trayecto';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_tra';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'Trayecto';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'codigo_tra',
        'nombre_tra',
        'numero_orden_tra',
        'tpd_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos nativos. */
    protected $casts = [
        'id_tra'           => 'integer',
        'numero_orden_tra' => 'integer',
        'tpd_id'           => 'integer',
        'status'           => 'boolean',
        'status_id'        => 'integer',
    ];

    /** Atributos dinámicos anexados al objeto. */
    protected $appends = [
        'est_tra',
    ];

    /**
     * Accesor para compatibilidad con convención de estatus est_tra.
     */
    public function getEstTraAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutador: Limpia y normaliza el nombre del trayecto.
     */
    public function setNombreTraAttribute($value): void
    {
        $this->attributes['nombre_tra'] = trim((string) $value);
    }

    /**
     * Mutador: Limpia y formatea a mayúsculas el código referencial.
     */
    public function setCodigoTraAttribute($value): void
    {
        $this->attributes['codigo_tra'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    /**
     * Relación Eloquent: Tipo de Período / Modalidad académica padre.
     */
    public function tipoPeriodo(): BelongsTo
    {
        return $this->belongsTo(TipoPeriodo::class, 'tpd_id', 'id_tpd');
    }

    /**
     * Relación Eloquent: Usuario creador.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    /**
     * Relación Eloquent: Usuario actualizador.
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    /**
     * Scope: Solo registros activos.
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
     * Realiza leftJoin con tipo_periodo (tpd_id) para traer el nombre de la modalidad académica.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $tpdId = $filtros['tpd_id'] ?? null;
        $status = $filtros['status'] ?? null;

        $query = DB::table('trayecto')
            ->leftJoin('tipo_periodo', 'tipo_periodo.id_tpd', '=', 'trayecto.tpd_id')
            ->select([
                'trayecto.id_tra',
                'trayecto.codigo_tra',
                'trayecto.nombre_tra',
                'trayecto.numero_orden_tra',
                'trayecto.tpd_id',
                'trayecto.status',
                'trayecto.status_id',
                'trayecto.created_id',
                'trayecto.updated_id',
                'trayecto.created_at',
                'trayecto.updated_at',
                'tipo_periodo.nombre_tpd',
                'tipo_periodo.codigo_tpd as tipo_periodo_codigo',
                'tipo_periodo.status as tipo_periodo_status',
            ])
            ->whereNull('trayecto.deleted_at');

        // Búsqueda por texto (nombre, código o modalidad)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('trayecto.nombre_tra', 'ilike', "%{$search}%")
                  ->orWhere('trayecto.codigo_tra', 'ilike', "%{$search}%")
                  ->orWhere('tipo_periodo.nombre_tpd', 'ilike', "%{$search}%");
            });
        }

        // Filtro por modalidad académica padre
        if (!empty($tpdId) && $tpdId !== 'all') {
            $query->where('trayecto.tpd_id', (int) $tpdId);
        }

        // Filtro por estatus lógico
        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('trayecto.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        $paginator = $query->orderBy('trayecto.id_tra', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Mapeo e hidratación para compatibilidad de componentes Vue/Inertia
        $paginator->getCollection()->transform(function ($item) {
            $item->id_tra = (int) $item->id_tra;
            $item->numero_orden_tra = (int) $item->numero_orden_tra;
            $item->tpd_id = (int) $item->tpd_id;
            $item->status = (bool) $item->status;
            $item->est_tra = (bool) $item->status;

            $item->tipo_periodo = $item->tpd_id ? (object) [
                'id_tpd'     => (int) $item->tpd_id,
                'nombre_tpd' => $item->nombre_tpd,
                'codigo_tpd' => $item->tipo_periodo_codigo,
                'status'     => (bool) $item->tipo_periodo_status,
            ] : null;

            return $item;
        });

        return $paginator;
    }

    /**
     * 2. Registra un nuevo trayecto mediante Query Builder puro.
     */
    public static function registrarTrayecto(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_tra'       => trim((string) $datos['nombre_tra']),
            'codigo_tra'       => !empty($datos['codigo_tra']) ? mb_strtoupper(trim((string) $datos['codigo_tra']), 'UTF-8') : null,
            'numero_orden_tra' => isset($datos['numero_orden_tra']) ? (int) $datos['numero_orden_tra'] : 1,
            'tpd_id'           => (int) $datos['tpd_id'],
            'status'           => true,
            'status_id'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('trayecto')->insertGetId($insertData, 'id_tra');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarTrayecto para compatibilidad.
     */
    public static function crearTrayecto(array $datos, ?int $userId = null): object
    {
        return static::registrarTrayecto($datos, $userId);
    }

    /**
     * 3. Actualiza un trayecto existente mediante Query Builder puro.
     */
    public static function actualizarTrayecto(int $id_tra, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (array_key_exists('nombre_tra', $datos)) {
            $updateData['nombre_tra'] = trim((string) $datos['nombre_tra']);
        }
        if (array_key_exists('codigo_tra', $datos)) {
            $updateData['codigo_tra'] = !empty($datos['codigo_tra']) ? mb_strtoupper(trim((string) $datos['codigo_tra']), 'UTF-8') : null;
        }
        if (array_key_exists('numero_orden_tra', $datos)) {
            $updateData['numero_orden_tra'] = (int) $datos['numero_orden_tra'];
        }
        if (array_key_exists('tpd_id', $datos)) {
            $updateData['tpd_id'] = (int) $datos['tpd_id'];
        }
        if (array_key_exists('status', $datos)) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
            $updateData['status_id'] = $updateData['status'] ? 1 : 2;
        }

        $updateData['updated_at'] = now();

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('trayecto')
            ->where('id_tra', $id_tra)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_tra);
    }

    /**
     * 4. Busca un trayecto por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_tra): object
    {
        $trayecto = DB::table('trayecto')
            ->leftJoin('tipo_periodo', 'tipo_periodo.id_tpd', '=', 'trayecto.tpd_id')
            ->select([
                'trayecto.id_tra',
                'trayecto.codigo_tra',
                'trayecto.nombre_tra',
                'trayecto.numero_orden_tra',
                'trayecto.tpd_id',
                'trayecto.status',
                'trayecto.status_id',
                'trayecto.created_id',
                'trayecto.updated_id',
                'trayecto.created_at',
                'trayecto.updated_at',
                'tipo_periodo.nombre_tpd',
                'tipo_periodo.codigo_tpd as tipo_periodo_codigo',
                'tipo_periodo.status as tipo_periodo_status',
            ])
            ->where('trayecto.id_tra', $id_tra)
            ->whereNull('trayecto.deleted_at')
            ->first();

        if (!$trayecto) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id_tra]);
        }

        $trayecto->id_tra = (int) $trayecto->id_tra;
        $trayecto->numero_orden_tra = (int) $trayecto->numero_orden_tra;
        $trayecto->tpd_id = (int) $trayecto->tpd_id;
        $trayecto->status = (bool) $trayecto->status;
        $trayecto->est_tra = (bool) $trayecto->status;

        $trayecto->tipo_periodo = $trayecto->tpd_id ? (object) [
            'id_tpd'     => (int) $trayecto->tpd_id,
            'nombre_tpd' => $trayecto->nombre_tpd,
            'codigo_tpd' => $trayecto->tipo_periodo_codigo,
            'status'     => (bool) $trayecto->tipo_periodo_status,
        ] : null;

        return $trayecto;
    }

    /**
     * 5. Obtiene el registro para poblar el formulario de edición.
     */
    public static function obtenerParaEditar(int $id_tra): object
    {
        return static::obtenerPorId($id_tra);
    }

    /**
     * 6. Obtiene el trayecto con sus relaciones completas y trazabilidad de usuarios.
     */
    public static function obtenerConRelaciones(int $id_tra): object
    {
        $trayecto = static::obtenerPorId($id_tra);

        $trayecto->creador = $trayecto->created_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $trayecto->created_id)->first()
            : null;

        $trayecto->actualizador = $trayecto->updated_id
            ? DB::table('users')->select(['id', 'name', 'email'])->where('id', $trayecto->updated_id)->first()
            : null;

        return $trayecto;
    }

    /**
     * 7. Obtiene los trayectos activos para selectores dependientes mediante Query Builder puro.
     */
    public static function obtenerActivosParaSelect(?int $tpdId = null): Collection
    {
        $query = DB::table('trayecto')
            ->select([
                'id_tra',
                'nombre_tra',
                'codigo_tra',
                'numero_orden_tra',
                'tpd_id',
            ])
            ->where('status', true)
            ->whereNull('deleted_at');

        if ($tpdId) {
            $query->where('tpd_id', $tpdId);
        }

        return $query->orderBy('numero_orden_tra', 'asc')
            ->orderBy('nombre_tra', 'asc')
            ->get();
    }

    /**
     * 8. Alterna el estatus lógico del trayecto.
     * REGLA ESTRICTA: Si se intenta ACTIVAR, valida mediante JOIN que la modalidad padre
     * (tipo_periodo) se encuentre activa antes de permitir la activación.
     */
    public static function alternarEstatus(int $id_tra, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_tra, $userId) {
            $trayecto = DB::table('trayecto')
                ->leftJoin('tipo_periodo', 'tipo_periodo.id_tpd', '=', 'trayecto.tpd_id')
                ->select([
                    'trayecto.id_tra',
                    'trayecto.nombre_tra',
                    'trayecto.status',
                    'trayecto.tpd_id',
                    'tipo_periodo.nombre_tpd',
                    'tipo_periodo.status as tipo_periodo_status',
                ])
                ->where('trayecto.id_tra', $id_tra)
                ->whereNull('trayecto.deleted_at')
                ->first();

            if (!$trayecto) {
                throw (new ModelNotFoundException)->setModel(static::class, [$id_tra]);
            }

            $nuevoEstatus = !$trayecto->status;
            $usuarioId = $userId ?? auth()->id();

            // REGLA: Si se intenta ACTIVAR, verificar que la modalidad padre esté activa
            if ($nuevoEstatus) {
                if ($trayecto->tpd_id && !$trayecto->tipo_periodo_status) {
                    $nombrePadre = $trayecto->nombre_tpd ?? 'asociada';
                    throw new \DomainException(
                        "No se puede activar el trayecto '{$trayecto->nombre_tra}' porque su modalidad académica '{$nombrePadre}' está inactiva."
                    );
                }
            }

            DB::table('trayecto')
                ->where('id_tra', $id_tra)
                ->update([
                    'status'     => $nuevoEstatus,
                    'status_id'  => $nuevoEstatus ? 1 : 2,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            return [
                'nombre'  => $trayecto->nombre_tra,
                'estatus' => $nuevoEstatus,
            ];
        });
    }
}

