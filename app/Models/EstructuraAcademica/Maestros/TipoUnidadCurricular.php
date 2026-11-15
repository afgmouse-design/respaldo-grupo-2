<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Tipo de Unidad Curricular (tabla `tipo_unidad_curricular`).
 *
 * Módulo: Estructura Académica / Currículo.
 * Propósito: Gestiona las clasificaciones o naturalezas de unidades curriculares (Teórica, Práctica, Proyecto, etc.).
 *
 * Actualización:
 *  - Ajuste estricto de la propiedad $fillable = ['nombre_tuc', 'status_id', 'status']
 *    para prevenir excepciones de asignación masiva (Mass Assignment Error).
 *  - Actualización 2026-10-22 (Orden DESC y soporte para mostrar registros inactivos):
 *    Se eliminó la restricción fija de solo status_id = 1 para permitir listar registros inactivos
 *    y se configuró la ordenación descendente (DESC) por id_tuc.
 */
class TipoUnidadCurricular extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla en singular. */
    protected $table = 'tipo_unidad_curricular';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_tuc';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'TipoUnidadCurricular';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'nombre_tuc',
        'status_id',
        'status',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_tuc'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_tuc'];

    /**
     * Accesor para el estatus lógico de compatibilidad con interfaz.
     */
    public function getEstTucAttribute(): bool
    {
        return (bool) ($this->status_id === 1);
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
     * Obtiene los registros paginados con orden DESC y soporte para activos e inactivos.
     *
     * @param string|array|null $busqueda Término de búsqueda por nombre, o array de filtros
     * @param int $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $status = is_array($busqueda) ? ($busqueda['status'] ?? null) : null;

        $query = DB::table('tipo_unidad_curricular')
            ->select([
                'id_tuc',
                'nombre_tuc',
                'status',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ]);

        if ($status !== null && $status !== '' && $status !== 'all') {
            $statusBool = filter_var($status, FILTER_VALIDATE_BOOLEAN);
            $query->where(function ($q) use ($statusBool) {
                $q->where('status', $statusBool)
                  ->orWhere('status_id', $statusBool ? 1 : 2);
            });
        }

        if (!empty($search)) {
            $query->where('nombre_tuc', 'ilike', "%{$search}%");
        }

        $paginator = $query->orderBy('id_tuc', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_tuc = (int) $item->id_tuc;
            $item->status = (bool) ($item->status ?? ($item->status_id === 1));
            $item->est_tuc = (bool) ($item->status ?? ($item->status_id === 1));
            return $item;
        });

        return $paginator;
    }

    /**
     * Actualiza el status_id del registro (1 = Activo, 2 = Inactivo) y sincroniza el campo booleano status.
     *
     * @param int $id Identificador único del tipo de unidad curricular
     * @param int $estado Código del nuevo estado (por defecto 2 = inactivo)
     * @param int|null $userId Usuario que efectúa la operación
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = DB::table('tipo_unidad_curricular')->where('id_tuc', $id)->first();

        if (!$registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        $nuevoStatusBool = ($estado === 1);
        $usuarioId = $userId ?? auth()->id();

        DB::table('tipo_unidad_curricular')
            ->where('id_tuc', $id)
            ->update([
                'status_id'  => $estado,
                'status'     => $nuevoStatusBool,
                'updated_id' => $usuarioId,
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Obtiene los tipos de unidad curricular activos para selectores desplegables.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('tipo_unidad_curricular')
            ->select(['id_tuc', 'nombre_tuc'])
            ->where('status_id', 1)
            ->orderBy('nombre_tuc', 'asc')
            ->get();
    }
}
