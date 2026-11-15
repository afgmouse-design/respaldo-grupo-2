<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Año Académico (tabla `ano_academico`).
 *
 * Entidad Padre: Macro-ciclo institucional que agrupa lapsos académicos.
 * Arquitectura: Fat Model (Modelo Gordo) con Query Builder puro.
 *
 * Actualización 2026-10-21 (Regla Único Año Activo y Filtrado Dinámico de Estatus):
 * Se actualizó `obtenerPaginados()` admitiendo el parámetro `$status` para permitir visualizar
 * registros activos, inactivos o todos en el TabFilter de Vue. Se blindó `alternarEstatus()`
 * para impedir activar más de un año académico en simultáneo en todo el sistema.
 *
 * Actualización 2026-10-21 (Desactivación en Cascada: Año -> Lapsos -> Cohortes -> CohorteSede):
 * Se incorporó en `alternarEstatus()` la propagación en cascada transaccional para que
 * al desactivar un año académico se desactiven automáticamente todos sus períodos lectivos
 * (lapsos académicos), las cohortes asociadas a los mismos y las cohortes por sede.
 *
 * Actualización 2026-10-21 (Inclusión de Fechas en Catálogo de Selección):
 * Se incorporaron `fecha_inicio_ano` y `fecha_fin_ano` en `obtenerActivosParaSelect()` para
 * que la interfaz y validaciones de Lapsos Académicos verifiquen que las fechas no excedan
 * los límites del año académico padre.
 *
 * Actualización 2026-10-21 (Soporte para Eliminación en Cascada y Filtrado deleted_at):
 * Se incorporó `deleted_at` en `$fillable` y `$casts`. Se filtraron los registros en `obtenerPaginados()`,
 * `obtenerPorId()`, `alternarEstatus()` y `obtenerActivosParaSelect()` asegurando que no se consulten
 * años académicos eliminados lógicamente ni interfieran con la regla de único año activo.
 */
class AnoAcademico extends Model
{
    /** Nombre explícito de la tabla en singular */
    protected $table = 'ano_academico';

    /** Llave primaria personalizada */
    protected $primaryKey = 'id_ano';

    /** Desactivación de timestamps automáticos de Eloquent */
    public $timestamps = false;

    /** Campos asignables masivamente */
    protected $fillable = [
        'nombre_ano',
        'fecha_inicio_ano',
        'fecha_fin_ano',
        'status',
        'deleted_at',
    ];

    /** Conversión de tipos */
    protected $casts = [
        'id_ano'           => 'integer',
        'fecha_inicio_ano' => 'date',
        'fecha_fin_ano'    => 'date',
        'status'           => 'boolean',
        'deleted_at'       => 'datetime',
    ];

    // =========================================================================
    // MÉTODOS ESTÁTICOS - QUERY BUILDER PURO (DB::table)
    // =========================================================================

    /**
     * Obtiene los años académicos paginados con filtros de búsqueda, estatus dinámico y orden desc.
     *
     * @param string|null $busqueda
     * @param int $porPagina
     * @param string|bool|int|null $status
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10, $status = null)
    {
        $query = DB::table('ano_academico')
            ->whereNull('deleted_at');

        // Filtrado dinámico por estatus (TabFilter: 'all', 'true', 'false'):
        if ($status === 'true' || $status === '1' || $status === 1 || $status === true) {
            $query->where('status', true);
        } elseif ($status === 'false' || $status === '0' || $status === 0 || $status === false) {
            $query->where('status', false);
        }

        if (!empty($busqueda)) {
            $query->where('nombre_ano', 'like', "%{$busqueda}%");
        }

        return $query->orderBy('fecha_inicio_ano', 'desc')
            ->orderBy('id_ano', 'desc')
            ->paginate($porPagina);
    }

    /**
     * Registra un nuevo año académico mediante inserción directa.
     *
     * @param array $datos
     * @return object|null
     */
    public static function registrar(array $datos)
    {
        $registro = [
            'nombre_ano'       => $datos['nombre_ano'],
            'fecha_inicio_ano' => $datos['fecha_inicio_ano'] ?? null,
            'fecha_fin_ano'    => $datos['fecha_fin_ano'] ?? null,
            'status'           => isset($datos['status']) ? (bool) $datos['status'] : true,
        ];

        $id = DB::table('ano_academico')->insertGetId($registro, 'id_ano');

        return self::obtenerPorId($id);
    }

