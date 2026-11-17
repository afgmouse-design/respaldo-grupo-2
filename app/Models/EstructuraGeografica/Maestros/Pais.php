<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\HandlesSoftDeleteWithStatus;
use App\Models\Concerns\RegistraEnBitacora;
use App\Models\Concerns\UsesStatusCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Pais (tabla `pais`).
 *
 * Propósito: Representa el nivel raíz de la Estructura Geográfica del SOGAC.
 *
 * Historial:
 *  - 2026-11-14: Actualizado a convenciones de `sogac_mio.sql` (`status_id` entero 1/2,
 *    `SoftDeletes` + `timestamps`, sin columna booleana `status` ni casts heredados).
 *  - 2026-11-16: Se añade `eliminarConCascada` para baja lógica en interfaz (`deleted_at` +
 *    `status_id = 2`) manteniendo el registro en PostgreSQL, y liberación de `nombre_pai`
 *    UNIQUE en registros eliminados lógicamente al crear o actualizar.
 */
class Pais extends Model
{
    use SoftDeletes;
    use RegistraEnBitacora;
    use UsesStatusCatalog;
    use HandlesSoftDeleteWithStatus;

    /**
     * Niveles territoriales dependientes de un país en orden jerárquico descendente.
     */
    public const NIVELES_CASCADA_TERRITORIAL = [
        ['table' => 'estado', 'pk' => 'id_esd', 'fk' => 'pai_id'],
        ['table' => 'municipio', 'pk' => 'id_mun', 'fk' => 'esd_id'],
        ['table' => 'parroquia', 'pk' => 'id_par', 'fk' => 'mun_id'],
        ['table' => 'localidad', 'pk' => 'id_loc', 'fk' => 'par_id'],
    ];

    protected $table = 'pais';
    protected $primaryKey = 'id_pai';
    protected $moduloAuditoria = 'Pais';

    protected $fillable = [
        'nombre_pai',
        'status_id',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'id_pai' => 'integer',
        'status_id' => 'integer',
    ];

    public function setNombrePaiAttribute($value): void
    {
        $this->attributes['nombre_pai'] = mb_convert_case(
            trim(preg_replace('/[0-9]/', '', (string) $value)),
            MB_CASE_TITLE,
            'UTF-8'
        );
    }

    public function estados(): HasMany
    {
        return $this->hasMany(Estado::class, 'pai_id', 'id_pai');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status_id', self::STATUS_ACTIVO);
    }

    /**
     * Consulta paginada con filtros dinámicos mediante Query Builder puro.
     */
    public static function obtenerPaginados(array|int $filtros = [], ?int $perPage = 5): LengthAwarePaginator
    {
        if (is_int($filtros) && ! is_array($perPage)) {
            $cantidadPorPagina = $filtros;
            $search = is_string($perPage) ? $perPage : null;
            $status = null;
        } else {
            $cantidadPorPagina = is_int($perPage) ? $perPage : 5;
            $search = is_array($filtros) ? ($filtros['search'] ?? null) : null;
            $status = is_array($filtros) ? ($filtros['status'] ?? $filtros['status_id'] ?? null) : null;
        }

        $statusId = static::resolverFiltroStatusId($status);

        return DB::table('pais')
            ->select('pais.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM estado WHERE estado.pai_id = pais.id_pai AND estado.deleted_at IS NULL) as estados_count')
            ->whereNull('pais.deleted_at')
            ->when($statusId !== null, fn ($query) => $query->where('pais.status_id', $statusId))
            ->when(! empty($search), fn ($query) => $query->where('nombre_pai', 'ilike', "%{$search}%"))
            ->orderBy('id_pai', 'desc')
            ->paginate($cantidadPorPagina)
            ->withQueryString();
    }

