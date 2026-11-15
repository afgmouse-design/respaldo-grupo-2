<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listado de Chequeo de Cursos Intensivos - SOGAC</title>
    <style>
        @page {
            margin: 2.5cm 1.5cm 2cm 1.5cm;
            size: letter portrait;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.35;
            color: #1e293b;
        }

        header {
            position: fixed;
            top: -2.1cm;
            left: 0;
            right: 0;
            height: 1.8cm;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 4px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-text {
            text-align: center;
            vertical-align: middle;
        }

        .header-institution {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
        }

        .header-sub {
            font-size: 8.5px;
            color: #475569;
        }

        .header-title {
            font-size: 11px;
            font-weight: bold;
            color: #0284c7;
            margin-top: 2px;
            text-transform: uppercase;
        }

        footer {
            position: fixed;
            bottom: -1.3cm;
            left: 0;
            right: 0;
            height: 1cm;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 7.5px;
            color: #64748b;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        .meta-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
        }

        .meta-label {
            font-weight: bold;
            color: #334155;
            background-color: #f1f5f9;
            width: 25%;
        }

        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 8.5px;
        }

        table.content-table thead {
            display: table-header-group;
        }

        table.content-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 5px 6px;
            border: 1px solid #334155;
            text-align: left;
        }

        table.content-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        table.content-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-active {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td class="header-text">
                    <div class="header-institution">Universidad Politécnica Territorial del Estado Portuguesa "J.J. Montilla"</div>
                    <div class="header-sub">Dirección de Control de Estudios • División de Programación Académica</div>
                    <div class="header-title">Listado de Chequeo y Auditoría — Secciones y Cursos Intensivos</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">SOGAC - Módulo de Programación Académica</td>
                <td style="width: 50%; text-align: right;">Emisión: {{ date('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </footer>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Régimen Académico:</td>
            <td style="width: 25%;">Avance Intensivo (Período Vacacional / Especial)</td>
            <td class="meta-label">Total Secciones Registradas:</td>
            <td style="width: 25%; font-weight: bold;">{{ count($secciones) }} secciones</td>
        </tr>
        <tr>
            <td class="meta-label">Unidades de Formación:</td>
            <td>PNF / Carreras Vigentes</td>
            <td class="meta-label">Fecha de Auditoría:</td>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">N°</th>
                <th style="width: 22%;">Sección Intensiva</th>
                <th style="width: 24%;">Programa / Malla Curricular</th>
                <th style="width: 28%;">Unidades Curriculares Asignadas</th>
                <th style="width: 10%; text-align: center;">Capacidad</th>
                <th style="width: 10%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($secciones as $idx => $sec)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $sec->nombre_sec }}</strong><br>
                        <small style="font-family: monospace; color: #64748b;">{{ $sec->codigo_sec ?? 'S/C' }}</small>
                    </td>
                    <td>
                        {{ $sec->mallaCurricular ? $sec->mallaCurricular->nombre_mac : 'Sin Malla Asignada' }}
                        @if($sec->mallaCurricular && $sec->mallaCurricular->pnf)
                            <br><small style="color: #64748b;">{{ $sec->mallaCurricular->pnf->nombre_pnf }}</small>
                        @endif
                    </td>
                    <td>
                        @if($sec->unidadesDocentes && $sec->unidadesDocentes->isNotEmpty())
                            @foreach($sec->unidadesDocentes as $ud)
                                <div style="margin-bottom: 2px;">
                                    • {{ $ud->mallaCurricularDetalle->unidadCurricular->nombre_unc ?? 'Materia' }}
                                    @if($ud->docente)
                                        <small style="color: #64748b;">(Prof. {{ $ud->docente->name }})</small>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Sin asignaturas vinculadas</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-family: monospace; font-weight: bold;">
                        {{ $sec->cupo_maximo_sec ?? 30 }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-status {{ $sec->status ? 'badge-active' : 'badge-inactive' }}">
                            {{ $sec->status ? 'Abierta' : 'Cerrada' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px; color: #64748b;">
                        No existen secciones registradas bajo la modalidad de Intensivo en el período consultado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SCRIPT DE NUMERACIÓN DOMPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 7.5;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 28;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.4, 0.45, 0.5));
        }
    </script>
</body>
</html>

