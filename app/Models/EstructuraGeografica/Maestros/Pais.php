<?php

namespace App\Models\EstructuraGeografica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Pais (tabla `pais`).
 *
 * Propósito: Representa el nivel raíz de la Estructura Geográfica del SOGAC.
 *
 * Reglas de Arquitectura:
 *  - Sin SoftDeletes (la tabla no posee columna deleted_at).
 *  - Sin timestamps automáticos ($timestamps = false).
 *  - Query Builder puro con DB::table('pais') para consultas, inserciones y actualizaciones.
 *  - Prohibido el uso de self::query(), create(), update() o save() en métodos de persistencia.
 */
class Pais extends Model
{
    use RegistraEnBitacora;

    protected $table = 'pais';
    protected $primaryKey = 'id_pai';
    protected $moduloAuditoria = 'Pais';

    /** Desactivación de timestamps automáticos de Eloquent. */
    public $timestamps = false;

    protected $fillable = [
        'nombre_pai',
        'status',
        'created_id',
        'updated_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'est_pai',
    ];

    public function getEstPaiAttribute(): bool
    {
        return (bool) $this->status;
    }

    public function setNombrePaiAttribute($value): void
    {
        $this->attributes['nombre_pai'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $value)), MB_CASE_TITLE, 'UTF-8');
    }

    public function estados(): HasMany
    {
        return $this->hasMany(Estado::class, 'pai_id', 'id_pai');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    // =========================================================================
    // MÉTODOS ESTÁTICOS DE QUERY BUILDER PURO (DB::table)
    // =========================================================================

    /**
     * 1. Consulta paginada con filtros dinámicos mediante Query Builder puro.
     */
    public static function obtenerPaginados(array|int $filtros = [], ?int $perPage = 5): LengthAwarePaginator
    {
        // Compatibilidad si se pasan los parámetros invertidos ($perPage, $search)
        if (is_int($filtros) && !is_array($perPage)) {
            $cantidadPorPagina = $filtros;
            $search = is_string($perPage) ? $perPage : null;
            $status = null;
        } else {
            $cantidadPorPagina = is_int($perPage) ? $perPage : 5;
            $search = is_array($filtros) ? ($filtros['search'] ?? null) : null;
            $status = is_array($filtros) ? ($filtros['status'] ?? null) : null;
        }

        return DB::table('pais')
            ->select('pais.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM estado WHERE estado.pai_id = pais.id_pai) as estados_count')
            ->when($status !== null && $status !== '' && $status !== 'all', function ($query) use ($status) {
                $query->where('pais.status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('nombre_pai', 'ilike', "%{$search}%");
            })
            ->orderBy('id_pai', 'desc')
            ->paginate($cantidadPorPagina)
            ->withQueryString();
    }

    /**
     * 2. Registra un nuevo país mediante Query Builder puro (DB::table insert).
     */
    public static function registrarPais(array $datos, ?int $userId = null): object
    {
        $insertData = [
            'nombre_pai' => mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_pai'])), MB_CASE_TITLE, 'UTF-8'),
            'status'     => true, // Columna booleana real de la base de datos
        ];

        if ($userId || auth()->check()) {
            $insertData['created_id'] = $userId ?? auth()->id();
        }

        $id = DB::table('pais')->insertGetId($insertData, 'id_pai');

        return static::obtenerPorId($id);
    }

    /**
     * Alias de registrarPais.
     */
    public static function crearPais(array $datos, ?int $userId = null): object
    {
        return static::registrarPais($datos, $userId);
    }

    /**
     * 3. Actualiza un país existente mediante Query Builder puro (DB::table update).
     */
    public static function actualizarPais(int $id_pai, array $datos, ?int $userId = null): object
    {
        $updateData = [];

        if (isset($datos['nombre_pai'])) {
            $updateData['nombre_pai'] = mb_convert_case(trim(preg_replace('/[0-9]/', '', (string) $datos['nombre_pai'])), MB_CASE_TITLE, 'UTF-8');
        }

        if (isset($datos['status'])) {
            $updateData['status'] = filter_var($datos['status'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($userId || auth()->check()) {
            $updateData['updated_id'] = $userId ?? auth()->id();
        }

        DB::table('pais')
            ->where('id_pai', $id_pai)
            ->update($updateData);

        return static::obtenerPorId($id_pai);
    }

    /**
     * Busca un país por su clave primaria mediante Query Builder puro.
     */
    public static function obtenerPorId(int $id_pai): object
    {
        $pais = DB::table('pais')->where('id_pai', $id_pai)->first();

        if (!$pais) {
            abort(404, 'País no encontrado.');
        }

        return $pais;
    }

    /**
     * Obtiene el detalle de un país y sus estados subordinados mediante Query Builder puro.
     */
    public static function obtenerConEstados(int $id_pai): object
    {
        $pais = static::obtenerPorId($id_pai);

        $pais->estados = DB::table('estado')
            ->select('estado.*')
            ->selectRaw('(SELECT COUNT(*)::int FROM municipio WHERE municipio.esd_id = estado.id_esd) as municipios_count')
            ->where('pai_id', $id_pai)
            ->orderBy('nombre_esd', 'asc')
            ->get();

        return $pais;
    }

    /**
     * Obtiene países activos para llenar selectores con Query Builder puro.
     */
    public static function obtenerActivosParaSelect(): \Illuminate\Support\Collection
    {
        return DB::table('pais')
            ->select(['id_pai', 'nombre_pai'])
            ->where('status', true)
            ->orderBy('nombre_pai', 'asc')
            ->get();
    }

    /**
     * Alterna el estatus lógico del país y propaga en cascada mediante Query Builder puro.
     */
    public static function alternarEstatusConCascada(int $id_pai, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_pai, $userId) {
            $pais = static::obtenerPorId($id_pai);
            $nuevoEstatus = !$pais->status;
            $usuarioId = $userId ?? auth()->id();

            // 1. Actualizar País
            DB::table('pais')
                ->where('id_pai', $id_pai)
                ->update([
                    'status'     => $nuevoEstatus,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener Estados vinculados
            $estadoIds = DB::table('estado')
                ->where('pai_id', $pais->id_pai)
                ->pluck('id_esd');

            if ($estadoIds->isNotEmpty()) {
                DB::table('estado')
                    ->whereIn('id_esd', $estadoIds)
                    ->update([
                        'status'     => $nuevoEstatus,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Obtener Municipios vinculados
                $municipioIds = DB::table('municipio')
                    ->whereIn('esd_id', $estadoIds)
                    ->pluck('id_mun');

                if ($municipioIds->isNotEmpty()) {
                    DB::table('municipio')
                        ->whereIn('id_mun', $municipioIds)
                        ->update([
                            'status'     => $nuevoEstatus,
                            'updated_id' => $usuarioId,
                        ]);

                    // 4. Obtener Parroquias vinculadas
                    $parroquiaIds = DB::table('parroquia')
                        ->whereIn('mun_id', $municipioIds)
                        ->pluck('id_par');

                    if ($parroquiaIds->isNotEmpty()) {
                        DB::table('parroquia')
                            ->whereIn('id_par', $parroquiaIds)
                            ->update([
                                'status'     => $nuevoEstatus,
                                'updated_id' => $usuarioId,
                            ]);

                        // 5. Actualizar Localidades vinculadas
                        DB::table('localidad')
                            ->whereIn('par_id', $parroquiaIds)
                            ->update([
                                'status'     => $nuevoEstatus,
                                'updated_id' => $usuarioId,
                            ]);
                    }
                }
            }

            return [
                'nombre'  => $pais->nombre_pai,
                'estatus' => $nuevoEstatus,
            ];
        });
    }

    /**
     * Realiza la baja lógica del país apagando (status = false) en cascada a todos sus descendientes.
     * Cascada: País -> Estados -> Municipios -> Parroquias -> Localidades.
     */
    public static function desactivarConCascada(int $id_pai, ?int $userId = null): array
    {
        return DB::transaction(function () use ($id_pai, $userId) {
            $pais = static::obtenerPorId($id_pai);
            $usuarioId = $userId ?? auth()->id();

            // 1. Apagar País
            DB::table('pais')
                ->where('id_pai', $id_pai)
                ->update([
                    'status'     => false,
                    'updated_id' => $usuarioId,
                ]);

            // 2. Obtener Estados vinculados
            $estadoIds = DB::table('estado')
                ->where('pai_id', $pais->id_pai)
                ->pluck('id_esd');

            if ($estadoIds->isNotEmpty()) {
                // Apagar Estados
                DB::table('estado')
                    ->whereIn('id_esd', $estadoIds)
                    ->update([
                        'status'     => false,
                        'updated_id' => $usuarioId,
                    ]);

                // 3. Obtener Municipios vinculados
                $municipioIds = DB::table('municipio')
                    ->whereIn('esd_id', $estadoIds)
                    ->pluck('id_mun');

                if ($municipioIds->isNotEmpty()) {
                    // Apagar Municipios
                    DB::table('municipio')
                        ->whereIn('id_mun', $municipioIds)
                        ->update([
                            'status'     => false,
                            'updated_id' => $usuarioId,
                        ]);

                    // 4. Obtener Parroquias vinculadas
                    $parroquiaIds = DB::table('parroquia')
                        ->whereIn('mun_id', $municipioIds)
                        ->pluck('id_par');

                    if ($parroquiaIds->isNotEmpty()) {
                        // Apagar Parroquias
                        DB::table('parroquia')
                            ->whereIn('id_par', $parroquiaIds)
                            ->update([
                                'status'     => false,
                                'updated_id' => $usuarioId,
                            ]);

                        // 5. Apagar Localidades vinculadas
                        DB::table('localidad')
                            ->whereIn('par_id', $parroquiaIds)
                            ->update([
                                'status'     => false,
                                'updated_id' => $usuarioId,
                            ]);
                    }
                }
            }

            return [
                'nombre'  => $pais->nombre_pai,
                'estatus' => false,
            ];
        });
    }
}