    /**
     * Registra un nuevo país mediante Query Builder puro, liberando nombres
     * previamente eliminados lógicamente para evitar colisiones `uq_pais_nombre_pai`.
     */
    public static function registrarPais(array $datos, ?int $userId = null): object
    {
        return DB::transaction(function () use ($datos, $userId) {
            $nombreNormalizado = mb_convert_case(
                trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_pai'])),
                MB_CASE_TITLE,
                'UTF-8'
            );

            static::liberarValorUnicoEliminado(
                'pais',
                'id_pai',
                'nombre_pai',
                $nombreNormalizado,
                null,
                255,
                'Ya existe un país registrado con ese nombre.'
            );

            $now = now();
            $insertData = [
                'nombre_pai' => $nombreNormalizado,
                'status_id'  => isset($datos['status_id']) ? (int) $datos['status_id'] : self::STATUS_ACTIVO,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($userId || auth()->check()) {
                $insertData['created_id'] = $userId ?? auth()->id();
            }

            $id = DB::table('pais')->insertGetId($insertData, 'id_pai');

            return static::obtenerPorId($id);
        });
    }

    public static function crearPais(array $datos, ?int $userId = null): object
    {
        return static::registrarPais($datos, $userId);
    }

    /**
     * Actualiza un país existente mediante Query Builder puro.
     */
    public static function actualizarPais(int $id_pai, array $datos, ?int $userId = null): object
    {
        return DB::transaction(function () use ($id_pai, $datos, $userId) {
            $updateData = ['updated_at' => now()];

            if (isset($datos['nombre_pai'])) {
                $nombreNormalizado = mb_convert_case(
                    trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_pai'])),
                    MB_CASE_TITLE,
                    'UTF-8'
                );

                static::liberarValorUnicoEliminado(
                    'pais',
                    'id_pai',
                    'nombre_pai',
                    $nombreNormalizado,
                    $id_pai,
                    255,
                    'Ya existe otro país registrado con ese nombre.'
                );

                $updateData['nombre_pai'] = $nombreNormalizado;
            }

            if (isset($datos['status_id'])) {
                $updateData['status_id'] = (int) $datos['status_id'];
            } elseif (isset($datos['status'])) {
                $updateData['status_id'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN)
                    ? self::STATUS_ACTIVO
                    : self::STATUS_INACTIVO;
            }

            if ($userId || auth()->check()) {
                $updateData['updated_id'] = $userId ?? auth()->id();
            }

            DB::table('pais')
                ->where('id_pai', $id_pai)
                ->whereNull('deleted_at')
                ->update($updateData);

            return static::obtenerPorId($id_pai);
        });
    }

    public static function obtenerPorId(int $id_pai): object
    {
        $pais = DB::table('pais')
            ->where('id_pai', $id_pai)
            ->whereNull('deleted_at')
            ->first();

        if (! $pais) {
            abort(404, 'País no encontrado.');
        }

        return $pais;
    }

    public static function obtenerConEstados(int $id_pai): object
    {
        $pais = static::obtenerPorId($id_pai);

        $pais->estados = DB::table('estado')
            ->select('estado.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM municipio WHERE municipio.esd_id = estado.id_esd AND municipio.deleted_at IS NULL) as municipios_count')
            ->where('pai_id', $id_pai)
            ->whereNull('deleted_at')
            ->orderBy('nombre_esd', 'asc')
            ->get();

        return $pais;
    }

    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('pais')
            ->select(['id_pai', 'nombre_pai'])
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_pai', 'asc')
            ->get();
    }

    /**
     * Alterna el `status_id` del país y propaga en cascada a toda la jerarquía territorial.
     */
    public static function alternarEstatusConCascada(int $id_pai, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_pai, $userId) {
            $pais = static::obtenerPorId($id_pai);
            $activar = ! static::esStatusActivo($pais);
            $nuevoStatusId = $activar ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
            $usuarioId = $userId ?? auth()->id();

            static::aplicarStatusCascadaPais($id_pai, $nuevoStatusId, $usuarioId);

            return [
                'nombre'    => $pais->nombre_pai,
                'status_id' => $nuevoStatusId,
                'estatus'   => $activar,
            ];
        });
    }

    /**
     * Elimina lógicamente el país de la interfaz (status_id = 2 + deleted_at)
     * propagando la baja a su jerarquía territorial sin borrar filas en BD.
     */
    public static function eliminarConCascada(int $id_pai, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_pai, $userId) {
            $pais = static::find($id_pai);
            if (! $pais) {
                abort(404, 'País no encontrado.');
            }

            $nombre = $pais->nombre_pai;

            static::ejecutarBajaLogicaConHijos(
                $pais,
                static::NIVELES_CASCADA_TERRITORIAL,
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

    public static function desactivarConCascada(int $id_pai, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_pai, $userId) {
            $pais = static::obtenerPorId($id_pai);
            $usuarioId = $userId ?? auth()->id();

            static::aplicarStatusCascadaPais($id_pai, self::STATUS_INACTIVO, $usuarioId);

            return [
                'nombre'    => $pais->nombre_pai,
                'status_id' => self::STATUS_INACTIVO,
                'estatus'   => false,
            ];
        });
    }

    private static function aplicarStatusCascadaPais(int $id_pai, int $nuevoStatusId, ?int $usuarioId): void
    {
        $now = now();
        $payload = ['status_id' => $nuevoStatusId, 'updated_id' => $usuarioId, 'updated_at' => $now];

        DB::table('pais')->where('id_pai', $id_pai)->update($payload);

        $estadoIds = DB::table('estado')->where('pai_id', $id_pai)->pluck('id_esd');
        if ($estadoIds->isEmpty()) {
            return;
        }
        DB::table('estado')->whereIn('id_esd', $estadoIds)->update($payload);

        $municipioIds = DB::table('municipio')->whereIn('esd_id', $estadoIds)->pluck('id_mun');
        if ($municipioIds->isEmpty()) {
            return;
        }
        DB::table('municipio')->whereIn('id_mun', $municipioIds)->update($payload);

        $parroquiaIds = DB::table('parroquia')->whereIn('mun_id', $municipioIds)->pluck('id_par');
        if ($parroquiaIds->isEmpty()) {
            return;
        }
        DB::table('parroquia')->whereIn('id_par', $parroquiaIds)->update($payload);
        DB::table('localidad')->whereIn('par_id', $parroquiaIds)->update($payload);
    }
}
