<?php

/**
 * Propósito: Controlador de Reportes de Currículo y Estructura Académica (vistas web Inertia y exportación CSV para Excel).
 * Historial: Actualizado para consultar `Programa` (`programa`), `ReglaPrelacion` (`prelacionesExigidas.unidadOrigen` / `esPrerrequisitoDe.unidadDestino`) y los totales oficiales (`total_credito_*`, `total_horas_*`).
 */

namespace App\Http\Controllers\EstructuraAcademica;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\TipoUnidadCurricular;
use App\Models\EstructuraAcademica\Maestros\Trayecto;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesCurriculoController extends Controller
{
    /**
     * Resuelve la malla solicitada o la primera activa cargando su programa.
     */
    private function obtenerMallaSeleccionada(mixed $mallaId): ?MallaCurricular
    {
        return $mallaId
            ? MallaCurricular::with(['programa'])->find($mallaId)
            : MallaCurricular::with(['programa'])->where('status_id', 1)->orderBy('nombre_mac')->first();
    }

    /**
     * Genera la respuesta CSV UTF-8 con BOM compartida entre todos los exportadores tabulares.
     */
    private function descargarCsv(string $nombreArchivo, callable $escritor): StreamedResponse
    {
        return response()->streamDownload(function () use ($escritor) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $escritor($handle);
            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * 1. Listado De Malla Curricular.
     */
    public function listadoMalla(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $mallas = MallaCurricular::with('programa')
            ->where('status_id', 1)
            ->orderBy('nombre_mac')
            ->get();

        $mallaSeleccionada = $mallaId
            ? MallaCurricular::with(['programa'])->find($mallaId)
            : $mallas->first();

        $detallesPorTrayecto = [];
        $totales = [
            'unidades' => 0,
            'creditos' => 0,
            'htea'     => 0,
            'htei'     => 0,
            'thte'     => 0,
        ];

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
            $totales['htea'] = $detalles->sum('htea_mcd');
            $totales['htei'] = $detalles->sum('htei_mcd');
            $totales['thte'] = $detalles->sum('thte_mcd');

            $detallesPorTrayecto = $detalles->groupBy(
                fn ($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Sin Trayecto Asignado'
            );
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
     * 2. Listado De Malla Con Prelación sobre `regla_prelacion`.
     */
    public function listadoMallaConPrelacion(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $mallas = MallaCurricular::with('programa')
            ->where('status_id', 1)
            ->orderBy('nombre_mac')
            ->get();

        $mallaSeleccionada = $mallaId
            ? MallaCurricular::with(['programa'])->find($mallaId)
            : $mallas->first();

        $detallesPorTrayecto = [];

        if ($mallaSeleccionada) {
            $detalles = MallaCurricularDetalle::with([
                'unidadCurricular.tipoUnidadCurricular',
                'unidadCurricular.prelacionesExigidas.unidadOrigen',
                'unidadCurricular.esPrerrequisitoDe.unidadDestino',
                'trayecto',
                'ejeCurricular',
            ])
                ->where('mac_id', $mallaSeleccionada->id_mac)
                ->where('status_id', 1)
                ->get();

            $detallesPorTrayecto = $detalles->groupBy(
                fn ($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Sin Trayecto Asignado'
            );
        }

        return Inertia::render('EstructuraAcademica/Reportes/ListadoMallaConPrelacion', [
            'mallas'              => $mallas,
            'mallaSeleccionada'   => $mallaSeleccionada,
            'detallesPorTrayecto' => $detallesPorTrayecto,
            'filters'             => $request->only(['malla_id']),
        ]);
    }

    /**
     * 3. Relación De Unidades (Por Malla / Programa con desglose de horas y créditos).
     */
    public function relacionUnidades(Request $request): Response
    {
        $mallaId = $request->input('malla_id');
        $search = $request->input('search');
        $traId = $request->input('tra_id');

        $mallas = MallaCurricular::with('programa')->where('status_id', 1)->orderBy('nombre_mac')->get();
        $trayectos = Trayecto::where('status_id', 1)->orderBy('numero_orden_tra')->get();

        $query = MallaCurricularDetalle::with([
            'mallaCurricular.programa',
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
            ->with(['tipoUnidadCurricular', 'detallesMalla.mallaCurricular.programa'])
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
     * Exporta el Listado de Malla a Excel (CSV con BOM UTF-8).
     */
    public function exportarMallaExcel(Request $request)
    {
        $malla = $this->obtenerMallaSeleccionada($request->input('malla_id'));
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

        return $this->descargarCsv($nombreArchivo, function ($handle) use ($malla, $detalles) {
            fputcsv($handle, ['UNIVERSIDAD POLITÉCNICA TERRITORIAL DEL ESTADO PORTUGUESA - SOGAC'], ';');
            fputcsv($handle, ['PENSUM / MALLA CURRICULAR:', $malla->nombre_mac, 'PROGRAMA:', $malla->programa?->nombre_prg ?? 'N/A'], ';');
            fputcsv($handle, ['VERSIÓN:', $malla->version_pensum_mac ?? 'N/A', 'GACETA:', $malla->codigo_gaceta_mac ?? 'N/A'], ';');
            fputcsv($handle, ['REQUISITOS TSU:', "{$malla->total_credito_tsu_mac} UC / {$malla->total_horas_tsu_mac} Horas"], ';');
            fputcsv($handle, ['REQUISITOS INGENIERÍA:', "{$malla->total_credito_ing_mac} UC / {$malla->total_horas_ing_mac} Horas"], ';');
            fputcsv($handle, []);
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
        });
    }

    /**
     * Exporta el Listado de Malla con Prelaciones a Excel (`regla_prelacion`).
     */
    public function exportarMallaPrelacionExcel(Request $request)
    {
        $malla = $this->obtenerMallaSeleccionada($request->input('malla_id'));
        if (!$malla) {
            return redirect()->back()->withErrors(['error' => 'No se encontró la malla curricular especificada.']);
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'unidadCurricular.prelacionesExigidas.unidadOrigen',
            'trayecto',
        ])
            ->where('mac_id', $malla->id_mac)
            ->where('status_id', 1)
            ->orderBy('tra_id')
            ->get();

        $nombreArchivo = 'Prelaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $malla->nombre_mac) . '.csv';

        return $this->descargarCsv($nombreArchivo, function ($handle) use ($malla, $detalles) {
            fputcsv($handle, ['SOGAC - MATRIZ DE PRELACIONES CURRICULARES'], ';');
            fputcsv($handle, ['MALLA:', $malla->nombre_mac, 'PROGRAMA:', $malla->programa?->nombre_prg ?? 'N/A'], ';');
            fputcsv($handle, []);
            fputcsv($handle, ['Nro.', 'Trayecto', 'Código', 'Unidad Curricular', 'UC', 'Materias Requisito (Prelaciones)'], ';');

            foreach ($detalles as $idx => $d) {
                $prelaciones = $d->unidadCurricular?->prelacionesExigidas ?? collect();
                $prelacionesStr = $prelaciones->map(function ($p) {
                    $cod = $p->unidadOrigen?->codigo_unc ?? 'S/C';
                    $nom = $p->unidadOrigen?->nombre_unc ?? 'Unidad';

                    return "{$cod} - {$nom} ({$p->tipo_requisito_rep})";
                })->implode(' | ');

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
        });
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
            'mallaCurricular.programa',
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

        return $this->descargarCsv('Relacion_Unidades_Curriculares.csv', function ($handle) use ($detalles) {
            fputcsv($handle, ['SOGAC - RELACIÓN DE UNIDADES CURRICULARES'], ';');
            fputcsv($handle, []);
            fputcsv($handle, ['Nro.', 'Código', 'Unidad Curricular', 'Malla', 'Programa', 'Trayecto', 'Eje', 'UC', 'HTEA', 'HTEI', 'THTE', 'Semanas'], ';');

            foreach ($detalles as $idx => $d) {
                fputcsv($handle, [
                    $idx + 1,
                    $d->unidadCurricular?->codigo_unc ?? 'S/C',
                    $d->unidadCurricular?->nombre_unc ?? 'Sin Nombre',
                    $d->mallaCurricular?->nombre_mac ?? 'N/A',
                    $d->mallaCurricular?->programa?->nombre_prg ?? 'N/A',
                    $d->trayecto?->nombre_tra ?? 'Sin Trayecto',
                    $d->ejeCurricular?->nombre_ecu ?? 'General',
                    $d->unidades_credito_mcd,
                    $d->htea_mcd,
                    $d->htei_mcd,
                    $d->thte_mcd,
                    $d->duracion_semanas_mcd ?? 36,
                ], ';');
            }
        });
    }

    /**
     * Exporta el Catálogo General de Unidades a Excel.
     */
    public function exportarRelacionUnidadesGeneralExcel(Request $request)
    {
        $search = $request->input('search');
        $tucId = $request->input('tuc_id');

        $query = UnidadCurricular::query()
            ->with(['tipoUnidadCurricular', 'detallesMalla.mallaCurricular.programa'])
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

        return $this->descargarCsv('Catalogo_General_Unidades_Curriculares.csv', function ($handle) use ($unidades) {
            fputcsv($handle, ['SOGAC - CATÁLOGO INSTITUCIONAL DE UNIDADES CURRICULARES'], ';');
            fputcsv($handle, []);
            fputcsv($handle, ['Nro.', 'Código', 'Unidad Curricular', 'Tipo', 'Cantidad Mallas', 'Programas Asociados'], ';');

            foreach ($unidades as $idx => $u) {
                $programasStr = $u->detallesMalla
                    ->map(fn ($dm) => $dm->mallaCurricular?->programa?->nombre_prg)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                fputcsv($handle, [
                    $idx + 1,
                    $u->codigo_unc,
                    $u->nombre_unc,
                    $u->tipoUnidadCurricular?->nombre_tuc ?? 'Obligatoria',
                    $u->detalles_malla_count,
                    $programasStr ?: 'No vinculada',
                ], ';');
            }
        });
    }
}
