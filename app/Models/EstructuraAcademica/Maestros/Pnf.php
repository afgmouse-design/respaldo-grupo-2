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
 * Modelo: Programa Nacional de Formación (tabla `pnf`).
 *
 * Módulo: Estructura Académica / Oferta Académica.
 * Propósito: Gestiona los Programas Nacionales de Formación (carreras) ofertados por la institución.
 *
 * Actualización:
 *  - Ajuste estricto de la propiedad $fillable = ['codigo_pnf', 'nombre_pnf', 'status_id', 'status']
 *    para prevenir excepciones de asignación masiva (Mass Assignment Error).
 *  - Actualización 2026-10-22 (Orden DESC y soporte para mostrar registros inactivos):
 *    Se eliminó la restricción fija de solo status_id = 1 para permitir listar registros inactivos
 *    y se configuró la ordenación descendente (DESC) por id_pnf.
 *  - Actualización 2026-10-22 (Desactivación en cascada de Malla Curricular):
 *    Al desactivar un PNF, se desactivan automáticamente en cascada todas las mallas curriculares
 *    vinculadas bajo una transacción atómica.
 */
class Pnf extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla en singular. */
    protected $table = 'pnf';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_pnf';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'Pnf';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'codigo_pnf',
        'nombre_pnf',
        'status_id',
        'status',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_pnf'    => 'integer',
        'status'    => 'boolean',
        'status_id' => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_pnf'];

    /**
     * Accesor para el estatus lógico de compatibilidad con interfaz.
     */
    public function getEstPnfAttribute(): bool
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
     * Relación Eloquent: Mallas curriculares vinculadas a este PNF.
     */
    public function mallas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'pnf_id', 'id_pnf');
    }

    /**
     * Obtiene los registros paginados con orden DESC y soporte para activos e inactivos.
     *
     * @param string|array|null $busqueda Término de búsqueda por código o nombre, o array de filtros
     * @param int $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $status = is_array($busqueda) ? ($busqueda['status'] ?? null) : null;

        $query = DB::table('pnf')
            ->select([
                'id_pnf',
                'codigo_pnf',
                'nombre_pnf',
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
            $query->where(function ($q) use ($search) {
                $q->where('nombre_pnf', 'ilike', "%{$search}%")
                  ->orWhere('codigo_pnf', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('id_pnf', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_pnf = (int) $item->id_pnf;
            $item->status = (bool) ($item->status ?? ($item->status_id === 1));
            $item->est_pnf = (bool) ($item->status ?? ($item->status_id === 1));
            return $item;
        });

        return $paginator;
    }

    /**
     * Actualiza el status_id del registro (1 = Activo, 2 = Inactivo) y sincroniza el campo booleano status.
     *
     * @param int $id Identificador único del PNF
     * @param int $estado Código del nuevo estado (por defecto 2 = inactivo)
     * @param int|null $userId Usuario que efectúa la operación
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = DB::table('pnf')->where('id_pnf', $id)->first();

        if (!$registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        $nuevoStatusBool = ($estado === 1);
        $usuarioId = $userId ?? auth()->id();

        DB::transaction(function () use ($id, $estado, $nuevoStatusBool, $usuarioId) {
            DB::table('pnf')
                ->where('id_pnf', $id)
                ->update([
                    'status_id'  => $estado,
                    'status'     => $nuevoStatusBool,
                    'updated_id' => $usuarioId,
                    'updated_at' => now(),
                ]);

            // Si se desactiva el programa (PNF), se desactivan en cascada sus mallas curriculares
            if ($estado === 2 || !$nuevoStatusBool) {
                DB::table('malla_curricular')
                    ->where('pnf_id', $id)
                    ->update([
                        'status_id'  => 2,
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);
            }
        });

        return true;
    }

    /**
     * Obtiene los PNF activos para selectores desplegables.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('pnf')
            ->select(['id_pnf', 'codigo_pnf', 'nombre_pnf'])
            ->where('status_id', 1)
            ->orderBy('nombre_pnf', 'asc')
            ->get();
    }
}
