<?php

namespace App\Models\EstructuraAcademica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\OrganizacionInstitucional\Maestros\Universidad;
use App\Models\ProgramacionAcademica\Maestros\Seccion;
use App\Models\ProgramacionAcademica\Maestros\SedePrograma;
use App\Models\ProgramacionAcademica\Maestros\Turno;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Programa de Formación (tabla `programa`).
 *
 * Propósito: Modelo maestro oficial de `sogac_mio.sql` que reemplaza al legacy `Pnf` (`pnf`).
 * Representa las carreras / PNF / PNFA ofertados por la institución.
 *
 * Historial:
 *  - 2026-11-14: Creación reemplazando `Pnf` sobre `programa(id_prg)`, incluyendo las extensiones
 *    aditivas aprobadas (`codigo_prg`, `titulo_prg`, `tpg_id`, `ree_id`, `moe_id`), `status_id` y `SoftDeletes`.
 *  - 2026-11-16: Añadido `eliminarConSoftDelete` para baja lógica en interfaz (`deleted_at` + `status_id = 2`)
 *    con cascada hacia `malla_curricular` y `malla_curricular_detalle`.
 */
class Programa extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'programa';
    protected $primaryKey = 'id_prg';
    protected $moduloAuditoria = 'Programa';

    protected $fillable = [
        'uni_id',
        'tur_id',
        'esm_id',
        'tpg_id',
        'ree_id',
        'moe_id',
        'codigo_prg',
        'codigo_ministerio_prg',
        'nombre_prg',
        'siglas_prg',
        'titulo_prg',
        'intermedia_prg',
        'gaceta_prg',
        'resolucion_prg',
        'fecha_gaceta_prg',
        'fecha_resolucion_prg',
        'comentario_prg',
        'arancel_prg',
        'tipo_prg',
        'nombre_carnet_prg',
        'color_fondo_prg',
        'color_texto_prg',
        'pro_codigo',
        'pro_nombre',
        'pro_siglas',
        'pro_estatus',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_prg'               => 'integer',
        'uni_id'               => 'integer',
        'tur_id'               => 'integer',
        'esm_id'               => 'integer',
        'tpg_id'               => 'integer',
        'ree_id'               => 'integer',
        'moe_id'               => 'integer',
        'pro_codigo'           => 'integer',
        'arancel_prg'          => 'decimal:2',
        'fecha_gaceta_prg'     => 'date',
        'fecha_resolucion_prg' => 'date',
        'status_id'            => 'integer',
    ];

    public function universidad(): BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'uni_id', 'id_uni');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'tur_id', 'id_tur');
    }

    public function escalaMedicion(): BelongsTo
    {
        return $this->belongsTo(EscalaMedicion::class, 'esm_id', 'id_esm');
    }

    public function tipoPrograma(): BelongsTo
    {
        return $this->belongsTo(TipoPrograma::class, 'tpg_id', 'id_tpg');
    }

    public function regimenEstudio(): BelongsTo
    {
        return $this->belongsTo(RegimenEstudio::class, 'ree_id', 'id_ree');
    }

    public function modalidadEstudio(): BelongsTo
    {
        return $this->belongsTo(ModalidadEstudio::class, 'moe_id', 'id_moe');
    }

    public function mallas(): HasMany
    {
        return $this->hasMany(MallaCurricular::class, 'prg_id', 'id_prg');
    }

    public function sedesPrograma(): HasMany
    {
        return $this->hasMany(SedePrograma::class, 'prg_id', 'id_prg');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'prg_id', 'id_prg');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    public static function obtenerPaginados(array|string|null $busqueda = null, int $porPagina = 10): LengthAwarePaginator
    {
        $search = is_array($busqueda) ? ($busqueda['search'] ?? null) : $busqueda;
        $statusId = static::resolverFiltroStatusId(is_array($busqueda) ? ($busqueda['status'] ?? $busqueda['status_id'] ?? null) : null);

        $query = DB::table('programa')
            ->leftJoin('tipo_programa', 'tipo_programa.id_tpg', '=', 'programa.tpg_id')
            ->leftJoin('regimen_estudio', 'regimen_estudio.id_ree', '=', 'programa.ree_id')
            ->leftJoin('modalidad_estudio', 'modalidad_estudio.id_moe', '=', 'programa.moe_id')
            ->leftJoin('escala_medicion', 'escala_medicion.id_esm', '=', 'programa.esm_id')
            ->select([
                'programa.*',
                'tipo_programa.nombre_tpg',
                'regimen_estudio.nombre_ree',
                'modalidad_estudio.nombre_moe',
                'escala_medicion.nombre_esm',
            ])
            ->whereNull('programa.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('programa.status_id', $statusId));

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('programa.nombre_prg', 'ilike', "%{$search}%")
                    ->orWhere('programa.codigo_prg', 'ilike', "%{$search}%")
                    ->orWhere('programa.codigo_ministerio_prg', 'ilike', "%{$search}%")
                    ->orWhere('programa.siglas_prg', 'ilike', "%{$search}%")
                    ->orWhere('programa.titulo_prg', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('programa.id_prg', 'desc')
            ->paginate($porPagina)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->id_prg = (int) $item->id_prg;
            $item->status_id = (int) $item->status_id;

            return $item;
        });

        return $paginator;
    }

    public static function alternarEstatus(int $id, int $estado = self::STATUS_INACTIVO, ?int $userId = null): bool
    {
        $registro = DB::table('programa')->where('id_prg', $id)->whereNull('deleted_at')->first();
        if (! $registro) {
            throw (new ModelNotFoundException)->setModel(static::class, [$id]);
        }

        $usuarioId = $userId ?? auth()->id();
        $now = now();

        DB::transaction(function () use ($id, $estado, $usuarioId, $now) {
            DB::table('programa')
                ->where('id_prg', $id)
                ->update([
                    'status_id'  => $estado,
                    'updated_id' => $usuarioId,
                    'updated_at' => $now,
                ]);

            if ($estado === self::STATUS_INACTIVO) {
                DB::table('malla_curricular')
                    ->where('prg_id', $id)
                    ->update([
                        'status_id'  => self::STATUS_INACTIVO,
                        'updated_id' => $usuarioId,
                        'updated_at' => $now,
                    ]);
            }
        });

        return true;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('programa')
            ->select(['id_prg', 'codigo_prg', 'codigo_ministerio_prg', 'nombre_prg', 'siglas_prg'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_prg', 'asc')
            ->get();
    }

    /**
     * Elimina lógicamente el programa de la interfaz (`deleted_at` + `status_id = 2`),
     * propagando la baja a sus mallas curriculares y detalles asociados.
     */
    public static function eliminarConSoftDelete(int $id, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id, $userId) {
            $programa = static::find($id);
            if (! $programa) {
                throw (new ModelNotFoundException)->setModel(static::class, [$id]);
            }

            $nombre = $programa->nombre_prg;

            static::ejecutarBajaLogicaConHijos(
                $programa,
                [
                    ['table' => 'malla_curricular', 'pk' => 'id_mac', 'fk' => 'prg_id'],
                    ['table' => 'malla_curricular_detalle', 'pk' => 'id_mcd', 'fk' => 'mac_id'],
                ],
                $userId,
                self::STATUS_INACTIVO
            );

            return [
                'nombre'    => $nombre,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }
}
