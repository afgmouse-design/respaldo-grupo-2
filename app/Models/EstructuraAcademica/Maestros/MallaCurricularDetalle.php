<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo: Malla Curricular Detalle (tabla `malla_curricular_detalle`).
 *
 * Módulo: Estructura Académica / Currículo.
 * Propósito: Gestiona los renglones/asignaturas asignadas a un diseño de plan de estudios (malla),
 * vinculando Unidad Curricular, Trayecto, Eje Curricular y Escala de Medición con sus cargas de horas y UC.
 *
 * Actualización 2026-11-08:
 * Se agregaron `semestre_mcd`, `etapa_curricular_mcd`, `nota_minima_aprobatoria_mcd` y `tuc_id` a `$fillable`
 * y `$casts` para soportar el constructor dinámico de Malla Curricular por Trayectos y Semestres/Períodos.
 */
class MallaCurricularDetalle extends Model
{
    use RegistraEnBitacora;

    /** Nombre explícito de la tabla en singular. */
    protected $table = 'malla_curricular_detalle';

    /** Llave primaria personalizada. */
    protected $primaryKey = 'id_mcd';

    /** Módulo para auditoría en bitácora. */
    protected $moduloAuditoria = 'MallaCurricularDetalle';

    /** Desactivación de timestamps automáticos de Eloquent si se manejan nativamente. */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'unidades_credito_mcd',
        'htea_mcd',
        'htei_mcd',
        'thte_mcd',
        'duracion_semanas_mcd',
        'nota_minima_aprobatoria_mcd',
        'semestre_mcd',
        'etapa_curricular_mcd',
        'mac_id',
        'unc_id',
        'tra_id',
        'ecu_id',
        'esm_id',
        'tuc_id',
        'status_id',
        'status',
        'created_id',
        'updated_id',
    ];

    /** Conversión de tipos de datos nativos. */
    protected $casts = [
        'id_mcd'                      => 'integer',
        'unidades_credito_mcd'        => 'integer',
        'htea_mcd'                    => 'integer',
        'htei_mcd'                    => 'integer',
        'thte_mcd'                    => 'integer',
        'duracion_semanas_mcd'        => 'integer',
        'nota_minima_aprobatoria_mcd' => 'float',
        'semestre_mcd'                => 'integer',
        'mac_id'                      => 'integer',
        'unc_id'                      => 'integer',
        'tra_id'                      => 'integer',
        'ecu_id'                      => 'integer',
        'esm_id'                      => 'integer',
        'tuc_id'                      => 'integer',
        'status'                      => 'boolean',
        'status_id'                   => 'integer',
    ];

    /** Atributos virtuales calculados. */
    protected $appends = ['est_mcd'];

    /**
     * Accesor para el estatus de compatibilidad lógica.
     */
    public function getEstMcdAttribute(): string
    {
        return (int) $this->status_id === 1 ? 'Activo' : 'Inactivo';
    }

    // ==========================================
    // RELACIONES ELOQUENT (BELONGSTO)
    // ==========================================

    /**
     * Relación con la Malla Curricular cabecera.
     */
    public function mallaCurricular(): BelongsTo
    {
        return $this->belongsTo(MallaCurricular::class, 'mac_id', 'id_mac');
    }

    /**
     * Relación con la Unidad Curricular universal.
     */
    public function unidadCurricular(): BelongsTo
    {
        return $this->belongsTo(UnidadCurricular::class, 'unc_id', 'id_unc');
    }

    /**
     * Relación con el Trayecto o nivel formativo.
     */
    public function trayecto(): BelongsTo
    {
        return $this->belongsTo(Trayecto::class, 'tra_id', 'id_tra');
    }

    /**
     * Relación con el Eje Curricular formativo.
     */
    public function ejeCurricular(): BelongsTo
    {
        return $this->belongsTo(EjeCurricular::class, 'ecu_id', 'id_ecu');
    }

    /**
     * Relación con la Escala de Medición evaluativa.
     */
    public function escalaMedicion(): BelongsTo
    {
        return $this->belongsTo(EscalaMedicion::class, 'esm_id', 'id_esm');
    }

    /**
     * Relación con las prelaciones o requisitos que esta asignatura exige.
     */
    public function prelaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnidadCurricularPrelacion::class, 'mcd_id', 'id_mcd');
    }

    /**
     * Relación con las asignaturas de las que esta materia es requisito.
     */
    public function esRequisitoDe(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnidadCurricularPrelacion::class, 'mcd_prelacion_id', 'id_mcd');
    }

    /**
     * Usuario que creó el registro.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id', 'id');
    }

    /**
     * Usuario que actualizó el registro por última vez.
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id', 'id');
    }

    // ==========================================
    // MÉTODOS ESTÁNDAR SOGAC
    // ==========================================

    /**
     * Obtiene registros paginados con filtro estricto de estatus activo (status_id = 1).
     *
     * @param  array  $filtros  Criterios de búsqueda
     * @param  int    $porPagina Cantidad de registros por página
     */
    public static function obtenerPaginados(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        $query = self::query()
            ->with([
                'mallaCurricular',
                'unidadCurricular',
                'trayecto',
                'ejeCurricular',
                'escalaMedicion',
            ])
            ->where('status_id', 1);

        // Búsqueda por texto en relaciones o campos numéricos
        if (!empty($filtros['search'])) {
            $busqueda = trim($filtros['search']);
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('unidadCurricular', function ($qunc) use ($busqueda) {
                    $qunc->where('nombre_unc', 'ilike', "%{$busqueda}%")
                        ->orWhere('codigo_unc', 'ilike', "%{$busqueda}%");
                })
                ->orWhereHas('mallaCurricular', function ($qmac) use ($busqueda) {
                    $qmac->where('nombre_mac', 'ilike', "%{$busqueda}%");
                })
                ->orWhereHas('trayecto', function ($qtra) use ($busqueda) {
                    $qtra->where('nombre_tra', 'ilike', "%{$busqueda}%");
                });
            });
        }

        // Filtro específico por Malla Curricular si se provee
        if (!empty($filtros['mac_id'])) {
            $query->where('mac_id', (int) $filtros['mac_id']);
        }

        // Filtro específico por Trayecto si se provee
        if (!empty($filtros['tra_id'])) {
            $query->where('tra_id', (int) $filtros['tra_id']);
        }

        return $query->orderBy('id_mcd', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * Realiza la baja lógica o reactivación del registro alternando su estado.
     *
     * @param  int  $id      Identificador primario (id_mcd)
     * @param  int  $estado  Nuevo estado lógico (1 = Activo, 2 = Inactivo)
     */
    public static function alternarEstatus(int $id, int $estado): bool
    {
        $registro = self::find($id);

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

