<?php

/**
 * Propósito: Controlador para la emisión y descarga de reportes curriculares oficiales en PDF con Dompdf.
 * Historial: Actualizado para consultar `programa`, `regla_prelacion` (`prelacionesExigidas.unidadOrigen`) y las columnas `codigo_unc`, `nombre_unc`, `nombre_ecu`, `total_credito_*` / `total_horas_*` de sogac_mio.
 */

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\EstructuraAcademica\Maestros\MallaCurricular;
use App\Models\EstructuraAcademica\Maestros\MallaCurricularDetalle;
use App\Models\EstructuraAcademica\Maestros\UnidadCurricular;
use App\Services\Reportes\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PdfReporteController extends Controller
{
    protected PdfGeneratorService $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Resuelve la malla solicitada o la primera activa cargando su programa asociado.
     */
    private function obtenerMallaConPrograma(mixed $mallaId): MallaCurricular
    {
        $malla = MallaCurricular::with(['programa'])->find($mallaId)
            ?? MallaCurricular::with(['programa'])->where('status_id', 1)->first();

        if (!$malla) {
            abort(404, 'No se encontró la malla curricular seleccionada.');
        }

        return $malla;
    }

    /**
     * Extrae la cabecera institucional común de la malla para las plantillas PDF.
     */
    private function construirCabeceraMalla(MallaCurricular $malla): array
    {
        $programa = $malla->programa;

        return [
            'programa' => $programa->nombre_prg ?? 'Programa General',
            'version'  => $malla->version_pensum_mac ?? 'Vigente',
            'gaceta'   => $malla->codigo_gaceta_mac ?? ($programa->codigo_prg ?? $programa->codigo_ministerio_prg ?? 'Oficial'),
        ];
    }

    /**
     * 1. Exportar Listado de Malla Curricular en PDF.
     */
    public function exportarMallaPdf(Request $request): Response
    {
        $malla = $this->obtenerMallaConPrograma($request->input('malla_id'));

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])
            ->where('mac_id', $malla->id_mac)
            ->where('status_id', 1)
            ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn ($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Trayecto Único') as $trayecto => $items) {
            $bloques[$trayecto] = $items->map(function ($item) {
                return [
                    'codigo'      => $item->unidadCurricular->codigo_unc ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_unc ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => '',
                ];
            });
        }

        $data = array_merge($this->construirCabeceraMalla($malla), [
            'titulo'        => 'Listado Oficial de Malla Curricular',
            'codigoDoc'     => 'SOGAC-CUR-01',
            'requisitosTsu' => "{$malla->total_credito_tsu_mac} UC / {$malla->total_horas_tsu_mac} Horas",
            'requisitosIng' => "{$malla->total_credito_ing_mac} UC / {$malla->total_horas_ing_mac} Horas",
            'bloques'       => $bloques,
        ]);

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Malla_Curricular_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 2. Exportar Listado de Malla con Matriz de Prelaciones en PDF.
     */
    public function exportarMallaPrelacionPdf(Request $request): Response
    {
        $malla = $this->obtenerMallaConPrograma($request->input('malla_id'));

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.prelacionesExigidas.unidadOrigen',
            'trayecto',
            'ejeCurricular',
        ])
            ->where('mac_id', $malla->id_mac)
            ->where('status_id', 1)
            ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn ($d) => $d->trayecto ? $d->trayecto->nombre_tra : 'Trayecto Único') as $trayecto => $items) {
            $bloques[$trayecto] = $items->map(function ($item) {
                $prelacionesNombres = [];
                if ($item->unidadCurricular && $item->unidadCurricular->prelacionesExigidas) {
                    foreach ($item->unidadCurricular->prelacionesExigidas as $prel) {
                        $codigo = $prel->unidadOrigen->codigo_unc ?? $prel->unidadOrigen->nombre_unc ?? 'Requisito';
                        $prelacionesNombres[] = "{$codigo} ({$prel->tipo_requisito_rep})";
                    }
                }

                return [
                    'codigo'      => $item->unidadCurricular->codigo_unc ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_unc ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => !empty($prelacionesNombres) ? implode(', ', $prelacionesNombres) : 'Sin prelación',
                ];
            });
        }

        $data = array_merge($this->construirCabeceraMalla($malla), [
            'titulo'        => 'Malla Curricular con Matriz de Prelaciones',
            'codigoDoc'     => 'SOGAC-PREL-02',
            'requisitosTsu' => "{$malla->total_credito_tsu_mac} UC / {$malla->total_horas_tsu_mac} Horas",
            'requisitosIng' => "{$malla->total_credito_ing_mac} UC / {$malla->total_horas_ing_mac} Horas",
            'bloques'       => $bloques,
        ]);

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Malla_Prelaciones_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 3. Exportar Relación de Unidades Curriculares por Ejes en PDF.
     */
    public function exportarRelacionUnidadesPdf(Request $request): Response
    {
        $malla = $this->obtenerMallaConPrograma($request->input('malla_id'));

        $detalles = MallaCurricularDetalle::with([
            'unidadCurricular.tipoUnidadCurricular',
            'trayecto',
            'ejeCurricular',
        ])
            ->where('mac_id', $malla->id_mac)
            ->where('status_id', 1)
            ->get();

        $bloques = [];
        foreach ($detalles->groupBy(fn ($d) => $d->ejeCurricular ? $d->ejeCurricular->nombre_ecu : 'Eje General') as $eje => $items) {
            $bloques[$eje] = $items->map(function ($item) {
                return [
                    'codigo'      => $item->unidadCurricular->codigo_unc ?? 'N/A',
                    'nombre'      => $item->unidadCurricular->nombre_unc ?? 'Sin nombre',
                    'htea'        => $item->htea_mcd ?? 0,
                    'htei'        => $item->htei_mcd ?? 0,
                    'thte'        => $item->thte_mcd ?? 0,
                    'uc'          => $item->unidades_credito_mcd ?? 0,
                    'prelaciones' => $item->trayecto->nombre_tra ?? 'N/A',
                ];
            });
        }

        $data = array_merge($this->construirCabeceraMalla($malla), [
            'titulo'        => 'Relación de Unidades Curriculares por Ejes',
            'codigoDoc'     => 'SOGAC-EJE-03',
            'requisitosTsu' => "{$malla->total_credito_tsu_mac} UC",
            'requisitosIng' => "{$malla->total_credito_ing_mac} UC",
            'bloques'       => $bloques,
        ]);

        return $this->pdfService
            ->make('reportes.pdf.plantilla_base', $data, "Relacion_Unidades_{$malla->id_mac}.pdf")
            ->download();
    }

    /**
     * 4. Exportar Inventario General de Unidades Curriculares en PDF.
     */
    public function exportarRelacionUnidadesGeneralPdf(Request $request): Response
    {
        $unidades = UnidadCurricular::with(['tipoUnidadCurricular', 'detallesMalla'])
            ->where('status_id', 1)
            ->orderBy('nombre_unc')
            ->get();

        $bloques = [
            'Catálogo General de Unidades Curriculares' => $unidades->map(function ($item) {
                $detalle = $item->detallesMalla->first();

                return [
                    'codigo'      => $item->codigo_unc ?? 'N/A',
                    'nombre'      => $item->nombre_unc ?? 'Sin nombre',
                    'htea'        => $detalle->htea_mcd ?? 0,
                    'htei'        => $detalle->htei_mcd ?? 0,
                    'thte'        => $detalle->thte_mcd ?? 0,
                    'uc'          => $detalle->unidades_credito_mcd ?? 0,
                    'prelaciones' => $item->tipoUnidadCurricular->nombre_tuc ?? 'Regular',
                ];
            }),
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
            ->make('reportes.pdf.plantilla_base', $data, 'Relacion_General_Unidades.pdf')
            ->download();
    }
}
