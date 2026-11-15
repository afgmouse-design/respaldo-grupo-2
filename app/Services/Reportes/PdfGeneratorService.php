<?php

namespace App\Services\Reportes;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Servicio robusto para la generación de reportes y documentos en PDF
 * Utiliza Dompdf con soporte para encabezados fijos, paginación dinámica,
 * tablas con salto inteligente y exportación directa/almacenamiento.
 */
class PdfGeneratorService
{
    protected $pdf;
    protected string $filename;

    /**
     * Construye una instancia de PDF con la vista Blade y los datos provistos
     */
    public function make(
        string $view,
        array $data = [],
        string $filename = 'documento.pdf',
        string $paper = 'letter',
        string $orientation = 'portrait'
    ): self {
        $this->filename = str_ends_with($filename, '.pdf') ? $filename : $filename . '.pdf';

        $this->pdf = Pdf::loadView($view, $data);
        $this->pdf->setPaper($paper, $orientation);
        $this->pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'Helvetica',
            'enable_php'           => true,
            'dpi'                  => 120,
        ]);

        return $this;
    }

    /**
     * Muestra el PDF en el navegador (Stream)
     */
    public function stream(): Response
    {
        return $this->pdf->stream($this->filename);
    }

    /**
     * Descarga forzada del PDF en el cliente
     */
    public function download(): Response
    {
        return $this->pdf->download($this->filename);
    }

    /**
     * Guarda el archivo PDF en el almacenamiento local o disco indicado
     */
    public function save(string $diskPath, string $disk = 'local'): string
    {
        $content = $this->pdf->output();
        Storage::disk($disk)->put($diskPath, $content);
        return Storage::disk($disk)->path($diskPath);
    }

    /**
     * Retorna el contenido crudo (bytes) del PDF
     */
    public function output(): string
    {
        return $this->pdf->output();
    }
}

