<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Services\Reportes\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controlador para la emisión y descarga de reportes oficiales en PDF
 * Utiliza PdfGeneratorService con Dompdf y plantillas Blade institucionales.
 */
class PdfReporteController extends Controller
{
    protected PdfGeneratorService $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * 1. Exportar Listado de Malla Curricular en PDF
     */
    public function exportarMallaPdf(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $malla = MallaCurricular::with(['pnf'])->find($mallaId) 
            ?? MallaCurricular::with(['pnf'])->where('status_id', 1)->first();

        if (!$malla) {
            abort(404, 'No se encontró la malla curricular seleccionada.');
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])
        ->where('mac_id', $malla->id_mac)
        ->where('status_id', 1)
        ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Trayecto Único') as $trayecto => $items) {
            $bloques[$trayecto] = $items->map(function ($item) {
                return [
                    'codigo'      => $item->unidadCurricular->codigo_ucu ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_ucu ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => '',
                ];
            });
        }

        $data = [
            'titulo'        => 'Listado Oficial de Malla Curricular',
            'codigoDoc'     => 'SOGAC-CUR-01',
            'programa'      => $malla->pnf->nombre_pnf ?? 'PNF General',
            'version'       => $malla->version_pensum_mac ?? 'Vigente',
            'gaceta'        => $malla->pnf->codigo_pnf ?? 'Oficial',
            'requisitosTsu' => "{$malla->creditos_minimos_tsu_mac} UC / {$malla->horas_minimas_tsu_mac} Horas",
            'requisitosIng' => "{$malla->creditos_minimos_ing_mac} UC / {$malla->horas_minimas_ing_mac} Horas",
            'bloques'       => $bloques,
        ];

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Malla_Curricular_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 2. Exportar Listado de Malla con Prelaciones en PDF
     */
    public function exportarMallaPrelacionPdf(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $malla = MallaCurricular::with(['pnf'])->find($mallaId) 
            ?? MallaCurricular::with(['pnf'])->where('status_id', 1)->first();

        if (!$malla) {
            abort(404, 'No se encontró la malla curricular seleccionada.');
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.prelacionesOrigen.unidadCurricularRequisito',
            'trayecto',
            'ejeCurricular',
        ])
        ->where('mac_id', $malla->id_mac)
        ->where('status_id', 1)
        ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Trayecto Único') as $trayecto => $items) {
            $bloques[$trayecto] = $items->map(function ($item) {
                $prelacionesNombres = [];
                if ($item->unidadCurricular && $item->unidadCurricular->prelacionesOrigen) {
                    foreach ($item->unidadCurricular->prelacionesOrigen as $prel) {
                        $prelacionesNombres[] = $prel->unidadCurricularRequisito->codigo_ucu ?? 'Requisito';
                    }
                }

                return [
                    'codigo'      => $item->unidadCurricular->codigo_ucu ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_ucu ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => !empty($prelacionesNombres) ? implode(', ', $prelacionesNombres) : 'Sin prelación',
                ];
            });
        }

        $data = [
            'titulo'        => 'Malla Curricular con Matriz de Prelaciones',
            'codigoDoc'     => 'SOGAC-PREL-02',
            'programa'      => $malla->pnf->nombre_pnf ?? 'PNF General',
            'version'       => $malla->version_pensum_mac ?? 'Vigente',
            'gaceta'        => $malla->pnf->codigo_pnf ?? 'Oficial',
            'requisitosTsu' => "{$malla->creditos_minimos_tsu_mac} UC / {$malla->horas_minimas_tsu_mac} Horas",
            'requisitosIng' => "{$malla->creditos_minimos_ing_mac} UC / {$malla->horas_minimas_ing_mac} Horas",
            'bloques'       => $bloques,
        ];

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Malla_Prelaciones_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 3. Exportar Relación de Unidades Curriculares por Malla
     */
    public function exportarRelacionUnidadesPdf(Request $request): Response
    {
        $mallaId = $request->input('malla_id');

        $malla = MallaCurricular::with(['pnf'])->find($mallaId) 
            ?? MallaCurricular::with(['pnf'])->where('status_id', 1)->first();

        if (!$malla) {
            abort(404, 'No se encontró la malla curricular seleccionada.');
        }

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])
        ->where('mac_id', $malla->id_mac)
        ->where('status_id', 1)
        ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn($d) => $d->ejeCurricular ? $d->ejeCurricular->nombre_eje : 'Eje General') as $eje => $items) {
            $bloques[$eje] = $items->map(function ($item) {
                return [
                    'codigo'      => $item->unidadCurricular->codigo_ucu ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_ucu ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => $item->trayecto->nombre_tra ?? 'N/A',
                ];
            });
        }

        $data = [
            'titulo'        => 'Relación de Unidades Curriculares por Ejes',
            'codigoDoc'     => 'SOGAC-EJE-03',
            'programa'      => $malla->pnf->nombre_pnf ?? 'PNF General',
            'version'       => $malla->version_pensum_mac ?? 'Vigente',
            'gaceta'        => $malla->pnf->codigo_pnf ?? 'Oficial',
            'requisitosTsu' => "{$malla->creditos_minimos_tsu_mac} UC",
            'requisitosIng' => "{$malla->creditos_minimos_ing_mac} UC",
            'bloques'       => $bloques,
        ];

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Relacion_Unidades_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 4. Exportar Relación de Unidades General en PDF
     */
    public function exportarRelacionUnidadesGeneralPdf(Request $request): Response
    {
        $unidades = UnidadCurricular::with(['tipoUnidadCurricular'])
            ->where('status_id', 1)
            ->orderBy('nombre_ucu')
            ->get();

        $bloques = [
            'Catálogo General de Unidades Curriculares' => $unidades->map(function ($item) {
                return [
                    'codigo'      => $item->codigo_ucu ?? 'N/A',
                    'nombre'      => $item->nombre_ucu ?? 'Sin nombre',
                    'htea'        => $item->htea_ucu ?? 0,
                    'htei'        => $item->htei_ucu ?? 0,
                    'thte'        => $item->thte_ucu ?? 0,
                    'uc'          => $item->unidades_credito_ucu ?? 0,
                    'prelaciones' => $item->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                ];
            })
        ];

        $data = [
            'titulo'        => 'Inventario General de Unidades Curriculares',
            'codigoDoc'     => 'SOGAC-INV-04',
            'programa'      => 'TODAS LAS CARRERAS / INSTITUCIONAL',
            'version'       => 'SOGAC ' . date('Y'),
            'gaceta'        => 'Repositorio Central UPTP',
            'requisitosTsu' => 'N/A',
            'requisitosIng' => 'N/A',
            'bloques'       => $bloques,
        ];

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Relacion_General_Unidades.pdf")
            ->download();
    }
}

