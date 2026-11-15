<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Lapso Académico (tabla `lapso_academico`).
 *
 * Entidad Hija: Depende de un Año Académico padre (`ano_id` -> `ano_academico.id_ano`).
 *
 * Actualización 2026-10-21 (Soporte status_id y corrección de filtrado reactivo de estatus):
 * Se incorporaron `status_id` y `status` al arreglo `$fillable` y `$casts` para permitir
 * la persistencia nativa con Eloquent (`create()` y `update()`). Se corrigió `obtenerPaginados()`
 * agregando el parámetro `$estadoLa` para filtrar dinámicamente según la pestaña seleccionada
 * ('all' -> todos, 'true' -> activos, 'false' -> inactivos) eliminando la restricción fija
 * de `status_id = 1` que impedía visualizar los registros guardados como inactivos.
 *
 * Actualización 2026-10-21 (Desactivación en Cascada: Lapso -> Cohorte -> CohorteSede):
 * Se incorporó en `alternarEstatus()` la propagación en cascada para que al desactivar
 * un lapso académico se desactiven automáticamente todas sus cohortes asociadas y las
 * cohortes por sede dependientes de las mismas.
 *
 * Actualización 2026-10-21 (Fechas del Año Padre en obtenerPorId):
 * Se agregaron `fecha_inicio_ano` y `fecha_fin_ano` al join de `obtenerPorId()` para que la
 * vista de edición disponga del rango de vigencia del año académico padre para restringir fechas.
 *
 * Actualización 2026-10-21 (Soporte para Eliminación en Cascada y Filtrado deleted_at):
 * Se incorporó `deleted_at` en `$fillable` y `$casts`. Se filtraron los registros en `obtenerPaginados()`,
 * `obtenerPorId()` y `alternarEstatus()` asegurando que no se listen lapsos eliminados lógicamente
 * ni de años académicos eliminados, y que no colisionen con la regla de único lapso activo.
 */
class LapsoAcademico extends Model
{
    /** Nombre explícito de la tabla en singular */
    protected $table = 'lapso_academico';

    /** Llave primaria personalizada */
    protected $primaryKey = 'id_la';

    /** Desactivación de timestamps automáticos de Eloquent */
    public $timestamps = false;

    /** Campos asignables masivamente */
    protected $fillable = [
        'ano_id',
        'nombre_la',
        'fecha_inicio_la',
        'fecha_fin_la',
        'estado_la',
        'status_id',
        'status',
        'deleted_at',
    ];

    /** Conversión de tipos */
    protected $casts = [
        'id_la'           => 'integer',
        'ano_id'          => 'integer',
        'fecha_inicio_la' => 'date',
        'fecha_fin_la'    => 'date',
        'estado_la'       => 'boolean',
        'status_id'       => 'integer',
        'status'          => 'boolean',
        'deleted_at'       => 'datetime',
    ];

    // =========================================================================
    // MÉTODOS ESTÁTICOS - QUERY BUILDER PURO (DB::table)
    // =========================================================================

    /**
     * Obtiene los lapsos académicos paginados con leftJoin a ano_academico.
     * Permite filtrar dinámicamente por término de búsqueda, año académico y estatus (activo/inactivo/todos).
     *
     * @param string|null $busqueda
     * @param int $porPagina
     * @param int|string|null $anoId
     * @param string|bool|int|null $estadoLa
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function obtenerPaginados($busqueda = null, int $porPagina = 10, $anoId = null, $estadoLa = null)
    {
        $query = DB::table('lapso_academico')
            ->leftJoin('ano_academico', 'lapso_academico.ano_id', '=', 'ano_academico.id_ano')
            ->select([
                'lapso_academico.id_la',
                'lapso_academico.ano_id',
                'lapso_academico.nombre_la',
                'lapso_academico.fecha_inicio_la',
                'lapso_academico.fecha_fin_la',
                'lapso_academico.estado_la',
                'lapso_academico.status_id',
                'lapso_academico.status',
                'ano_academico.nombre_ano',
                'ano_academico.status as ano_status',
            ])
            ->whereNull('lapso_academico.deleted_at')
            ->where(function ($q) {
                $q->whereNull('ano_academico.deleted_at')
                  ->orWhereNull('lapso_academico.ano_id');
            });

        // Filtrado dinámico por estatus (TabFilter: 'all', 'true', 'false'):
        if ($estadoLa === 'true' || $estadoLa === '1' || $estadoLa === 1 || $estadoLa === true) {
            $query->where(function ($q) {
                $q->where('lapso_academico.status_id', 1)
                  ->orWhere('lapso_academico.estado_la', true);
            });
        } elseif ($estadoLa === 'false' || $estadoLa === '0' || $estadoLa === 0 || $estadoLa === false) {
            $query->where(function ($q) {
                $q->where('lapso_academico.status_id', 2)
                  ->orWhere('lapso_academico.estado_la', false);
            });
        }

        if (!empty($busqueda)) {
            $query->where('lapso_academico.nombre_la', 'like', "%{$busqueda}%");
        }

        if (!empty($anoId) && $anoId !== 'all') {
            $query->where('lapso_academico.ano_id', $anoId);
        }

        return $query->orderBy('lapso_academico.fecha_inicio_la', 'desc')
            ->orderBy('lapso_academico.id_la', 'desc')
            ->paginate($porPagina);
    }

    /**
     * Registra un nuevo lapso académico mediante inserción directa.
     *
     * @param array $datos
     * @return object|null
     */
    public static function registrarLapso(array $datos)
    {
        $registro = [
            'ano_id'          => $datos['ano_id'] ?? null,
            'nombre_la'       => $datos['nombre_la'],
            'fecha_inicio_la' => $datos['fecha_inicio_la'],
            'fecha_fin_la'    => $datos['fecha_fin_la'],
            'estado_la'       => isset($datos['estado_la']) ? (bool) $datos['estado_la'] : true,
        ];

        $id = DB::table('lapso_academico')->insertGetId($registro, 'id_la');

        return self::obtenerPorId($id);
    }

