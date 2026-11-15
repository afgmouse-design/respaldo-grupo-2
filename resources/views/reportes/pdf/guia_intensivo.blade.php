<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Plan de Estudios y Guía de Supervivencia - Curso Intensivo</title>
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

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            border-left: 3px solid #0284c7;
            padding-left: 5px;
            margin-top: 10px;
            margin-bottom: 5px;
            page-break-after: avoid;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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
            width: 22%;
        }

        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 8px;
        }

        table.content-table thead {
            display: table-header-group;
        }

        table.content-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 4px 5px;
            border: 1px solid #334155;
            text-align: left;
        }

        table.content-table td {
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        table.content-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-week {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            font-size: 7.5px;
        }

        .badge-eval {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            font-size: 7.5px;
        }

        .callout-box {
            padding: 6px 8px;
            background-color: #f0fdf4;
            border-left: 3px solid #16a34a;
            margin-bottom: 8px;
            font-size: 8px;
            color: #166534;
        }

        .warning-box {
            padding: 6px 8px;
            background-color: #fff1f2;
            border-left: 3px solid #e11d48;
            margin-bottom: 8px;
            font-size: 8px;
            color: #9f1239;
        }

        .signatures {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .sig-block {
            width: 32%;
            text-align: center;
            vertical-align: top;
            font-size: 8px;
        }

        .sig-line {
            width: 80%;
            margin: 30px auto 4px auto;
            border-top: 1px solid #475569;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <header>
        <table class="header-table">
            <tr>
                <td class="header-text">
                    <div class="header-institution">Universidad Politécnica Territorial del Estado Portuguesa "J.J. Montilla"</div>
                    <div class="header-sub">Vicerrectorado Académico • Dirección de Control de Estudios • Régimen Acelerado SOGAC</div>
                    <div class="header-title">Plan de Estudios, Cronograma y Guía de Supervivencia — Curso Intensivo</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">SOGAC - Sistema de Gestión Académica y Control de Estudios</td>
                <td style="width: 50%; text-align: right;">Emisión: {{ date('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </footer>

    <!-- METADATOS DEL CURSO -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Asignatura / Unidad:</td>
            <td style="width: 28%; font-weight: bold; color: #0284c7;">{{ $asignatura ?? 'Bases de Datos / Programación' }}</td>
            <td class="meta-label">Código Oficial:</td>
            <td style="width: 24%; font-family: monospace;">{{ $codigo ?? 'INF-2204' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Régimen y Duración:</td>
            <td>4 Semanas (20 sesiones hábiles intensivas)</td>
            <td class="meta-label">Carga Diaria:</td>
            <td>5 Horas (3h Aula / Lab + 2h Autónomo)</td>
        </tr>
        <tr>
            <td class="meta-label">Total Horas Período:</td>
            <td>100 Horas Académicas Aceleradas</td>
            <td class="meta-label">Sistema Evaluativo:</td>
            <td>4 Hitos Prácticos Continuos (25% c/u)</td>
        </tr>
    </table>

    <div class="callout-box">
        <strong>Directriz Académica de Aceleración:</strong> El curso intensivo cubre el 100% de las competencias curriculares bajo metodología High-Yield (alta densidad pedagógica). Todo contenido no aplicable o redundante ha sido sintetizado en favor de retos prácticos diarios y entregables verificables sesión por sesión.
    </div>

    <!-- 1. SYLLABUS COMPRIMIDO -->
    <div class="section-title">1. Desglose por Semanas (Syllabus Comprimido High-Yield)</div>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 14%;">Semana</th>
                <th style="width: 28%;">Núcleo Temático de Alto Impacto</th>
                <th style="width: 38%;">Objetivo Práctico / Hito Técnico</th>
                <th style="width: 20%; text-align: center;">Ponderación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge-week">SEMANA 1</span></td>
                <td><strong>Fundamentos y Arquitectura Base</strong><br>Entorno, Modelo Relacional, DDL y Restricciones de Integridad.</td>
                <td>Estructuración del esquema relacional en PostgreSQL sin redundancias ni violaciones de integridad referencial.</td>
                <td style="text-align: center;"><span class="badge-eval">Hito 1: 25%</span><br><small>Esquema y DDL</small></td>
            </tr>
            <tr>
                <td><span class="badge-week">SEMANA 2</span></td>
                <td><strong>Lógica Central y Consultas Complejas</strong><br>DML, JOINS múltiples, agregaciones, GROUP BY y Subconsultas.</td>
                <td>Construcción de consultas analíticas avanzadas para generación de reportes y auditoría transaccional.</td>
                <td style="text-align: center;"><span class="badge-eval">Hito 2: 25%</span><br><small>Taller Práctico SQL</small></td>
            </tr>
            <tr>
                <td><span class="badge-week">SEMANA 3</span></td>
                <td><strong>Procedimientos, Triggers y Optimización</strong><br>PL/pgSQL, Triggers automáticos, Índices B-Tree y Planes de Ejecución (EXPLAIN).</td>
                <td>Automatización de reglas de negocio en base de datos y optimización de latencia en consultas críticas.</td>
                <td style="text-align: center;"><span class="badge-eval">Hito 3: 25%</span><br><small>Lógica en BD & Tuning</small></td>
            </tr>
            <tr>
                <td><span class="badge-week">SEMANA 4</span></td>
                <td><strong>Proyecto Integrado y Despliegue</strong><br>Transacciones ACID, Concurrencia, Políticas de Respaldos y Defensa Técnica.</td>
                <td>Defensa de proyecto de software con persistencia optimizada y simulación de carga masiva concurrente.</td>
                <td style="text-align: center;"><span class="badge-eval">Hito 4: 25%</span><br><small>Proyecto Final & Pitch</small></td>
            </tr>
        </tbody>
    </table>

    <!-- 2. CRONOGRAMA DIARIO SESIÓN POR SESIÓN -->
    <div class="section-title">2. Cronograma Diario Sesión por Sesión (Día 1 al Día 20)</div>
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">Día</th>
                <th style="width: 24%;">Tema Central</th>
                <th style="width: 36%;">Sesión de Aula / Laboratorio (3 Horas)</th>
                <th style="width: 32%;">Reto Autónomo Diario (2 Horas)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 1</td>
                <td>Arquitectura y Paradigma</td>
                <td>Configuración del motor Postgres, roles, permisos y arquitectura cliente-servidor.</td>
                <td>Script de aprovisionamiento de base de datos y verificación de conexiones.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 2</td>
                <td>Modelo Entidad-Relación</td>
                <td>Normalización (1FN, 2FN, 3FN) con casos de estudio institucionales reales.</td>
                <td>Modelado del diagrama relacional en dbdiagram.io / pgModeler con 8 entidades.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 3</td>
                <td>DDL y Restricciones</td>
                <td>Creación de tablas, claves primarias compuestas, foráneas (CASCADE/RESTRICT).</td>
                <td>Creación de 5 constraints complejos (CHECK, UNIQUE condicional, NOT NULL).</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 4</td>
                <td>Inserción Masiva y Seeds</td>
                <td>Poblado masivo con SQL y factories, integridad de secuencias e índices PK.</td>
                <td>Poblado de 500 registros coherentes mediante script SQL parametrizado.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; background-color: #fef2f2;">Día 5</td>
                <td><strong>Defensa Hito 1 (25%)</strong></td>
                <td>Revisión de esquemas en vivo. Corrección de normalización bajo presión.</td>
                <td>Entrega formal del repositorio con esquema validado por el docente.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 6</td>
                <td>DML y Filtros Complejos</td>
                <td>Operaciones UPDATE/DELETE condicionales, cláusulas WHERE, LIKE, IN y BETWEEN.</td>
                <td>Batería de 15 sentencias de actualización y depuración de inconsistencias.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 7</td>
                <td>JOINS Multitabla</td>
                <td>INNER JOIN, LEFT JOIN, RIGHT JOIN y FULL OUTER JOIN con álgebra relacional.</td>
                <td>Resolución de 10 consultas cruzadas que involucren 4 tablas simultáneamente.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 8</td>
                <td>Agrupaciones y Ventana</td>
                <td>GROUP BY, HAVING, funciones agregadas (COUNT, SUM, AVG) y Window Functions.</td>
                <td>Generación de reporte financiero/académico con rankings (ROW_NUMBER, RANK).</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 9</td>
                <td>Subconsultas y CTEs</td>
                <td>Subqueries correlacionadas, EXISTS, y Common Table Expressions (WITH).</td>
                <td>Refactorización de consultas anidadas a expresiones CTE legibles y eficientes.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; background-color: #fef2f2;">Día 10</td>
                <td><strong>Defensa Hito 2 (25%)</strong></td>
                <td>Taller práctico cronometrado: 5 requerimientos de consulta en 90 minutos.</td>
                <td>Corrección de errores identificados en el taller y subida de bitácora.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 11</td>
                <td>Vistas e Índices</td>
                <td>Vistas estándar y materializadas (REFRESH), índices B-Tree, Hash y GIN.</td>
                <td>Benchmarking con EXPLAIN ANALYZE antes y después de aplicar índices.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 12</td>
                <td>Funciones y Stored Procedures</td>
                <td>Estructuras de control (IF, LOOP), variables y manejo de excepciones en PL/pgSQL.</td>
                <td>Implementación de 2 funciones con validaciones y retorno de tablas (SETOF).</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 13</td>
                <td>Triggers y Auditoría</td>
                <td>Disparadores BEFORE/AFTER, OLD/NEW y tabla de bitácora transaccional automática.</td>
                <td>Desarrollo de trigger para registrar auditoría de cada modificación en el sistema.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 14</td>
                <td>Manejo de Errores y Seguridad</td>
                <td>Bloques BEGIN/EXCEPTION, RAISE EXCEPTION, permisos de esquema y vistas seguras.</td>
                <td>Blindaje de procedimientos contra inyección y validación estricta de parámetros.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; background-color: #fef2f2;">Día 15</td>
                <td><strong>Defensa Hito 3 (25%)</strong></td>
                <td>Demostración en vivo de ejecución de triggers, funciones y auditoría en terminal.</td>
                <td>Entrega del paquete de base de datos con procedimientos 100% operativos.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 16</td>
                <td>Transacciones ACID y Bloqueos</td>
                <td>Niveles de aislamiento, BEGIN/COMMIT/ROLLBACK, SAVEPOINT y FOR UPDATE.</td>
                <td>Simulación de concurrencia y prevención de bloqueos mutuos (Deadlocks).</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 17</td>
                <td>Respaldos y Migraciones</td>
                <td>Estrategias con pg_dump/pg_restore, respaldos incrementales y migrations.</td>
                <td>Automatización de backup con script y prueba de restauración en frío.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 18</td>
                <td>Integración con la Aplicación</td>
                <td>Conexión ORM/Driver, pooling de conexiones y prevención de N+1 queries.</td>
                <td>Prueba integral de la aplicación consumiendo la base de datos intensiva.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">Día 19</td>
                <td>Pruebas de Carga y Ajuste</td>
                <td>Generación de carga con 10.000 transacciones y monitoreo de recursos del servidor.</td>
                <td>Ajuste fino de configuración (work_mem, shared_buffers) y documentación final.</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold; background-color: #fef2f2;">Día 20</td>
                <td><strong>Hito 4: Defensa Final (25%)</strong></td>
                <td>Presentación formal del proyecto, pruebas en caliente con el jurado y cierre.</td>
                <td>Cierre de actas de notas definitivas y entrega de reporte en Control de Estudios.</td>
            </tr>
        </tbody>
    </table>

    <!-- 3. PLAN DE MITIGACIÓN DE RIESGOS -->
    <div class="avoid-break">
        <div class="section-title">3. Plan de Mitigación de Riesgos y Cuellos de Botella Académicos</div>
        <div class="warning-box">
            <strong>Protocolo de Supervivencia y Prevención de Deserción:</strong> Debido al ritmo acelerado de 5 horas diarias, un atraso de más de 48 horas es causal de reprobación inminente. A continuación se detallan las medidas de contingencia:
        </div>

        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Punto Crítico / Cuello de Botella</th>
                    <th style="width: 35%;">Síntoma Típico de Rezago</th>
                    <th style="width: 40%;">Estrategia Inmediata de Superación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Normalización Inadecuada (Semana 1)</strong></td>
                    <td>Tablas con campos repetidos o claves foráneas ambiguas que impiden continuar con el DDL.</td>
                    <td>Reunión técnica obligatoria de 30 min en Día 3 con el docente para redibujar el diagrama antes de codificar SQL.</td>
                </tr>
                <tr>
                    <td><strong>JOINS Múltiples y Agrupaciones (Semana 2)</strong></td>
                    <td>Resultados cartesianos desbordados, duplicación de métricas y confusión en LEFT vs INNER.</td>
                    <td>Uso de tablas temporales intermedias y técnica paso a paso: validar primero el filtro antes de agregar la agrupación.</td>
                </tr>
                <tr>
                    <td><strong>Lógica de Triggers y PL/pgSQL (Semana 3)</strong></td>
                    <td>Recursión infinita en triggers de actualización o errores de tipo de dato en NEW/OLD.</td>
                    <td>Implementar plantillas de disparadores pre-validadas y aislar la lógica en funciones independientes testeables.</td>
                </tr>
                <tr>
                    <td><strong>Transacciones y Concurrencia (Semana 4)</strong></td>
                    <td>Bloqueos de tablas completas y caídas de rendimiento por falta de índices en claves de filtro.</td>
                    <td>Verificación de EXPLAIN ANALYZE obligatoria en el 100% de los endpoints de la aplicación.</td>
                </tr>
            </tbody>
        </table>

        <!-- FIRMAS -->
        <table class="signatures">
            <tr>
                <td class="sig-block">
                    <div class="sig-line"></div>
                    <strong>Docente Responsable</strong><br>
                    C.I. / Firma y Fecha
                </td>
                <td class="sig-block">
                    <div class="sig-line"></div>
                    <strong>Coordinación del PNF</strong><br>
                    Sello y Firma
                </td>
                <td class="sig-block">
                    <div class="sig-line"></div>
                    <strong>Dirección de Control de Estudios</strong><br>
                    Validación Institucional SOGAC
                </td>
            </tr>
        </table>
    </div>

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

