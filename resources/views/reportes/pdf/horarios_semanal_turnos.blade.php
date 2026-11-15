<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'Horario de Estudiante' }} - UPTP</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0.6cm 0.7cm 0.5cm 0.7cm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .mppeu-sub {
            font-size: 8px;
            color: #1e293b;
            line-height: 1.15;
        }

        .mppeu-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e3a8a;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .uptp-inst {
            font-size: 8.5px;
            font-weight: bold;
            color: #000000;
            line-height: 1.1;
        }

        .uptp-edo {
            font-size: 7px;
            font-weight: bold;
            color: #dc2626;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .uptp-nom {
            font-size: 9px;
            font-weight: bold;
            color: #000000;
            line-height: 1.1;
        }

        .linea-roja {
            width: 100%;
            height: 3.5px;
            background-color: #990000;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        /* TÍTULOS Y DATOS DEL ESTUDIANTE */
        .title-block {
            text-align: center;
            margin-bottom: 4px;
            line-height: 1.25;
        }

        .main-title {
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .lapso-title {
            font-size: 9.5px;
            font-weight: bold;
            font-style: italic;
            text-transform: uppercase;
        }

        .persona-title {
            font-size: 10px;
            font-weight: bold;
            font-style: italic;
            text-transform: uppercase;
        }

        .sub-turno-header {
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            font-style: italic;
            text-transform: uppercase;
            margin-top: 3px;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        /* CUADRÍCULAS TABULARES */
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            table-layout: fixed;
            margin-bottom: 2px;
        }

        table.grid-table th,
        table.grid-table td {
            border: 1px solid #000000;
            padding: 0;
            text-align: center;
            vertical-align: middle;
        }

        .th-hora {
            width: 11%;
            background-color: #ffffff;
            height: 16px;
        }

        .th-dia {
            width: 12.71%;
            font-size: 8.5px;
            font-weight: bold;
            font-style: italic;
            text-transform: uppercase;
            background-color: #ffffff;
            height: 16px;
        }

        .td-hora {
            width: 11%;
            font-size: 7px;
            line-height: 1.1;
            height: 23px;
            padding: 1px 0 !important;
        }

        .hora-top {
            display: block;
        }

        .hora-bot {
            display: block;
        }

        .td-clase {
            width: 12.71%;
            height: 23px;
            padding: 1px !important;
            font-size: 7.5px;
            line-height: 1.15;
        }

        .bloque-materia {
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            font-size: 7.5px;
        }

        .bloque-ubicacion {
            font-size: 6.5px;
            color: #000000;
            text-transform: uppercase;
        }

        /* LEYENDA INFERIOR */
        table.legend-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-top: 3px;
            table-layout: fixed;
        }

        table.legend-table td {
            border: 1px solid #000000;
            padding: 1.5px 3px;
            font-size: 6.5px;
            font-weight: bold;
            line-height: 1.25;
            vertical-align: top;
            text-transform: uppercase;
            width: 50%;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 44px; text-align: left; vertical-align: middle;">
                <div style="width: 36px; border: 0.5px solid #94a3b8; margin-bottom: 2px;">
                    <div style="height: 5px; background-color: #facc15;"></div>
                    <div style="height: 5px; background-color: #1d4ed8; text-align: center; line-height: 4.5px; color: #fff; font-size: 4px;">★ ★ ★ ★ ★</div>
                    <div style="height: 5px; background-color: #dc2626;"></div>
                </div>
                <div style="font-size: 5px; color: #4b5563; line-height: 1; text-transform: uppercase;">REPÚBLICA BOLIVARIANA DE</div>
                <div style="font-size: 6px; font-weight: bold; color: #000000; line-height: 1; text-transform: uppercase;">VENEZUELA</div>
            </td>
            <td style="width: 12px; text-align: center; vertical-align: middle;">
                <div style="border-left: 1px solid #94a3b8; height: 32px; margin: 0 auto;"></div>
            </td>
            <td style="text-align: left; vertical-align: middle; padding-left: 3px;">
                <div class="mppeu-sub">Ministerio del Poder Popular para la</div>
                <div class="mppeu-title">EDUCACIÓN UNIVERSITARIA</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <table style="float: right; border-collapse: collapse;">
                    <tr>
                        <td style="width: 38px; vertical-align: middle; text-align: center; padding-right: 5px;">
                            @if (file_exists(public_path('img/logouptp.png')))
                                <img src="{{ public_path('img/logouptp.png') }}" style="height: 32px; max-width: 38px;" alt="UPTP" />
                            @endif
                        </td>
                        <td style="vertical-align: middle; text-align: left;">
                            <div class="uptp-inst">Universidad Politécnica Territorial</div>
                            <div class="uptp-edo">DEL ESTADO PORTUGUESA</div>
                            <div class="uptp-nom">“Juan de Jesús Montilla”</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="linea-roja"></div>

    <!-- TÍTULOS Y DATOS -->
    <div class="title-block">
        <div class="main-title">HORARIO DE ESTUDIANTE</div>
        <div class="lapso-title">LAPSO: {{ $lapsoCodigo }}</div>
        <div class="persona-title">{{ $nombrePersona }}</div>
    </div>

    <!-- TURNO 1: MATUTINO -->
    <div class="sub-turno-header">MATUTINO</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th class="th-hora"></th>
                @foreach ($diasSemanales as $diaNum => $diaNom)
                    <th class="th-dia">{{ $diaNom }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($turnos[0]['bloques'] as $b)
                <tr>
                    <td class="td-hora">
                        <span class="hora-top">{{ $b['hora_texto_inicio'] }}</span>
                        <span class="hora-bot">{{ $b['hora_texto_fin'] }}</span>
                    </td>

                    @foreach ($diasSemanales as $diaNum => $diaNom)
                        @php
                            $claseEncontrada = $clases->first(function ($c) use ($diaNum, $b) {
                                if ((int) $c->dia_semana !== (int) $diaNum) {
                                    return false;
                                }
                                $ini = substr((string) $c->hora_inicio, 0, 5) . ':00';
                                $fin = substr((string) $c->hora_fin, 0, 5) . ':00';
                                return ($ini < $b['fin'] && $fin > $b['inicio']);
                            });
                        @endphp

                        <td class="td-clase">
                            @if ($claseEncontrada)
                                <div class="bloque-materia">{{ $claseEncontrada->codigo_unc }}</div>
                                <div class="bloque-ubicacion">
                                    S: {{ $claseEncontrada->codigo_sec ?: 'S/C' }} A-{{ $claseEncontrada->codigo_aula_aul ?: 'S/A' }}
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TURNO 2: VESPERTINO -->
    <div class="sub-turno-header">VESPERTINO</div>
    <table class="grid-table">
        <tbody>
            @foreach ($turnos[1]['bloques'] as $b)
                <tr>
                    <td class="td-hora">
                        <span class="hora-top">{{ $b['hora_texto_inicio'] }}</span>
                        <span class="hora-bot">{{ $b['hora_texto_fin'] }}</span>
                    </td>

                    @foreach ($diasSemanales as $diaNum => $diaNom)
                        @php
                            $claseEncontrada = $clases->first(function ($c) use ($diaNum, $b) {
                                if ((int) $c->dia_semana !== (int) $diaNum) {
                                    return false;
                                }
                                $ini = substr((string) $c->hora_inicio, 0, 5) . ':00';
                                $fin = substr((string) $c->hora_fin, 0, 5) . ':00';
                                return ($ini < $b['fin'] && $fin > $b['inicio']);
                            });
                        @endphp

                        <td class="td-clase">
                            @if ($claseEncontrada)
                                <div class="bloque-materia">{{ $claseEncontrada->codigo_unc }}</div>
                                <div class="bloque-ubicacion">
                                    S: {{ $claseEncontrada->codigo_sec ?: 'S/C' }} A-{{ $claseEncontrada->codigo_aula_aul ?: 'S/A' }}
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TURNO 3: NOCTURNO -->
    <div class="sub-turno-header">NOCTURNO</div>
    <table class="grid-table">
        <tbody>
            @foreach ($turnos[2]['bloques'] as $b)
                <tr>
                    <td class="td-hora">
                        <span class="hora-top">{{ $b['hora_texto_inicio'] }}</span>
                        <span class="hora-bot">{{ $b['hora_texto_fin'] }}</span>
                    </td>

                    @foreach ($diasSemanales as $diaNum => $diaNom)
                        @php
                            $claseEncontrada = $clases->first(function ($c) use ($diaNum, $b) {
                                if ((int) $c->dia_semana !== (int) $diaNum) {
                                    return false;
                                }
                                $ini = substr((string) $c->hora_inicio, 0, 5) . ':00';
                                $fin = substr((string) $c->hora_fin, 0, 5) . ':00';
                                return ($ini < $b['fin'] && $fin > $b['inicio']);
                            });
                        @endphp

                        <td class="td-clase">
                            @if ($claseEncontrada)
                                <div class="bloque-materia">{{ $claseEncontrada->codigo_unc }}</div>
                                <div class="bloque-ubicacion">
                                    S: {{ $claseEncontrada->codigo_sec ?: 'S/C' }} A-{{ $claseEncontrada->codigo_aula_aul ?: 'S/A' }}
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- LEYENDA INFERIOR DE MATERIAS Y DOCENTES -->
    @if (!empty($materiasLista) && count($materiasLista) > 0)
        @php
            $total = count($materiasLista);
            $mitad = (int) ceil($total / 2);
            $colIzq = array_slice($materiasLista, 0, $mitad);
            $colDer = array_slice($materiasLista, $mitad);
            $maxFilas = max(count($colIzq), count($colDer));
        @endphp

        <table class="legend-table">
            <tbody>
                @for ($i = 0; $i < $maxFilas; $i++)
                    <tr>
                        <td>
                            @if (isset($colIzq[$i]))
                                @php $m = $colIzq[$i]; @endphp
                                {{ $m['codigo'] }} - {{ $m['nombre'] }} - {{ $m['docente'] }}
                                @if (!empty($m['telefono'])) - {{ $m['telefono'] }} @endif
                                @if (!empty($m['email'])) {{ $m['email'] }} @endif
                            @endif
                        </td>
                        <td>
                            @if (isset($colDer[$i]))
                                @php $m = $colDer[$i]; @endphp
                                {{ $m['codigo'] }} - {{ $m['nombre'] }} - {{ $m['docente'] }}
                                @if (!empty($m['telefono'])) - {{ $m['telefono'] }} @endif
                                @if (!empty($m['email'])) {{ $m['email'] }} @endif
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif

</body>
</html>
