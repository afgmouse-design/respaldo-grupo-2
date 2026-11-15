<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Malla Curricular (tabla `malla_curricular`).
 *
 * Módulo: Estructura Académica / Currículo.
 * Propósito: Gestiona los diseños curriculares y planes de estudio cabecera asociados a cada PNF.
 *
 * Actualización 2026-11-08:
 * Se añadió `prg_id` a `$fillable` y `$casts` para mantener sincronizada la llave foránea del programa
 * junto con `pnf_id` y el número de cohorte (`codigo_gaceta_mac`).
 */
class MallaCurricular extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla en singular. */
    protected $table = 'malla_curricular';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_mac';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'MallaCurricular';

    /** Desactivación de timestamps automáticos de Eloquent si se manejan explícitamente o nativos. */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'nombre_mac',
        'codigo_gaceta_mac',
        'fecha_aprobacion_mac',
        'observacion_mac',
        'pnf_id',
        'prg_id',
        'version_pensum_mac',
        'creditos_minimos_tsu_mac',
        'horas_minimas_tsu_mac',
        'materias_obligatorias_tsu_mac',
        'creditos_minimos_ing_mac',
        'horas_minimas_ing_mac',
        'materias_obligatorias_ing_mac',
        'creditos_minimos_prosecucion_mac',
        'status_id',
        'status',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_mac'                           => 'integer',
        'pnf_id'                           => 'integer',
        'fecha_aprobacion_mac'             => 'date',
        'creditos_minimos_tsu_mac'         => 'integer',
        'horas_minimas_tsu_mac'            => 'integer',
        'materias_obligatorias_tsu_mac'    => 'integer',
        'creditos_minimos_ing_mac'         => 'integer',
        'horas_minimas_ing_mac'            => 'integer',
        'materias_obligatorias_ing_mac'    => 'integer',
        'creditos_minimos_prosecucion_mac' => 'integer',
        'status'                           => 'boolean',
        'status_id'                        => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_mac'];

    /**
     * Accesor para el estatus lógico de compatibilidad.
     */
    public function getEstMacAttribute(): bool
    {
        return (bool) ($this->status_id === 1);
    }

    /**
     * Relación Eloquent: Programa Nacional de Formación (PNF) al que pertenece.
     */
    public function pnf(): BelongsTo
    {
        return $this->belongsTo(Pnf::class, 'pnf_id', 'id_pnf');
    }

    /**
     * Relación Eloquent: Materias / Unidades curriculares asignadas en el detalle de la malla.
     */
    public function detalles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MallaCurricularDetalle::class, 'mac_id', 'id_mac');
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
     * Obtiene los registros paginados aplicando OBLIGATORIAMENTE el filtro de registros activos (status_id = 1)
     * e incluyendo la relación con PNF.
     *
     * @param string|array|null $busqueda Término de búsqueda o array de filtros
     * @param int $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $status = is_array($busqueda) ? ($busqueda['status'] ?? null) : null;

        $query = static::with('pnf');

        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status_id', filter_var($status, FILTER_VALIDATE_BOOLEAN) ? 1 : 2);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_mac', 'ilike', "%{$search}%")
                  ->orWhere('codigo_gaceta_mac', 'ilike', "%{$search}%")
                  ->orWhereHas('pnf', function ($qp) use ($search) {
                      $qp->where('nombre_pnf', 'ilike', "%{$search}%")
                         ->orWhere('codigo_pnf', 'ilike', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('id_mac', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * Actualiza el status_id del registro (1 = Activo, 2 = Inactivo) y sincroniza el campo booleano status.
     *
     * @param int $id Identificador único de la malla curricular
     * @param int $estado Código del nuevo estado (por defecto 2 = inactivo)
     */
    public static function alternarEstatus(int $id, int $estado = 2): bool
    {
        $registro = static::where('id_mac', $id)->first();

        if (!$registro) {
            return false;
        }

        // Si se intenta activar, verificar que el PNF padre no esté inactivo
        if ($estado === 1) {
            $pnf = Pnf::where('id_pnf', $registro->pnf_id)->first();
            if ($pnf && ($pnf->status_id === 2 || $pnf->status === false)) {
                throw new \DomainException("No se puede activar esta malla curricular porque su programa (PNF '{$pnf->nombre_pnf}') se encuentra inactivo.");
            }
        }

        return (bool) $registro->update([
            'status_id'  => $estado,
            'status'     => ($estado === 1),
            'updated_id' => auth()->id(),
        ]);
    }
}

