<?php

namespace App\Http\Controllers\EstructuraAcademica;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\EjeCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\Pnf;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador de Reportes de Currículo y Estructura Académica (SOGAC).
 *
 * Incluye:
 * 1. Listado De Malla
 * 2. Listado De Malla Con Prelación
 * 3. Relación De Unidades
 * 4. Relación De Unidades General
 */
class ReportesCurriculoController extends Controller
{
    /**
     * 1. Listado De Malla Curricular.
     */
    public function listadoMalla(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $mallas = MallaCurricular::with('pnf')
            ->where('status_id', 1)
            ->orderBy('nombre_mac')
            ->get();

        $mallaSeleccionada = null;
        $detallesPorTrayecto = [];
        $totales = [
            'unidades' => 0,
            'creditos' => 0,
            'htea'     => 0,
            'htei'     => 0,
            'thte'     => 0,
        ];

        if ($mallaId) {
            $mallaSeleccionada = MallaCurricular::with(['pnf'])->find($mallaId);
        } elseif ($mallas->isNotEmpty()) {
            $mallaSeleccionada = $mallas->first();
        }

        if ($mallaSeleccionada) {
            $detalles = MallaCurricularDetalle::with([
                'unidadCurricular.tipoUnidadCurricular',
                'trayecto',
                'ejeCurricular',
                'escalaMedicion',
            ])
            ->where('mac_id', $mallaSeleccionada->id_mac)
            ->where('status_id', 1)
            ->get();

            $totales['unidades'] = $detalles->count();
            $totales['creditos'] = $detalles->sum('unidades_credito_mcd');
            $totales['htea']     = $detalles->sum('htea_mcd');
            $totales['htei']     = $detalles->sum('htei_mcd');
            $totales['thte']     = $detalles->sum('thte_mcd');

            $detallesPorTrayecto = $detalles->groupBy(function ($d) {
                return $d->trayecto ? $d->trayecto->nombre_tra : 'Sin Trayecto Asignado';
            });
        }

        return Inertia::render('EstructuraAcademica/Reportes/ListadoMalla', [
            'mallas'              => $mallas,
            'mallaSeleccionada'   => $mallaSeleccionada,
            'detallesPorTrayecto' => $detallesPorTrayecto,
            'totales'             => $totales,
            'filters'             => $request->only(['malla_id']),
        ]);
    }

    /**
     * 2. Listado De Malla Con Prelación.
     */
    public function listadoMallaConPrelacion(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $mallas = MallaCurricular::with('pnf')
            ->where('status_id', 1)
            ->orderBy('nombre_mac')
            ->get();

        $mallaSeleccionada = null;
        $detallesPorTrayecto = [];

        if ($mallaId) {
            $mallaSeleccionada = MallaCurricular::with(['pnf'])->find($mallaId);
        } elseif ($mallas->isNotEmpty()) {
            $mallaSeleccionada = $mallas->first();
        }

        if ($mallaSeleccionada) {
            $detalles = MallaCurricularDetalle::with([
                'unidadCurricular.tipoUnidadCurricular',
                'trayecto',
                'ejeCurricular',
                'prelaciones.detallePrelacion.unidadCurricular',
                'esRequisitoDe.detalleMalla.unidadCurricular',
            ])
            ->where('mac_id', $mallaSeleccionada->id_mac)
            ->where('status_id', 1)
            ->get();

            $detallesPorTrayecto = $detalles->groupBy(function ($d) {
                return $d->trayecto ? $d->trayecto->nombre_tra : 'Sin Trayecto Asignado';
            });
        }

        return Inertia::render('EstructuraAcademica/Reportes/ListadoMallaConPrelacion', [
            'mallas'              => $mallas,
            'mallaSeleccionada'   => $mallaSeleccionada,
            'detallesPorTrayecto' => $detallesPorTrayecto,
            'filters'             => $request->only(['malla_id']),
        ]);
    }

    /**
     * 3. Relación De Unidades (Por Malla / PNF con desglose de horas y créditos).
     */
    public function relacionUnidades(Request $request): Response
    {
        $mallaId = $request->input('malla_id');
        $search = $request->input('search');
        $traId = $request->input('tra_id');

        $mallas = MallaCurricular::with('pnf')->where('status_id', 1)->orderBy('nombre_mac')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra')->get();

        $query = MallaCurricularDetalle::with([
            'mallaCurricular.pnf',
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
            'escalaMedicion',
        ])->where('status_id', 1);

        if ($mallaId && $mallaId !== 'all') {
            $query->where('mac_id', $mallaId);
        }

        if ($traId && $traId !== 'all') {
            $query->where('tra_id', $traId);
        }

        if ($search) {
            $query->whereHas('unidadCurricular', function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('codigo_unc', 'ilike', "%{$search}%");
            });
        }

        $detalles = $query->orderBy('mac_id')->orderBy('tra_id')->paginate(20)->withQueryString();