    /**
     * Actualiza un lapso académico existente.
     *
     * @param int|string $id
     * @param array $datos
     * @return object|null
     */
    public static function actualizarLapso($id, array $datos)
    {
        $campos = [];

        if (array_key_exists('ano_id', $datos)) {
            $campos['ano_id'] = $datos['ano_id'];
        }
        if (array_key_exists('nombre_la', $datos)) {
            $campos['nombre_la'] = $datos['nombre_la'];
        }
        if (array_key_exists('fecha_inicio_la', $datos)) {
            $campos['fecha_inicio_la'] = $datos['fecha_inicio_la'];
        }
        if (array_key_exists('fecha_fin_la', $datos)) {
            $campos['fecha_fin_la'] = $datos['fecha_fin_la'];
        }
        if (array_key_exists('estado_la', $datos)) {
            $campos['estado_la'] = (bool) $datos['estado_la'];
        }

        if (!empty($campos)) {
            DB::table('lapso_academico')
                ->where('id_la', $id)
                ->update($campos);
        }

        return self::obtenerPorId($id);
    }

    /**
     * Obtiene un lapso académico específico por su identificador con datos del año padre.
     *
     * @param int|string $id
     * @return object|null
     */
    public static function obtenerPorId($id)
    {
        return DB::table('lapso_academico')
            ->leftJoin('ano_academico', 'lapso_academico.ano_id', '=', 'ano_academico.id_ano')
            ->select([
                'lapso_academico.*',
                'ano_academico.nombre_ano',
                'ano_academico.fecha_inicio_ano',
                'ano_academico.fecha_fin_ano',
                'ano_academico.status as ano_status',
            ])
            ->where('lapso_academico.id_la', $id)
            ->whereNull('lapso_academico.deleted_at')
            ->first();
    }

    /**
     * Alterna el estado_la entre activo (true/1) e inactivo (false/0).
     * Aplica la regla de Protección Ascendente al ACTIVAR, regla de Único Lapso Activo,
     * y Desactivación en Cascada (Lapso -> Cohortes -> CohorteSede) al DESACTIVAR.
     *
     * @param int|string $id
     * @param bool|null $nuevoEstatus
     * @return bool
     * @throws \DomainException
     */
    public static function alternarEstatus($id, $nuevoEstatus = null)
    {
        $lapso = DB::table('lapso_academico')->where('id_la', $id)->whereNull('deleted_at')->first();

        if (!$lapso) {
            throw new \DomainException("El lapso académico solicitado no existe.");
        }

        if (is_null($nuevoEstatus)) {
            $nuevoEstatus = ($lapso->status_id === 1 || $lapso->estado_la) ? 2 : 1;
        }

        $estatusBool = ($nuevoEstatus === 1 || $nuevoEstatus === true || $nuevoEstatus === '1');
        $statusId = $estatusBool ? 1 : 2;

        // Reglas de Negocio al ACTIVAR
        if ($estatusBool) {
            // 1. Protección Ascendente: No activar si el Año Padre está inactivo
            if (!empty($lapso->ano_id)) {
                $ano = DB::table('ano_academico')->where('id_ano', $lapso->ano_id)->whereNull('deleted_at')->first();
                if (!$ano || !$ano->status) {
                    throw new \DomainException("No se puede activar el lapso porque el Año Académico al que pertenece se encuentra inactivo o cerrado.");
                }
            }

            // 2. Único Lapso Activo: Verificar que no haya otro activo en el sistema
            $otroActivo = DB::table('lapso_academico')
                ->where('id_la', '!=', $id)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('status_id', 1)->orWhere('estado_la', true);
                })
                ->exists();

            if ($otroActivo) {
                throw new \DomainException("Ya existe otro Lapso Académico activo en el sistema. Solo puede haber un lapso activo a la vez.");
            }
        }

        return DB::transaction(function () use ($id, $statusId, $estatusBool) {
            // 1. Actualizar Lapso
            DB::table('lapso_academico')
                ->where('id_la', $id)
                ->update([
                    'status_id' => $statusId,
                    'status'    => $estatusBool,
                    'estado_la' => $estatusBool,
                ]);

            // 2. Desactivación en Cascada al DESACTIVAR (Lapso -> Cohortes -> CohorteSede)
            if (!$estatusBool) {
                $cohorteIds = DB::table('cohorte')
                    ->where('laa_id', $id)
                    ->pluck('id_coh');

                if ($cohorteIds->isNotEmpty()) {
                    $userId = auth()->id();

                    // Apagar Cohortes
                    DB::table('cohorte')
                        ->whereIn('id_coh', $cohorteIds)
                        ->update([
                            'status_id'  => 2,
                            'status'     => false,
                            'updated_id' => $userId,
                            'updated_at' => now(),
                        ]);

                    // Apagar Cohortes por Sede
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

            return $estatusBool;
        });
    }
}
