<?php

namespace App\Models\Personas;

use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo: Categoría Docente (tabla `categoria_docente`).
 *
 * Propósito: Representa el escalafón académico universitario del personal docente.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial alineada a `categoria_docente` en PostgreSQL (`sogac_mio.sql`).
 */
class CategoriaDocente extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;

    protected $table = 'categoria_docente';

    protected $primaryKey = 'id_cad';

    protected $moduloAuditoria = 'CategoriaDocente';

    protected $fillable = [
        'nombre_cad',
        'codigo_cad',
        'nivel_escalafon_cad',
        'anos_experiencia_cad',
        'credenciales_requeridas_cad',
        'descripcion_cad',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_cad'               => 'integer',
        'nivel_escalafon_cad'  => 'integer',
        'anos_experiencia_cad' => 'integer',
        'status_id'            => 'integer',
    ];

    public function docentes(): HasMany
    {
        return $this->hasMany(Persona::class, 'doc_cad_id', 'id_cad');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }
}
