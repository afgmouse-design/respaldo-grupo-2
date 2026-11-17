<?php

namespace App\Models\Personas;

use App\Models\Academico\LapsoAcademico;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\OrganizacionInstitucional\Maestros\Sede;
use App\Models\ProgramacionAcademica\Maestros\CohorteSede;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Persona (tabla `persona`).
 *
 * Propósito: Entidad maestra de identidad institucional en SOGAC (`id_per`). Centraliza los
 * datos personales y atributos específicos de estudiantes, docentes, administrativos y autoridades.
 * Su rol operativo se asigna mediante `persona_perfil` (`id_pep`).
 *
 * Historial:
 *  - 2026-11-14: Creación alineada estrictamente a la tabla `persona` de `sogac_mio.sql`.
 *  - 2026-11-16: Añadidos casts enteros de `doc_*` y relaciones `dedicacionDocente()`,
 *    `categoriaDocente()` y `condicionLaboral()` para control de carga horaria docente.
 */
class Persona extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'persona';

    protected $primaryKey = 'id_per';

    protected $moduloAuditoria = 'Persona';

    protected $fillable = [
        'use_id',
        'etn_id',
        'dis_id',
        'cor_id',
        'nacimiento_loc_id',
        'residencia_loc_id',
        'cedula_per',
        'nacionalidad_per',
        'documento_per',
        'nombres_per',
        'apellidos_per',
        'genero_per',
        'estado_civil_per',
        'fecha_nacimiento_per',
        'correo_per',
        'correo_secundario',
        'direccion_per',
        'lugar_nacimiento_per',
        'telefono_movil_per',
        'telefono_fijo_per',
        'telefono_trabajo_per',
        'antecedentes_penales_per',
        'foto_url_per',
        'status_id',
        'created_id',
        'updated_id',
        'est_sed_id',
        'est_cos_ingreso_id',
        'est_laa_ingreso_id',
        'est_laa_egreso_id',
        'est_fecha_ingreso',
        'est_sni',
        'est_ano_egreso_bachiller',
        'est_dependencia_bachiller',
        'est_privado_libertad',
        'est_ira_historico',
        'est_semestre',
        'est_solvencia_adm',
        'est_solvencia_aca',
        'est_solvencia_bib',
        'doc_dep_id',
        'doc_pfn_id',
        'doc_cad_id',
        'doc_ded_id',
        'doc_cla_id',
        'doc_especialidad',
        'doc_fecha_ingreso',
        'adm_dep_id',
        'adm_car_id',
        'adm_cla_id',
        'adm_fecha_ingreso',
        'aut_tau_id',
        'aut_gaceta',
        'aut_fecha_gaceta',
        'aut_resolucion',
        'aut_fecha_inicio',
        'aut_fecha_fin',
    ];

    protected $casts = [
        'id_per'                   => 'integer',
        'use_id'                   => 'integer',
        'fecha_nacimiento_per'     => 'date',
        'est_fecha_ingreso'        => 'date',
        'doc_fecha_ingreso'        => 'date',
        'adm_fecha_ingreso'        => 'date',
        'aut_fecha_gaceta'         => 'date',
        'aut_fecha_inicio'         => 'date',
        'aut_fecha_fin'            => 'date',
        'est_ano_egreso_bachiller' => 'integer',
        'est_privado_libertad'     => 'boolean',
        'est_ira_historico'        => 'decimal:2',
        'est_solvencia_adm'        => 'boolean',
        'est_solvencia_aca'        => 'boolean',
        'est_solvencia_bib'        => 'boolean',
        'doc_dep_id'               => 'integer',
        'doc_pfn_id'               => 'integer',
        'doc_cad_id'               => 'integer',
        'doc_ded_id'               => 'integer',
        'doc_cla_id'               => 'integer',
        'status_id'                => 'integer',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    /**
     * Nombre completo calculado a partir de nombres y apellidos.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres_per} {$this->apellidos_per}");
    }

    /**
     * Cuenta de usuario vinculada (`users.id`).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'use_id', 'id');
    }

    /**
     * Asignaciones de perfil (`persona_perfil`) asociadas a esta persona.
     */
    public function personaPerfiles(): HasMany
    {
        return $this->hasMany(PersonaPerfil::class, 'per_id', 'id_per');
    }

    /**
     * Perfiles asociados a la persona.
     */
    public function perfiles(): BelongsToMany
    {
        return $this->belongsToMany(Perfil::class, 'persona_perfil', 'per_id', 'prf_id', 'id_per', 'id_prf')
            ->withPivot(['id_pep', 'fecha_inicio_pep', 'fecha_fin_pep', 'status_id']);
    }

    public function localidadNacimiento(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'nacimiento_loc_id', 'id_loc');
    }

    public function localidadResidencia(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'residencia_loc_id', 'id_loc');
    }

    public function sedeEstudiante(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'est_sed_id', 'id_sed');
    }

    public function cohorteIngreso(): BelongsTo
    {
        return $this->belongsTo(CohorteSede::class, 'est_cos_ingreso_id', 'id_cos');
    }

    public function lapsoIngreso(): BelongsTo
    {
        return $this->belongsTo(LapsoAcademico::class, 'est_laa_ingreso_id', 'id_laa');
    }

    /**
     * Dedicación laboral docente (`persona.doc_ded_id -> dedicacion_docente.id_ded`).
     */
    public function dedicacionDocente(): BelongsTo
    {
        return $this->belongsTo(DedicacionDocente::class, 'doc_ded_id', 'id_ded');
    }

    /**
     * Categoría / escalafón docente (`persona.doc_cad_id -> categoria_docente.id_cad`).
     */
    public function categoriaDocente(): BelongsTo
    {
        return $this->belongsTo(CategoriaDocente::class, 'doc_cad_id', 'id_cad');
    }

    /**
     * Condición laboral docente (`persona.doc_cla_id -> condicion_laboral.id_cla`).
     */
    public function condicionLaboral(): BelongsTo
    {
        return $this->belongsTo(CondicionLaboral::class, 'doc_cla_id', 'id_cla');
    }

    /**
     * Scope para personas activas (`status_id = 1`).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
