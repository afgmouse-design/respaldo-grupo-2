<?php

namespace App\Models\ProgramacionAcademica\Maestros;

use App\Models\Concerns\RegistraEnBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Modelo: Cohorte (tabla `cohorte`).
 *
 * Propósito: Agrupación temporal de estudiantes de nuevo ingreso vinculada a un Lapso Académico.
 * Baja lógica mediante `status` (bool) y `status_id` (1 activo, 2 inactivo).
 *
 * Actualización 2026-10-21 (Regla de Negocio: Única Cohorte Activa y Desactivación en Cascada):
 * Se incorporó la validación en `alternarEstatus()` para impedir activar más de una cohorte
 * simultáneamente en el sistema y verificar que el Lapso Académico padre se encuentre activo.
 * Se implementó la desactivación en cascada sobre `cohorte_sede` cuando una cohorte pasa a inactiva.
 * Se robusteció `obtenerPaginados()` para soportar el TabFilter ('all', '0', '').
 *
 * Actualización 2026-10-21 (Corrección Filtro Inicial y Visualización de Todas las Cohortes):
 * Se ajustó `obtenerPaginados()` para que por defecto ('all' o sin filtro explícito) no restrinja
 * la consulta únicamente a cohortes activas, evitando mostrar una lista vacía cuando las cohortes
 * existentes están inactivas y permitiendo visualizar todas las cohortes registradas al ingresar
 * o al seleccionar la opción 'Todos'.
 *
 * Actualización 2026-10-21 (Soporte para Eliminación en Cascada y deleted_at en Fillable):
 * Se incorporó `deleted_at` en `$fillable` y `$casts`. Se filtró por `whereNull('deleted_at')`
 * en `alternarEstatus()` para la comprobación de cohorte activa y lapso activo.
 */
class Cohorte extends Model
{
    use RegistraEnBitacora;

    protected $table = 'cohorte';

    protected $primaryKey = 'id_coh';

    protected $moduloAuditoria = 'Cohorte';

    protected $fillable = [
        'nombre_coh',
        'fecha_inicio_coh',
        'laa_id',
        'status',
        'status_id',
        'created_id',
        'updated_id',
        'deleted_at',
    ];

    protected $casts = [
        'id_coh'           => 'integer',
        'fecha_inicio_coh' => 'date',
        'laa_id'           => 'integer',
        'status'           => 'boolean',
        'status_id'        => 'integer',
        'deleted_at'       => 'datetime',
    ];

    /**
     * Listado paginado con filtros (search, status, laa_id).
     * Por defecto ('all', null o vacío) muestra todas las cohortes registradas.
     * Expone `id` (alias de id_coh) y `nombre_la`.
     */
    public static function obtenerPaginados(array $filtros = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $filtros['search'] ?? null;
        $status = $filtros['status'] ?? 'all';
        $laaId  = $filtros['laa_id'] ?? null;

        $query = DB::table('cohorte')
            ->leftJoin('lapso_academico', 'lapso_academico.id_la', '=', 'cohorte.laa_id')
            ->select('cohorte.*', 'cohorte.id_coh as id', 'lapso_academico.nombre_la')
            ->whereNull('cohorte.deleted_at');

        // Filtrado dinámico por estatus (TabFilter: 'all', 'true', 'false'):
        if ($status === 'true' || $status === '1' || $status === 1 || $status === true) {
            $query->where(function ($w) {
                $w->where('cohorte.status', true)
                  ->orWhere('cohorte.status_id', 1);
            });
        } elseif ($status === 'false' || $status === '0' || $status === 0 || $status === false) {
            $query->where(function ($w) {
                $w->where('cohorte.status', false)
                  ->orWhere('cohorte.status_id', 2);
            });
        }
        // Si $status es 'all', null o vacío, no se aplica filtro para listar todas las cohortes

        if (!empty($laaId) && $laaId !== 'all') {
            $query->where('cohorte.laa_id', $laaId);
        }

        if (!empty($search)) {
            $query->where('cohorte.nombre_coh', 'ilike', "%{$search}%");
        }

        return $query->orderBy('cohorte.id_coh', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Baja/alta lógica: fija status_id (1 = activo, 2 = inactivo) y sincroniza `status`.
     * Aplica la regla "Única Cohorte Activa", protección ascendente y desactivación en cascada.
     *
     * @throws \DomainException
     */
    public static function alternarEstatus(int $id, int $estado = 2, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($id, $estado, $userId) {
            $registro = static::query()->where('id_coh', $id)->firstOrFail();
            $usuarioId = $userId ?? auth()->id();

            if ($estado === 1) {
                // Protección Ascendente: No activar si el Lapso Padre está inactivo
                if (!empty($registro->laa_id)) {
                    $lapso = DB::table('lapso_academico')->where('id_la', $registro->laa_id)->whereNull('deleted_at')->first();
                    if (!$lapso || !$lapso->estado_la) {
                        throw new \DomainException("No se puede activar la cohorte porque el Lapso Académico al que pertenece se encuentra inactivo.");
                    }
                }

                // Regla de Negocio: Única Cohorte Activa
                $otraActiva = static::query()
                    ->where('id_coh', '!=', $id)
                    ->where('status_id', 1)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($otraActiva) {
                    throw new \DomainException("Ya existe otra Cohorte activa en el sistema. Desactive la cohorte en curso antes de activar otra.");
                }
            }

            // 1. Actualizar la Cohorte
            $actualizado = $registro->update([
                'status_id'  => $estado,
                'status'     => $estado === 1,
                'updated_id' => $usuarioId,
            ]);

            // 2. Desactivación en cascada: Si se desactiva la cohorte, desactivar cohorte_sede dependientes
            if ($estado === 2) {
                DB::table('cohorte_sede')
                    ->where('coh_id', $id)
                    ->update([
                        'status_id'  => 2,
                        'status'     => false,
                        'updated_id' => $usuarioId,
                        'updated_at' => now(),
                    ]);
            }

            return $actualizado;
        });
    }
}

