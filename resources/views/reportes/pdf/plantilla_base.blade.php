<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'Reporte SOGAC' }}</title>
    <style>
        @page {
            margin: 3cm 1.5cm 2cm 1.5cm;
            size: letter portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
        }

        header {
            position: fixed;
            top: -2.3cm;
            left: 0;
            right: 0;
            height: 2cm;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 5px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 70px;
            text-align: left;
            vertical-align: middle;
        }

        .header-text {
            text-align: center;
            vertical-align: middle;
        }

        .header-institution {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }

        .header-system {
            font-size: 9px;
            color: #64748b;
        }

        .header-title {
            font-size: 13px;
            font-weight: bold;
            color: #0284c7;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .header-meta {
            width: 90px;
            font-size: 8px;
            text-align: right;
            color: #64748b;
            vertical-align: middle;
        }

        footer {
            position: fixed;
            bottom: -1.3cm;
            left: 0;
            right: 0;
            height: 1cm;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 8px;
            color: #64748b;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            border-left: 3px solid #0284c7;
            padding-left: 6px;
            margin-top: 14px;
            margin-bottom: 8px;
            page-break-after: avoid;
        }

        .paragraph {
            text-align: justify;
            margin-bottom: 8px;
            color: #334155;
        }

        .metadata-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .metadata-box td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }

        .meta-label {
            font-weight: bold;
            color: #475569;
            background-color: #f1f5f9;
            width: 25%;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table th {
            background-color: #0284c7;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #0284c7;
            text-align: left;
        }

        table.data-table td {
            padding: 5px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            color: #334155;
            vertical-align: middle;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: monospace; font-size: 8px; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <div style="font-weight: bold; font-size: 14px; color: #0284c7;">UPTP</div>
                </td>
                <td class="header-text">
                    <div class="header-institution">Universidad Politécnica Territorial del Estado Portuguesa</div>
                    <div class="header-system">Sistema Oficial de Gestión Académica y Control de Estudios (SOGAC)</div>
                    <div class="header-title">{{ $titulo }}</div>
                </td>
                <td class="header-meta">
                    <div><strong>Código:</strong> {{ $codigoDoc ?? 'SOGAC-RPT-01' }}</div>
                    <div><strong>Emisión:</strong> {{ date('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="text-align: left;" width="50%">
                    Documento oficial generado automáticamente por SOGAC.
                </td>
                <td style="text-align: right;" width="50%">
                    Validación Digital SOGAC: {{ strtoupper(substr(md5(($titulo ?? 'SOGAC') . date('Ymd')), 0, 16)) }}
                </td>
            </tr>
        </table>
    </footer>

    <main>
        <table class="metadata-box">
            <tr>
                <td class="meta-label">Programa (PNF):</td>
                <td>{{ $programa }}</td>
                <td class="meta-label">Versión / Pensum:</td>
                <td>{{ $version }}</td>
            </tr>
            <tr>
                <td class="meta-label">Gaceta Oficial:</td>
                <td>{{ $gaceta ?? 'Vigente' }}</td>
                <td class="meta-label">Requisitos Egreso TSU:</td>
                <td>{{ $requisitosTsu }}</td>
            </tr>
            <tr>
                <td class="meta-label">Sede / Núcleo:</td>
                <td>{{ $sede ?? 'Todas las Sedes' }}</td>
                <td class="meta-label">Requisitos Ingeniería:</td>
                <td>{{ $requisitosIng }}</td>
            </tr>
        </table>

        <div class="section-title">Descripción Curricular y Normativa</div>
        <p class="paragraph">
            El presente informe certifica la distribución académica de las unidades curriculares, prelaciones, cargas horarias y créditos académicos vigentes para la prosecución de estudios. Cualquier modificación en los componentes formativos debe estar avalada por el comité curricular institucional y la coordinación de control de estudios.
        </p>

        @foreach($bloques as $trayectoNombre => $materias)
            <div class="section-title">{{ strtoupper($trayectoNombre) }}</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">Nro.</th>
                        <th width="14%" class="text-center">Código</th>
                        <th width="38%">Unidad Curricular</th>
                        <th width="10%" class="text-center">HTEA</th>
                        <th width="10%" class="text-center">HTEI</th>
                        <th width="8%" class="text-center">THTE</th>
                        <th width="6%" class="text-center">UC</th>
                        <th width="16%">Prelaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materias as $index => $materia)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center font-mono"><strong>{{ $materia['codigo'] }}</strong></td>
                            <td>{{ $materia['nombre'] }}</td>
                            <td class="text-center">{{ $materia['htea'] }}h</td>
                            <td class="text-center">{{ $materia['htei'] }}h</td>
                            <td class="text-center"><strong>{{ $materia['thte'] }}h</strong></td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $materia['uc'] }}</span>
                            </td>
                            <td>
                                @if(!empty($materia['prelaciones']))
                                    <span class="badge badge-info">{{ $materia['prelaciones'] }}</span>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Sin prelación</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 8;
            $color = array(0.39, 0.45, 0.54);
            $canvasWidth = $pdf->get_width();
            $canvasHeight = $pdf->get_height();
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
            $x = $canvasWidth - 42 - $textWidth;
            $y = $canvasHeight - 38;
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>
</body>
</html>