    /**
     * Actualiza un año académico existente.
     *
     * @param int|string $id
     * @param array $datos
     * @return object|null
     */
    public static function actualizar($id, array $datos)
    {
        $campos = [];

        if (array_key_exists('nombre_ano', $datos)) {
            $campos['nombre_ano'] = $datos['nombre_ano'];
        }
        if (array_key_exists('fecha_inicio_ano', $datos)) {
            $campos['fecha_inicio_ano'] = $datos['fecha_inicio_ano'];
        }
        if (array_key_exists('fecha_fin_ano', $datos)) {
            $campos['fecha_fin_ano'] = $datos['fecha_fin_ano'];
        }
        if (array_key_exists('status', $datos)) {
            $campos['status'] = (bool) $datos['status'];
        }

        if (!empty($campos)) {
            DB::table('ano_academico')
                ->where('id_ano', $id)
                ->update($campos);
        }

        return self::obtenerPorId($id);
    }

    /**
     * Obtiene un año académico específico por su identificador.
     *
     * @param int|string $id
     * @return object|null
     */
    public static function obtenerPorId($id)
    {
        return DB::table('ano_academico')
            ->where('id_ano', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Alterna el status entre activo (true/1) e inactivo (false/0).
     * Aplica la regla de negocio "Único Año Académico Activo" al ACTIVAR,
     * y Desactivación en Cascada (Año -> Lapsos -> Cohortes -> CohorteSede) al DESACTIVAR.
     *
     * @param int|string $id
     * @param bool|null $nuevoEstatus
     * @return bool
     * @throws \DomainException
     */
    public static function alternarEstatus($id, $nuevoEstatus = null)
    {
        $ano = DB::table('ano_academico')->where('id_ano', $id)->whereNull('deleted_at')->first();
        if (!$ano) {
            throw new \DomainException("El año académico solicitado no existe.");
        }

        if (is_null($nuevoEstatus)) {
            $nuevoEstatus = !$ano->status;
        }

        $estatus = (bool) $nuevoEstatus;

        // Regla de Negocio al ACTIVAR: Único Año Académico Activo
        if ($estatus) {
            $otroActivo = DB::table('ano_academico')
                ->where('id_ano', '!=', $id)
                ->where('status', true)
                ->whereNull('deleted_at')
                ->exists();

            if ($otroActivo) {
                throw new \DomainException("Ya existe otro Año Académico activo en el sistema. Desactive el año en curso antes de activar otro.");
            }
        }

        return DB::transaction(function () use ($id, $estatus) {
            // 1. Actualizar el Año Académico
            DB::table('ano_academico')
                ->where('id_ano', $id)
                ->update(['status' => $estatus]);

            // 2. Desactivación en cascada al DESACTIVAR (Año -> Lapsos -> Cohortes -> CohorteSede)
            if (!$estatus) {
                $lapsoIds = DB::table('lapso_academico')
                    ->where('ano_id', $id)
                    ->pluck('id_la');

                if ($lapsoIds->isNotEmpty()) {
                    // Apagar lapsos dependientes
                    DB::table('lapso_academico')
                        ->whereIn('id_la', $lapsoIds)
                        ->update([
                            'status_id' => 2,
                            'status'    => false,
                            'estado_la' => false,
                        ]);

                    // Obtener cohortes dependientes
                    $cohorteIds = DB::table('cohorte')
                        ->whereIn('laa_id', $lapsoIds)
                        ->pluck('id_coh');

                    if ($cohorteIds->isNotEmpty()) {
                        $userId = auth()->id();

                        // Apagar cohortes dependientes
                        DB::table('cohorte')
                            ->whereIn('id_coh', $cohorteIds)
                            ->update([
                                'status_id'  => 2,
                                'status'     => false,
                                'updated_id' => $userId,
                                'updated_at' => now(),
                            ]);

                        // Apagar cohortes por sede dependientes
                        DB::table('cohorte_sede')
                            ->whereIn('coh_id', $cohorteIds)
                            ->update([
                                'status_id'  => 2,
                                'status'     => false,
                                'updated_id' => $userId,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            return $estatus;
        });
    }

    /**
     * Retorna únicamente el id_ano, nombre y fechas de los registros activos para alimentar selects y restringir fechas hijas.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function obtenerActivosParaSelect(): Collection
    {
        return DB::table('ano_academico')
            ->select(['id_ano', 'nombre_ano', 'fecha_inicio_ano', 'fecha_fin_ano'])
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('nombre_ano', 'desc')
            ->get();
    }
}

