<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Unidad Curricular (tabla `unidad_curricular`).
 *
 * Módulo: Estructura Académica / Currículo.
 * Propósito: Gestiona el catálogo de asignaturas o unidades curriculares base de la institución,
 * asociadas a su respectivo Tipo de Unidad Curricular (TUC).
 *
 * Actualización 2026-10-22 (Orden DESC y soporte para mostrar registros inactivos):
 * Se eliminó la restricción fija de solo status_id = 1 para permitir listar registros inactivos
 * y se configuró la ordenación descendente (DESC) por id_unc.
 */
class UnidadCurricular extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla en singular. */
    protected $table = 'unidad_curricular';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_unc';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'UnidadCurricular';

    /** Desactivación de timestamps automáticos de Eloquent si se manejan explícitamente o nativos. */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'codigo_unc',
        'nombre_unc',
        'tuc_id',
        'semestre_unc',
        'tra_id',
        'unidades_credito_unc',
        'htea_unc',
        'htei_unc',
        'thte_unc',
        'status_id',
        'status',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_unc'               => 'integer',
        'tuc_id'               => 'integer',
        'semestre_unc'         => 'integer',
        'tra_id'               => 'integer',
        'unidades_credito_unc' => 'integer',
        'htea_unc'             => 'integer',
        'htei_unc'             => 'integer',
        'thte_unc'             => 'integer',
        'status'               => 'boolean',
        'status_id'            => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_unc'];

    /**
     * Accesor para el estatus lógico de compatibilidad.
     */
    public function getEstUncAttribute(): bool
    {
        return (bool) ($this->status_id === 1);
    }

    /**
     * Relación Eloquent: Tipo de Unidad Curricular al que pertenece.
     */
    public function tipoUnidadCurricular(): BelongsTo
    {
        return $this->belongsTo(TipoUnidadCurricular::class, 'tuc_id', 'id_tuc');
    }

    /**
     * Relación Eloquent: Trayecto formativo asignado a la materia.
     */
    public function trayecto(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class, 'tra_id', 'id_tra');
    }

    /**
     * Relación Eloquent: Mallas curriculares en las que se imparte esta unidad.
     */
    public function detallesMalla(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MallaCurricularDetalle::class, 'unc_id', 'id_unc');
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
     * @param string|array|null $busqueda Término de búsqueda o array de filtros
     * @param int $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $status = is_array($busqueda) ? ($busqueda['status'] ?? null) : null;

        $query = static::with(['tipoUnidadCurricular', 'trayecto']);

        if ($status !== null && $status !== '' && $status !== 'all') {
            $statusBool = filter_var($status, FILTER_VALIDATE_BOOLEAN);
            $query->where(function ($q) use ($statusBool) {
                $q->where('status', $statusBool)
                  ->orWhere('status_id', $statusBool ? 1 : 2);
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('codigo_unc', 'ilike', "%{$search}%")
                  ->orWhereHas('tipoUnidadCurricular', function ($qt) use ($search) {
                      $qt->where('nombre_tuc', 'ilike', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('id_unc', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * Actualiza el status_id del registro (1 = Activo, 2 = Inactivo) y sincroniza el campo booleano status.
     *
     * @param int $id Identificador único de la unidad curricular
     * @param int $estado Código del nuevo estado (por defecto 2 = inactivo)
     */
    public static function alternarEstatus(int $id, int $estado = 2): bool
    {
        $registro = static::where('id_unc', $id)->first();

        if (!$registro) {
            return false;
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'status'     => ($estado === 1),
            'updated_id' => auth()->id(),
        ]);
    }
}