        return Inertia::render('EstructuraAcademica/Reportes/RelacionUnidades', [
            'detalles'  => $detalles,
            'mallas'    => $mallas,
            'trayectos' => $trayectos,
            'filters'   => $request->only(['malla_id', 'tra_id', 'search']),
        ]);
    }

    /**
     * 4. Relación De Unidades General (Catálogo maestro transversal de todas las asignaturas).
     */
    public function relacionUnidadesGeneral(Request $request): Response
    {
        $search = $request->input('search');
        $tucId = $request->input('tuc_id');

        $tipos = TipoUnidadCurricular::where('status_id', 1)->orderBy('nombre_tuc')->get();

        $query = UnidadCurricular::query()
            ->with(['tipoUnidadCurricular', 'detallesMalla.mallaCurricular.pnf'])
            ->withCount('detallesMalla')
            ->where('status_id', 1);

        if ($tucId && $tucId !== 'all') {
            $query->where('tuc_id', $tucId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('codigo_unc', 'ilike', "%{$search}%");
            });
        }

        $unidades = $query->orderBy('codigo_unc')->paginate(25)->withQueryString();

        return Inertia::render('EstructuraAcademica/Reportes/RelacionUnidadesGeneral', [
            'unidades' => $unidades,
            'tipos'    => $tipos,
            'filters'  => $request->only(['search', 'tuc_id']),
        ]);
    }

    /**
     * Exporta el Listado de Malla a Excel (CSV con formato UTF-8 y BOM para Excel).
     */
    public function exportarMallaExcel(Request $request)
    {
        $mallaId = $request->input('malla_id');
        $malla = MallaCurricular::with(['pnf'])->find($mallaId) ?? MallaCurricular::with(['pnf'])->where('status_id', 1)->first();

        if (!$malla) {
            return redirect()->back()->withErrors(['error' => 'No se encontró la malla curricular especificada.']);
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])
        ->where('mac_id', $malla->id_mac)
        ->where('status_id', 1)
        ->orderBy('tra_id')
        ->get();

        $nombreArchivo = 'Malla_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $malla->nombre_mac) . '.csv';

        return response()->streamDownload(function () use ($malla, $detalles) {
            $handle = fopen('php://output', 'w');
            // Byte Order Mark (BOM) para compatibilidad total con Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['UNIVERSIDAD POLITÉCNICA TERRITORIAL DEL ESTADO PORTUGUESA - SOGAC'], ';');
            fputcsv($handle, ['PENSUM / MALLA CURRICULAR:', $malla->nombre_mac, 'PNF:', $malla->pnf?->nombre_pnf ?? 'N/A'], ';');
            fputcsv($handle, ['VERSIÓN:', $malla->version_pensum_mac ?? 'N/A', 'GACETA:', $malla->codigo_gaceta_mac ?? 'N/A'], ';');
            fputcsv($handle, ['REQUISITOS TSU:', "{$malla->creditos_minimos_tsu_mac} UC / {$malla->horas_minimas_tsu_mac} Horas"], ';');
            fputcsv($handle, ['REQUISITOS INGENIERÍA:', "{$malla->creditos_minimos_ing_mac} UC / {$malla->horas_minimas_ing_mac} Horas"], ';');
            fputcsv($handle, []);

            // Encabezados de columnas
            fputcsv($handle, ['Nro.', 'Trayecto', 'Código', 'Unidad Curricular', 'Tipo', 'Eje Curricular', 'UC', 'HTEA', 'HTEI', 'THTE'], ';');

            foreach ($detalles as $idx => $d) {
                fputcsv($handle, [
                    $idx + 1,
                    $d->trayecto?->nombre_tra ?? 'Sin Trayecto',
                    $d->unidadCurricular?->codigo_unc ?? 'S/C',
                    $d->unidadCurricular?->nombre_unc ?? 'Sin Nombre',
                    $d->unidadCurricular?->tipoUnidadCurricular?->nombre_tuc ?? 'Obligatoria',
                    $d->ejeCurricular?->nombre_ecu ?? 'General',
                    $d->unidades_credito_mcd,
                    $d->htea_mcd,
                    $d->htei_mcd,
                    $d->thte_mcd,
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * Exporta el Listado de Malla con Prelaciones a Excel.
     */
    public function exportarMallaPrelacionExcel(Request $request)
    {
        $mallaId = $request->input('malla_id');
        $malla = MallaCurricular::with(['pnf'])->find($mallaId) ?? MallaCurricular::with(['pnf'])->where('status_id', 1)->first();

        if (!$malla) {
            return redirect()->back()->withErrors(['error' => 'No se encontró la malla curricular especificada.']);
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'prelaciones.detallePrelacion.unidadCurricular',
        ])
        ->where('mac_id', $malla->id_mac)
        ->where('status_id', 1)
        ->orderBy('tra_id')
        ->get();

        $nombreArchivo = 'Prelaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $malla->nombre_mac) . '.csv';

        return response()->streamDownload(function () use ($malla, $detalles) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['SOGAC - MATRIZ DE PRELACIONES CURRICULARES'], ';');
            fputcsv($handle, ['MALLA:', $malla->nombre_mac, 'PNF:', $malla->pnf?->nombre_pnf ?? 'N/A'], ';');
            fputcsv($handle, []);

            fputcsv($handle, ['Nro.', 'Trayecto', 'Código', 'Unidad Curricular', 'UC', 'Materias Requisito (Prelaciones)'], ';');

            foreach ($detalles as $idx => $d) {
                $prelacionesStr = $d->prelaciones->map(fn($p) => "{$p->detallePrelacion?->unidadCurricular?->codigo_unc} - {$p->detallePrelacion?->unidadCurricular?->nombre_unc}")->implode(' | ');
                if (empty($prelacionesStr)) {
                    $prelacionesStr = 'Sin Prelación (Libre Cursado)';
                }

                fputcsv($handle, [
                    $idx + 1,
                    $d->trayecto?->nombre_tra ?? 'Sin Trayecto',
                    $d->unidadCurricular?->codigo_unc ?? 'S/C',
                    $d->unidadCurricular?->nombre_unc ?? 'Sin Nombre',
                    $d->unidades_credito_mcd,
                    $prelacionesStr,
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * Exporta la Relación de Unidades a Excel.
     */
    public function exportarRelacionUnidadesExcel(Request $request)
    {
        $mallaId = $request->input('malla_id');
        $traId = $request->input('tra_id');
        $search = $request->input('search');

        $query = MallaCurricularDetalle::with([
            'mallaCurricular.pnf',
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])->where('status_id', 1);

        if ($mallaId && $mallaId !== 'all') {
            $query->where('mac_id', $mallaId);
        }
        if ($traId && $traId !== 'all') {
            $query->where('tra_id', $traId);
        }
        if ($search) {
            $query->whereHas('unidadCurricular', function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('codigo_unc', 'ilike', "%{$search}%");
            });
        }

        $detalles = $query->orderBy('mac_id')->orderBy('tra_id')->get();
        $nombreArchivo = 'Relacion_Unidades_Curriculares.csv';

        return response()->streamDownload(function () use ($detalles) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['SOGAC - RELACIÓN DE UNIDADES CURRICULARES'], ';');
            fputcsv($handle, []);
            fputcsv($handle, ['Nro.', 'Código', 'Unidad Curricular', 'Malla', 'PNF', 'Trayecto', 'Eje', 'UC', 'HTEA', 'HTEI', 'THTE', 'Semanas'], ';');

            foreach ($detalles as $idx => $d) {
                fputcsv($handle, [
                    $idx + 1,
                    $d->unidadCurricular?->codigo_unc ?? 'S/C',
                    $d->unidadCurricular?->nombre_unc ?? 'Sin Nombre',
                    $d->mallaCurricular?->nombre_mac ?? 'N/A',
                    $d->mallaCurricular?->pnf?->nombre_pnf ?? 'N/A',
                    $d->trayecto?->nombre_tra ?? 'Sin Trayecto',
                    $d->ejeCurricular?->nombre_ecu ?? 'General',
                    $d->unidades_credito_mcd,
                    $d->htea_mcd,
                    $d->htei_mcd,
                    $d->thte_mcd,
                    $d->duracion_semanas_mcd ?? 36,
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * Exporta el Catálogo General de Unidades a Excel.
     */
    public function exportarRelacionUnidadesGeneralExcel(Request $request)
    {
        $search = $request->input('search');
        $tucId = $request->input('tuc_id');

        $query = UnidadCurricular::query()
            ->with(['tipoUnidadCurricular', 'detallesMalla.mallaCurricular.pnf'])
            ->withCount('detallesMalla')
            ->where('status_id', 1);

        if ($tucId && $tucId !== 'all') {
            $query->where('tuc_id', $tucId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_unc', 'ilike', "%{$search}%")
                  ->orWhere('codigo_unc', 'ilike', "%{$search}%");
            });
        }

        $unidades = $query->orderBy('codigo_unc')->get();
        $nombreArchivo = 'Catalogo_General_Unidades_Curriculares.csv';

        return response()->streamDownload(function () use ($unidades) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['SOGAC - CATÁLOGO INSTITUCIONAL DE UNIDADES CURRICULARES'], ';');
            fputcsv($handle, []);
            fputcsv($handle, ['Nro.', 'Código', 'Unidad Curricular', 'Tipo', 'Cantidad Mallas', 'PNF Asociados'], ';');

            foreach ($unidades as $idx => $u) {
                $pnfsStr = $u->detallesMalla->map(fn($dm) => $dm->mallaCurricular?->pnf?->nombre_pnf)->filter()->unique()->implode(', ');

                fputcsv($handle, [
                    $idx + 1,
                    $u->codigo_unc,
                    $u->nombre_unc,
                    $u->tipoUnidadCurricular?->nombre_tuc ?? 'Obligatoria',
                    $u->detalles_malla_count,
                    $pnfsStr ?: 'No vinculada',
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }
}

