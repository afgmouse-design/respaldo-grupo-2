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
 * Modelo: Eje Curricular (tabla `eje_curricular`).
 *
 * Propósito: Gestiona los ejes curriculares formativos de los planes de estudio
 * (Eje Profesional, Eje de Proyecto, Eje Socio-Crítico, Eje Estético-Lúdico),
 * definiendo su clave primaria personalizada, atributos asignables, trazabilidad y métodos de consulta.
 *
 * Arquitectura SOGAC:
 *  - Nombre de tabla singular explícito ($table = 'eje_curricular').
 *  - Llave primaria personalizada ($primaryKey = 'id_ecu').
 *  - Auditoría automática con trait RegistraEnBitacora.
 *  - Métodos estáticos de consulta y actualización lógica mediante status_id.
 */
class EjeCurricular extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla (singular). */
    protected $table = 'eje_curricular';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_ecu';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'EjeCurricular';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    /** Atributos asignables masivamente. */
    protected $fillable = [
        'nombre_ecu',
        'codigo_ecu',
        'descripcion_ecu',
        'status',
        'status_id',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_ecu'      => 'integer',
        'status'      => 'boolean',
        'status_id'   => 'integer',
        'created_id'  => 'integer',
        'updated_id'  => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_ecu'];

    /**
     * Accesor para el estatus lógico de compatibilidad con interfaz.
     */
    public function getEstEcuAttribute(): bool
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
     * Obtiene los registros paginados aplicando OBLIGATORIAMENTE el filtro de registros activos (status_id = 1).
     *
     * @param string|array|null $busqueda Término de búsqueda por nombre o código, o array de filtros
     * @param int $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;

        $query = DB::table('eje_curricular')
            ->select([
                'id_ecu',
                'nombre_ecu',
                'codigo_ecu',
                'descripcion_ecu',
                'status',
                'status_id',
                'created_id',
                'updated_id',
                'created_at',
                'updated_at',
            ]);

        $status = $filtros['status'] ?? null;
        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status_id', filter_var($status, FILTER_VALIDATE_BOOLEAN) ? 1 : 2);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_ecu', 'ilike', "%{$search}%")
                  ->orWhere('codigo_ecu', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('id_ecu', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_ecu = (int) $item->id_ecu;
            $item->status = (bool) ($item->status_id === 1);
            $item->est_ecu = (bool) ($item->status_id === 1);
            return $item;
        });

        return $paginator;
    }

    /**
     * Actualiza el status_id del registro (1 = Activo, 2 = Inactivo) y sincroniza el campo status.
     *
     * @param int $id Identificador único del eje curricular
     * @param int $estado Código del nuevo estado
     * @param int|null $userId Usuario que efectúa la operación
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        $registro = DB::table('eje_curricular')->where('id_ecu', $id)->first();

        if (!$registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        $nuevoStatusBool = ($estado === 1);
        $usuarioId = $userId ?? auth()->id();

        DB::table('eje_curricular')
            ->where('id_ecu', $id)
            ->update([
                'status_id'  => $estado,
                'status'     => $nuevoStatusBool,
                'updated_id' => $usuarioId,
                'updated_at' => now(),
            ]);

        return true;
    }

    /**
     * Obtiene los ejes curriculares activos para selectores desplegables.
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('eje_curricular')
            ->select(['id_ecu', 'codigo_ecu', 'nombre_ecu'])
            ->where('status_id', 1)
            ->orderBy('nombre_ecu', 'asc')
            ->get();
    }
}

