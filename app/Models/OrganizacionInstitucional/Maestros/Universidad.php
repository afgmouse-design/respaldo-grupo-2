<?php

namespace App\Models\OrganizacionInstitucional\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use App\Models\EstructuraGeografica\Maestros\Estado;
use App\Models\EstructuraGeografica\Maestros\Localidad;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Universidad (tabla `universidad`).
 *
 * Propósito: Entidad institucional matriz que representa la institución universitaria rectora.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`cse_id -> condicion_sede.id_cse`, `dpi_id`,
 *    `esd_id`, `url_pagina_uni`, `fax_uni`, `status_id`, `SoftDeletes`, `users.username`,
 *    sin columna booleana `status`).
 *  - 2026-11-16: Añadido `eliminarConCascada` para baja lógica en interfaz (`deleted_at` +
 *    `status_id = 2`) hacia sedes asociadas sin eliminar filas en PostgreSQL.
 */
class Universidad extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    protected $table = 'universidad';
    protected $primaryKey = 'id_uni';
    protected $moduloAuditoria = 'Universidad';

    protected $fillable = [
        'cse_id',
        'dpi_id',
        'esd_id',
        'loc_id',
        'codigo_ministerio_uni',
        'nombre_uni',
        'siglas_uni',
        'telefono_uni',
        'fax_uni',
        'correo_uni',
        'gaceta_uni',
        'fecha_inicio_uni',
        'fecha_fin_uni',
        'url_pagina_uni',
        'logo_uni',
        'rif_uni',
        'decreto_uni',
        'fecha_gaceta_uni',
        'fecha_decreto_uni',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_uni'            => 'integer',
        'cse_id'            => 'integer',
        'dpi_id'            => 'integer',
        'esd_id'            => 'integer',
        'loc_id'            => 'integer',
        'gaceta_uni'        => 'integer',
        'decreto_uni'       => 'integer',
        'status_id'         => 'integer',
        'fecha_gaceta_uni'  => 'date',
        'fecha_decreto_uni' => 'date',
        'fecha_inicio_uni'  => 'date',
        'fecha_fin_uni'     => 'date',
    ];

    public function setNombreUniAttribute($value): void
    {
        $this->attributes['nombre_uni'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function setSiglasUniAttribute($value): void
    {
        $this->attributes['siglas_uni'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function setRifUniAttribute($value): void
    {
        $this->attributes['rif_uni'] = $value ? mb_strtoupper(trim((string) $value), 'UTF-8') : null;
    }

    public function condicionSede(): BelongsTo
    {
        return $this->belongsTo(CondicionSede::class, 'cse_id', 'id_cse');
    }

    public function dependenciaInstitucion(): BelongsTo
    {
        return $this->belongsTo(DependenciaInstitucion::class, 'dpi_id', 'id_dpi');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'esd_id', 'id_esd');
    }

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'loc_id', 'id_loc');
    }

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'uni_id', 'id_uni');
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

    public static function obtenerPaginados(array $filtros = [], int $perPage = 5): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $statusId = static::resolverFiltroStatusId($filtros['status'] ?? $filtros['status_id'] ?? null);

        $query = DB::table('universidad')
            ->leftJoin('localidad', 'localidad.id_loc', '=', 'universidad.loc_id')
            ->leftJoin('parroquia', 'parroquia.id_par', '=', 'localidad.par_id')
            ->leftJoin('municipio', 'municipio.id_mun', '=', 'parroquia.mun_id')
            ->leftJoin('estado', 'estado.id_esd', '=', 'municipio.esd_id')
            ->whereNull('universidad.deleted_at')
            ->when($statusId !== null, fn ($q) => $q->where('universidad.status_id', $statusId))
            ->select(
                'universidad.*',
                'localidad.id_loc as loc_id_loc',
                'localidad.nombre_loc as loc_nombre_loc',
                'parroquia.id_par as loc_id_par',
                'parroquia.nombre_par as loc_nombre_par',
                'municipio.id_mun as loc_id_mun',
                'municipio.nombre_mun as loc_nombre_mun',
                'estado.id_esd as loc_id_esd',
                'estado.nombre_esd as loc_nombre_esd'
            );

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('universidad.nombre_uni', 'ilike', "%{$search}%")
                    ->orWhere('universidad.siglas_uni', 'ilike', "%{$search}%")
                    ->orWhere('universidad.rif_uni', 'ilike', "%{$search}%")
                    ->orWhere('universidad.codigo_ministerio_uni', 'ilike', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('universidad.id_uni', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($item) {
            $item->localidad = $item->loc_id_loc ? (object) [
                'id_loc'     => $item->loc_id_loc,
                'nombre_loc' => $item->loc_nombre_loc,
                'parroquia'  => $item->loc_id_par ? (object) [
                    'id_par'     => $item->loc_id_par,
                    'nombre_par' => $item->loc_nombre_par,
                    'municipio'  => $item->loc_id_mun ? (object) [
                        'id_mun'     => $item->loc_id_mun,
                        'nombre_mun' => $item->loc_nombre_mun,
                        'estado'     => $item->loc_id_esd ? (object) [
                            'id_esd'     => $item->loc_id_esd,
                            'nombre_esd' => $item->loc_nombre_esd,
                        ] : null,
                    ] : null,
                ] : null,
            ] : null;

            unset(
                $item->loc_id_loc,
                $item->loc_nombre_loc,
                $item->loc_id_par,
                $item->loc_nombre_par,
                $item->loc_id_mun,
                $item->loc_nombre_mun,
                $item->loc_id_esd,
                $item->loc_nombre_esd
            );

            return $item;
        });

        return $paginator;
    }

    public static function registrarUniversidad(array $datos, ?int $userId = null): object
    {
        $now = now();
        $cseId = $datos['cse_id'] ?? $datos['cdn_id'] ?? null;
        $urlPagina = $datos['url_pagina_uni'] ?? $datos['sitio_web_uni'] ?? null;

        $insertData = [
            'nombre_uni'            => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_uni'])), MB_CASE_TITLE, 'UTF-8'),
            'codigo_ministerio_uni' => ! empty($datos['codigo_ministerio_uni']) ? substr(trim((string) $datos['codigo_ministerio_uni']), 0, 10) : null,
            'siglas_uni'            => ! empty($datos['siglas_uni']) ? substr(mb_strtoupper(trim((string) $datos['siglas_uni']), 'UTF-8'), 0, 10) : null,
            'rif_uni'               => ! empty($datos['rif_uni']) ? substr(mb_strtoupper(trim((string) $datos['rif_uni']), 'UTF-8'), 0, 12) : null,
            'telefono_uni'          => ! empty($datos['telefono_uni']) ? substr(trim((string) $datos['telefono_uni']), 0, 12) : null,
            'fax_uni'               => ! empty($datos['fax_uni']) ? substr(trim((string) $datos['fax_uni']), 0, 12) : null,
            'correo_uni'            => ! empty($datos['correo_uni']) ? trim((string) $datos['correo_uni']) : null,
            'url_pagina_uni'        => ! empty($urlPagina) ? trim((string) $urlPagina) : null,
            'logo_uni'              => ! empty($datos['logo_uni']) ? trim((string) $datos['logo_uni']) : null,
            'gaceta_uni'            => isset($datos['gaceta_uni']) && $datos['gaceta_uni'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['gaceta_uni']) : null,
            'fecha_gaceta_uni'      => ! empty($datos['fecha_gaceta_uni']) ? $datos['fecha_gaceta_uni'] : null,
            'decreto_uni'           => isset($datos['decreto_uni']) && $datos['decreto_uni'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['decreto_uni']) : null,
            'fecha_decreto_uni'     => ! empty($datos['fecha_decreto_uni']) ? $datos['fecha_decreto_uni'] : null,
            'fecha_inicio_uni'      => ! empty($datos['fecha_inicio_uni']) ? $datos['fecha_inicio_uni'] : null,
            'fecha_fin_uni'         => ! empty($datos['fecha_fin_uni']) ? $datos['fecha_fin_uni'] : null,
            'cse_id'                => ! empty($cseId) ? (int) $cseId : null,
            'dpi_id'                => ! empty($datos['dpi_id']) ? (int) $datos['dpi_id'] : null,
            'esd_id'                => ! empty($datos['esd_id']) ? (int) $datos['esd_id'] : null,
            'loc_id'                => ! empty($datos['loc_id']) ? (int) $datos['loc_id'] : null,
            'status_id'             => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
            'created_at'            => $now,
            'updated_at'            => $now,
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('universidad')->insertGetId($insertData, 'id_uni');

        return static::obtenerPorId($id);
    }

    public static function crearUniversidad(array $datos, ?int $userId = null): object
    {
        return static::registrarUniversidad($datos, $userId);
    }

    public static function actualizarUniversidad(int $id_uni, array $datos, ?int $userId = null): object
    {
        $updateData = ['updated_at' => now()];

        if (array_key_exists('nombre_uni', $datos)) {
            $updateData['nombre_uni'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_uni'])), MB_CASE_TITLE, 'UTF-8');
        }
        if (array_key_exists('codigo_ministerio_uni', $datos)) {
            $updateData['codigo_ministerio_uni'] = ! empty($datos['codigo_ministerio_uni']) ? substr(trim((string) $datos['codigo_ministerio_uni']), 0, 10) : null;
        }
        if (array_key_exists('siglas_uni', $datos)) {
            $updateData['siglas_uni'] = ! empty($datos['siglas_uni']) ? substr(mb_strtoupper(trim((string) $datos['siglas_uni']), 'UTF-8'), 0, 10) : null;
        }
        if (array_key_exists('rif_uni', $datos)) {
            $updateData['rif_uni'] = ! empty($datos['rif_uni']) ? substr(mb_strtoupper(trim((string) $datos['rif_uni']), 'UTF-8'), 0, 12) : null;
        }
        if (array_key_exists('telefono_uni', $datos)) {
            $updateData['telefono_uni'] = ! empty($datos['telefono_uni']) ? substr(trim((string) $datos['telefono_uni']), 0, 12) : null;
        }
        if (array_key_exists('fax_uni', $datos)) {
            $updateData['fax_uni'] = ! empty($datos['fax_uni']) ? substr(trim((string) $datos['fax_uni']), 0, 12) : null;
        }
        if (array_key_exists('correo_uni', $datos)) {
            $updateData['correo_uni'] = ! empty($datos['correo_uni']) ? trim((string) $datos['correo_uni']) : null;
        }
        if (array_key_exists('url_pagina_uni', $datos) || array_key_exists('sitio_web_uni', $datos)) {
            $urlPagina = $datos['url_pagina_uni'] ?? $datos['sitio_web_uni'] ?? null;
            $updateData['url_pagina_uni'] = ! empty($urlPagina) ? trim((string) $urlPagina) : null;
        }
        if (array_key_exists('logo_uni', $datos)) {
            $updateData['logo_uni'] = ! empty($datos['logo_uni']) ? trim((string) $datos['logo_uni']) : null;
        }
        if (array_key_exists('gaceta_uni', $datos)) {
            $updateData['gaceta_uni'] = $datos['gaceta_uni'] !== null && $datos['gaceta_uni'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['gaceta_uni']) : null;
        }
        if (array_key_exists('fecha_gaceta_uni', $datos)) {
            $updateData['fecha_gaceta_uni'] = ! empty($datos['fecha_gaceta_uni']) ? $datos['fecha_gaceta_uni'] : null;
        }
        if (array_key_exists('decreto_uni', $datos)) {
            $updateData['decreto_uni'] = $datos['decreto_uni'] !== null && $datos['decreto_uni'] !== '' ? (int) preg_replace('/\D/', '', (string) $datos['decreto_uni']) : null;
        }
        if (array_key_exists('fecha_decreto_uni', $datos)) {
            $updateData['fecha_decreto_uni'] = ! empty($datos['fecha_decreto_uni']) ? $datos['fecha_decreto_uni'] : null;
        }
        if (array_key_exists('fecha_inicio_uni', $datos)) {
            $updateData['fecha_inicio_uni'] = ! empty($datos['fecha_inicio_uni']) ? $datos['fecha_inicio_uni'] : null;
        }
        if (array_key_exists('fecha_fin_uni', $datos)) {
            $updateData['fecha_fin_uni'] = ! empty($datos['fecha_fin_uni']) ? $datos['fecha_fin_uni'] : null;
        }
        if (array_key_exists('cse_id', $datos) || array_key_exists('cdn_id', $datos)) {
            $cseId = $datos['cse_id'] ?? $datos['cdn_id'] ?? null;
            $updateData['cse_id'] = ! empty($cseId) ? (int) $cseId : null;
        }
        if (array_key_exists('dpi_id', $datos)) {
            $updateData['dpi_id'] = ! empty($datos['dpi_id']) ? (int) $datos['dpi_id'] : null;
        }
        if (array_key_exists('esd_id', $datos)) {
            $updateData['esd_id'] = ! empty($datos['esd_id']) ? (int) $datos['esd_id'] : null;
        }
        if (array_key_exists('loc_id', $datos)) {
            $updateData['loc_id'] = ! empty($datos['loc_id']) ? (int) $datos['loc_id'] : null;
        }
        if (array_key_exists('status_id', $datos)) {
            $updateData['status_id'] = (int) $datos['status_id'];
        } elseif (array_key_exists('status', $datos)) {
            $updateData['status_id'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN)
                ? self::STATUS_ACTIVO
                : self::STATUS_INACTIVO;
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('universidad')
            ->where('id_uni', $id_uni)
            ->whereNull('deleted_at')
            ->update($updateData);

        return static::obtenerPorId($id_uni);
    }

    public static function obtenerPorId(int $id_uni): object
    {
        $universidad = DB::table('universidad')
            ->where('id_uni', $id_uni)
            ->whereNull('deleted_at')
            ->first();

        if (! $universidad) {
            abort(404, 'Universidad no encontrada.');
        }

        return $universidad;
    }

    public static function obtenerParaEditar(int $id_uni): object
    {
        return static::obtenerPorId($id_uni);
    }

    public static function obtenerConRelaciones(int $id_uni): object
    {
        $universidad = static::obtenerPorId($id_uni);

        $universidad->condicion_sede = $universidad->cse_id
            ? DB::table('condicion_sede')->select(['id_cse', 'nombre_cse'])->where('id_cse', $universidad->cse_id)->first()
            : null;

        $universidad->dependencia_institucion = $universidad->dpi_id
            ? DB::table('dependencia_institucion')->select(['id_dpi', 'nombre_dpi'])->where('id_dpi', $universidad->dpi_id)->first()
            : null;

        if ($universidad->loc_id) {
            $localidad = DB::table('localidad')->select(['id_loc', 'nombre_loc', 'par_id'])->where('id_loc', $universidad->loc_id)->first();
            if ($localidad) {
                $parroquia = DB::table('parroquia')->select(['id_par', 'nombre_par', 'mun_id'])->where('id_par', $localidad->par_id)->first();
                if ($parroquia) {
                    $municipio = DB::table('municipio')->select(['id_mun', 'nombre_mun', 'esd_id'])->where('id_mun', $parroquia->mun_id)->first();
                    if ($municipio) {
                        $estado = DB::table('estado')->select(['id_esd', 'nombre_esd', 'pai_id'])->where('id_esd', $municipio->esd_id)->first();
                        if ($estado) {
                            $estado->pais = DB::table('pais')->select(['id_pai', 'nombre_pai'])->where('id_pai', $estado->pai_id)->first();
                        }
                        $municipio->estado = $estado;
                    }
                    $parroquia->municipio = $municipio;
                }
                $localidad->parroquia = $parroquia;
            }
            $universidad->localidad = $localidad;
        } else {
            $universidad->localidad = null;
        }

        $universidad->creador = $universidad->created_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $universidad->created_id)->first()
            : null;

        $universidad->actualizador = $universidad->updated_id
            ? DB::table('users')->select(['id', 'username', 'email'])->where('id', $universidad->updated_id)->first()
            : null;

        return $universidad;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('universidad')
            ->select(['id_uni', 'nombre_uni', 'siglas_uni'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_uni', 'asc')
            ->get();
    }

    public static function alternarEstatusConCascada(int $id_uni, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_uni, $userId) {
            $universidad = static::obtenerPorId($id_uni);
            $activar = ! static::esStatusActivo($universidad);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();
            $now = now();

            DB::table('universidad')
                ->where('id_uni', $id_uni)
                ->update([
                    'status_id'  => $nuevoStatusId,
                    'updated_id' => $usuarioId,
                    'updated_at' => $now,
                ]);

            if (! $activar) {
                DB::table('sede')
                    ->where('uni_id', $id_uni)
                    ->update([
                        'status_id'  => self::STATUS_INACTIVO,
                        'updated_id' => $usuarioId,
                        'updated_at' => $now,
                    ]);
            }

            return [
                'nombre'    => $universidad->nombre_uni,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    public static function alternarEstatus(int $id_uni, ?int $userId = null): array
    {
        return static::alternarEstatusConCascada($id_uni, $userId);
    }

    /**
     * Elimina lógicamente la universidad y sus sedes dependientes (`deleted_at` + `status_id = 2`)
     * preservando todos los registros en PostgreSQL.
     */
    public static function eliminarConCascada(int $id_uni, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_uni, $userId) {
            $universidad = static::find($id_uni);
            if (! $universidad) {
                abort(404, 'Universidad no encontrada.');
            }

            $nombre = $universidad->nombre_uni;

            static::ejecutarBajaLogicaConHijos(
                $universidad,
                [
                    ['table' => 'sede', 'pk' => 'id_sed', 'fk' => 'uni_id'],
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

    public static function obtenerRegistroMatriz(): ?object
    {
        return DB::table('universidad')->whereNull('deleted_at')->orderBy('id_uni', 'asc')->first();
    }
}
