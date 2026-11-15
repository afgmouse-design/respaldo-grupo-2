<script setup lang="ts">
/**
 * Vista Standalone: Gestión, Chequeo y Plan Instruccional de Cursos Intensivos (SOGAC)
 *
 * Propósito: Ofrece una plataforma integral para la modalidad intensiva acelerada:
 * 1. Auditoría de secciones y estudiantes inscritos en avance intensivo.
 * 2. Diseñador pedagógico acelerado: Syllabus comprimido (High-Yield 4 semanas).
 * 3. Cronograma diario sesión por sesión (20 días, 3h aula + 2h autónomo).
 * 4. Rúbrica y estrategia de práctica continua basada en 4 hitos (25% c/u).
 * 5. Plan de mitigación de cuellos de botella y prevención de deserción.
 * 6. Generación y descarga directa en PDF institucional oficial.
 */
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    FileCheck,
    Printer,
    Download,
    FileText,
    Calendar,
    Clock,
    Target,
    AlertTriangle,
    ShieldAlert,
    CheckCircle2,
    BookOpen,
    Layers,
    Sparkles,
    Flame,
    Compass,
    ArrowRight,
    Users,
    GraduationCap,
    HelpCircle,
    Activity,
} from 'lucide-vue-next';

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc: string;
    nombre_unc: string;
    semestre_unc?: number | null;
    unidades_credito_unc?: number | null;
}

interface SeccionItem {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    cupo_maximo_sec?: number | null;
    status: boolean;
    malla_curricular?: {
        nombre_mac: string;
        pnf?: {
            nombre_pnf: string;
        };
    };
    unidades_docentes?: Array<{
        id_sud: number;
        docente?: { name: string };
        malla_curricular_detalle?: {
            unidad_curricular?: {
                nombre_unc: string;
                codigo_unc: string;
            };
        };
    }>;
}

const props = defineProps<{
    secciones: {
        data: SeccionItem[];
        total: number;
    };
    unidadesCurriculares?: UnidadCurricularItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Planificación', href: '#' },
    { title: 'Cursos Intensivos', subtitle: 'Gestión y Chequeo', href: route('informes.chequeo_intensivo') },
];

// Pestaña Principal: 'guia' (Plan y Cronograma) o 'secciones' (Auditoría de Secciones)
const tabPrincipal = ref<'guia' | 'secciones'>('guia');

// Sub-pestaña del Diseñador Instruccional: 'syllabus' | 'cronograma' | 'evaluacion' | 'riesgos'
const tabSub = ref<'syllabus' | 'cronograma' | 'evaluacion' | 'riesgos'>('syllabus');

// Selector reactivo de Unidad Curricular para el diseño instruccional
const uncSeleccionadaId = ref<string | number>(
    props.unidadesCurriculares && props.unidadesCurriculares.length > 0
        ? props.unidadesCurriculares[0].id_unc
        : ''
);

const unidadActual = computed(() => {
    if (!props.unidadesCurriculares || !uncSeleccionadaId.value) return null;
    return props.unidadesCurriculares.find((u) => u.id_unc === Number(uncSeleccionadaId.value)) || null;
});

const nombreAsignatura = computed(() => {
    return unidadActual.value ? unidadActual.value.nombre_unc : 'Bases de Datos / Programación Avanzada';
});

const codigoAsignatura = computed(() => {
    return unidadActual.value ? unidadActual.value.codigo_unc : 'INF-2204';
});

// URL para descarga directa de la Guía Oficial en PDF
const urlDescargaPdfGuia = computed(() => {
    const params = new URLSearchParams();
    if (unidadActual.value) {
        params.append('unc_id', String(unidadActual.value.id_unc));
    }
    params.append('materia', nombreAsignatura.value);
    params.append('codigo', codigoAsignatura.value);
    return `/informes-programacion/chequeo-intensivo/guia-pdf?${params.toString()}`;
});

const urlDescargaPdfAuditoria = computed(() => {
    return `/informes-programacion/chequeo-intensivo/exportar-pdf`;
});

// Datos estructurados del Syllabus High-Yield (4 Semanas)
const syllabusSemanas = [
    {
        semana: 'Semana 1',
        titulo: 'Fundamentos de Alta Densidad y Arquitectura Base',
        enfoque: 'Entorno de ejecución, modelo relacional formal, DDL y restricciones de integridad.',
        horasAula: 15,
        horasAuto: 10,
        hito: 'Hito 1 (25%)',
        hitoNombre: 'Esquema y DDL Blindado',
        descripcionHito: 'Modelado relacional en 3FN e implementación del script DDL en PostgreSQL con constraints avanzados sin errores.',
        color: 'from-blue-500/10 to-indigo-500/10 border-blue-500/30 text-blue-700 dark:text-blue-400',
    },
    {
        semana: 'Semana 2',
        titulo: 'Lógica Central, JOINS y Consultas Complejas',
        enfoque: 'DML analítico, cruces multitabla (INNER/LEFT/RIGHT), agrupaciones complejas y subconsultas correlacionadas.',
        horasAula: 15,
        horasAuto: 10,
        hito: 'Hito 2 (25%)',
        hitoNombre: 'Taller Práctico de Consultas',
        descripcionHito: 'Taller cronometrado de 90 minutos: extracción de métricas complejas para reportes institucionales.',
        color: 'from-emerald-500/10 to-teal-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-400',
    },
    {
        semana: 'Semana 3',
        titulo: 'Procedimientos Almacenados, Triggers y Optimización',
        enfoque: 'PL/pgSQL avanzado, triggers automáticos de auditoría, índices B-Tree/GIN y análisis EXPLAIN.',
        horasAula: 15,
        horasAuto: 10,
        hito: 'Hito 3 (25%)',
        hitoNombre: 'Lógica en BD y Tuning',
        descripcionHito: 'Desarrollo de bitácora transaccional automática y reducción de latencia en consultas con indexación técnica.',
        color: 'from-amber-500/10 to-orange-500/10 border-amber-500/30 text-amber-700 dark:text-amber-400',
    },
    {
        semana: 'Semana 4',
        titulo: 'Proyecto Integrado, ACID, Concurrencia y Despliegue',
        enfoque: 'Transacciones aisladas, prevención de bloqueos (Deadlocks), copias de respaldo y sustentación final.',
        horasAula: 15,
        horasAuto: 10,
        hito: 'Hito 4 (25%)',
        hitoNombre: 'Proyecto Final & Pitch Técnico',
        descripcionHito: 'Demostración integral de la solución en producción con pruebas de carga concurrente y entrega de actas.',
        color: 'from-purple-500/10 to-pink-500/10 border-purple-500/30 text-purple-700 dark:text-purple-400',
    },
];

// Cronograma detallado de los 20 días hábiles
const cronogramaDias = [
    { dia: 1, semana: 1, tema: 'Arquitectura y Paradigma', aula: 'Setup de PostgreSQL, roles, extensiones y permisos.', reto: 'Aprovisionamiento de base de datos y script de test.', entregable: 'Conexión verificada' },
    { dia: 2, semana: 1, tema: 'Modelo Entidad-Relación y Normalización', aula: '1FN, 2FN y 3FN aplicadas a casos reales.', reto: 'Diagrama relacional con 8 entidades.', entregable: 'Diagrama dbdiagram.io' },
    { dia: 3, semana: 1, tema: 'DDL y Restricciones Estrictas', aula: 'Tablas, tipos de datos, PKs y FKs (CASCADE/RESTRICT).', reto: '5 constraints complejos (CHECK, UNIQUE condicional).', entregable: 'Script DDL ejecutable' },
    { dia: 4, semana: 1, tema: 'Poblado Masivo y Semillas', aula: 'Inserción masiva, secuencias e índices primarios.', reto: 'Script parametrizado de 500 registros coherentes.', entregable: 'Seeders en SQL' },
    { dia: 5, semana: 1, tema: 'Evaluación Hito 1 (25%)', aula: 'Revisión en vivo de esquemas y corrección de normalización.', reto: 'Push del repositorio final con el esquema validado.', entregable: 'Acta Hito 1 firmada' },
    { dia: 6, semana: 2, tema: 'DML y Filtros Avanzados', aula: 'UPDATE/DELETE condicionales, WHERE, LIKE, IN y BETWEEN.', reto: 'Batería de 15 sentencias de actualización segura.', entregable: 'Script DML limpio' },
    { dia: 7, semana: 2, tema: 'JOINS Multitabla', aula: 'INNER, LEFT, RIGHT y FULL JOIN con álgebra relacional.', reto: '10 consultas cruzadas sobre 4 tablas simultáneas.', entregable: 'Solución a las 10 consultas' },
    { dia: 8, semana: 2, tema: 'Agrupaciones y Ventana', aula: 'GROUP BY, HAVING, COUNT, SUM, AVG y Window Functions.', reto: 'Reporte académico/financiero con ROW_NUMBER y RANK.', entregable: 'Reporte analítico' },
    { dia: 9, semana: 2, tema: 'Subconsultas y CTEs', aula: 'Subconsultas correlacionadas, EXISTS y WITH (CTEs).', reto: 'Refactorización de queries anidados a CTEs optimizados.', entregable: 'Queries refactorizados' },
    { dia: 10, semana: 2, tema: 'Evaluación Hito 2 (25%)', aula: 'Taller práctico cronometrado: 5 requerimientos en 90 min.', reto: 'Corrección de observaciones y bitácora técnica.', entregable: 'Taller calificado' },
    { dia: 11, semana: 3, tema: 'Vistas e Índices Especiales', aula: 'Vistas estándar/materializadas e índices B-Tree/GIN.', reto: 'Benchmarking con EXPLAIN ANALYZE antes y después.', entregable: 'Informe de rendimiento' },
    { dia: 12, semana: 3, tema: 'Funciones en PL/pgSQL', aula: 'Estructuras de control (IF, LOOP), variables y SETOF.', reto: '2 funciones con validaciones y retorno tabular.', entregable: 'Funciones testeadas' },
    { dia: 13, semana: 3, tema: 'Triggers y Auditoría', aula: 'Disparadores BEFORE/AFTER, NEW/OLD y tabla de bitácora.', reto: 'Trigger de auditoría que registre todo cambio en la BD.', entregable: 'Trigger audit en acción' },
    { dia: 14, semana: 3, tema: 'Manejo de Errores y Seguridad', aula: 'BEGIN/EXCEPTION, RAISE EXCEPTION y prevención SQLi.', reto: 'Blindaje de procedimientos con validación estricta.', entregable: 'Funciones blindadas' },
    { dia: 15, semana: 3, tema: 'Evaluación Hito 3 (25%)', aula: 'Demostración en terminal de triggers, funciones y auditoría.', reto: 'Entrega del paquete procedimental consolidado.', entregable: 'Acta Hito 3 firmada' },
    { dia: 16, semana: 4, tema: 'Transacciones ACID y Bloqueos', aula: 'Aislamiento, BEGIN/COMMIT/ROLLBACK y FOR UPDATE.', reto: 'Simulación de concurrencia y prevención de deadlocks.', entregable: 'Script de concurrencia' },
    { dia: 17, semana: 4, tema: 'Respaldos y Migraciones', aula: 'pg_dump/pg_restore, backups incrementales y restauraciones.', reto: 'Script de backup automático y prueba de restauración.', entregable: 'Prueba de restore OK' },
    { dia: 18, semana: 4, tema: 'Integración con Aplicación', aula: 'Conexión ORM/Driver, pooling y prevención de N+1 queries.', reto: 'Prueba integral consumiendo la base de datos intensiva.', entregable: 'App conectada y testeada' },
    { dia: 19, semana: 4, tema: 'Pruebas de Carga y Ajustes', aula: 'Generación de 10.000 transacciones y monitoreo de memoria.', reto: 'Ajuste fino de configuración (work_mem, buffers).', entregable: 'Métricas de estrés' },
    { dia: 20, semana: 4, tema: 'Evaluación Hito 4: Defensa (25%)', aula: 'Presentación formal ante jurado y pruebas en caliente.', reto: 'Cierre de actas definitivas y entrega en Control de Estudios.', entregable: 'Calificación definitiva' },
];

// Matriz de Mitigación de Riesgos y Cuellos de Botella
const riesgosCuellosBotella = [
    {
        punto: 'Normalización Deficiente (Semana 1)',
        alerta: 'Tablas desbordadas de campos repetidos o claves foráneas inconsistentes que frenan el DDL.',
        solucion: 'Reunión clínica de 30 minutos el Día 3 con el docente para rediseñar el diagrama antes de escribir código.',
        nivel: 'Crítico',
    },
    {
        punto: 'Confusión en JOINS y Agrupaciones (Semana 2)',
        alerta: 'Generación de productos cartesianos descontrolados y duplicación de filas en cálculos.',
        solucion: 'Técnica de aislamiento: escribir primero el SELECT con filtros y verificar recuentos antes de aplicar el JOIN.',
        nivel: 'Alto',
    },
    {
        punto: 'Recursión y Bloqueos en Triggers (Semana 3)',
        alerta: 'Disparadores AFTER UPDATE que se disparan a sí mismos generando ciclos infinitos en el motor.',
        solucion: 'Aplicar patrones estandarizados con variables de control y encapsular la lógica en funciones aisladas.',
        nivel: 'Alto',
    },
    {
        punto: 'Deadlocks y Cuello de Botella Transaccional (Semana 4)',
        alerta: 'Procesos que quedan bloqueados esperando recursos retenidos por otra transacción abierta.',
        solucion: 'Establecer orden estricto de adquisición de bloqueos y configurar statement_timeout en las sesiones.',
        nivel: 'Medio',
    },
];

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestión y Chequeo de Cursos Intensivos - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <!-- ENCABEZADO PRINCIPAL -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-5 print:hidden">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">
                            <Flame class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                Cursos Intensivos: Plan Instruccional y Chequeo
                                <Badge variant="secondary" class="font-mono text-xs font-semibold bg-amber-500/15 text-amber-700 dark:text-amber-400">
                                    Modo Acelerado
                                </Badge>
                            </h1>
                            <p class="text-xs text-muted-foreground mt-0.5">
                                Planificación pedagógica de alta densidad (4 semanas), cronograma de 20 días y auditoría de secciones activas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES GLOBALES Y EXPORTACIÓN -->
                <div class="flex flex-wrap items-center gap-2">
                    <a :href="urlDescargaPdfGuia" target="_blank">
                        <Button variant="default" class="bg-primary text-primary-foreground shadow-xs gap-1.5 text-xs font-medium">
                            <Download class="h-3.5 w-3.5" />
                            Descargar Guía y Cronograma PDF
                        </Button>
                    </a>

                    <a :href="urlDescargaPdfAuditoria" target="_blank">
                        <Button variant="outline" size="sm" class="gap-1.5 text-xs font-medium">
                            <FileText class="h-3.5 w-3.5 text-emerald-600" />
                            Exportar Auditoría PDF
                        </Button>
                    </a>

                    <Button variant="ghost" size="sm" class="gap-1.5 text-xs font-medium" @click="imprimir">
                        <Printer class="h-3.5 w-3.5" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <!-- SELECTOR DE PESTAÑAS PRINCIPALES -->
            <div class="flex items-center gap-2 border-b border-border pb-2 print:hidden">
                <button
                    type="button"
                    class="px-4 py-2 text-xs font-bold rounded-lg transition-colors flex items-center gap-2"
                    :class="tabPrincipal === 'guia' ? 'bg-primary text-primary-foreground shadow-xs' : 'text-muted-foreground hover:bg-muted'"
                    @click="tabPrincipal = 'guia'"
                >
                    <Compass class="h-4 w-4" />
                    <span>Plan Instruccional & Guía Acelerada</span>
                </button>

                <button
                    type="button"
                    class="px-4 py-2 text-xs font-bold rounded-lg transition-colors flex items-center gap-2"
                    :class="tabPrincipal === 'secciones' ? 'bg-primary text-primary-foreground shadow-xs' : 'text-muted-foreground hover:bg-muted'"
                    @click="tabPrincipal = 'secciones'"
                >
                    <Layers class="h-4 w-4" />
                    <span>Auditoría de Secciones Intensivas</span>
                    <Badge variant="outline" class="ml-1 text-[10px] px-1.5 py-0 font-mono">
                        {{ props.secciones.total }}
                    </Badge>
                </button>
            </div>

            <!-- ========================================== -->
            <!-- PESTAÑA 1: PLAN INSTRUCCIONAL Y GUÍA       -->
            <!-- ========================================== -->
            <div v-if="tabPrincipal === 'guia'" class="space-y-6">
                <!-- BARRA DE CONFIGURACIÓN RÁPIDA / MATERIA -->
                <Card class="border shadow-xs bg-muted/20">
                    <CardContent class="p-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                        Asignatura del Curso Intensivo
                                    </h3>
                                    <p class="text-xs text-muted-foreground">
                                        Seleccione una unidad curricular para sincronizar su guía pedagógica y cronograma.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <select
                                    v-model="uncSeleccionadaId"
                                    class="h-9 px-3 py-1 text-xs rounded-md border border-input bg-background font-semibold focus:outline-none focus:ring-2 focus:ring-primary w-full md:w-80"
                                >
                                    <option v-for="u in props.unidadesCurriculares" :key="u.id_unc" :value="u.id_unc">
                                        {{ u.codigo_unc }} — {{ u.nombre_unc }}
                                    </option>
                                    <option v-if="!props.unidadesCurriculares || props.unidadesCurriculares.length === 0" value="">
                                        Bases de Datos / Programación Avanzada
                                    </option>
                                </select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- PARÁMETROS DEL CURSO (TARJETAS KPI) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <Card class="border shadow-2xs bg-card">
                        <CardContent class="p-3.5 space-y-1">
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Duración Total</span>
                                <Calendar class="h-4 w-4 text-blue-500" />
                            </div>
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100">
                                4 Semanas
                            </div>
                            <p class="text-[10px] text-muted-foreground">20 días hábiles continuos</p>
                        </CardContent>
                    </Card>

                    <Card class="border shadow-2xs bg-card">
                        <CardContent class="p-3.5 space-y-1">
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Carga Diaria</span>
                                <Clock class="h-4 w-4 text-emerald-500" />
                            </div>
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100">
                                5 Horas / Día
                            </div>
                            <p class="text-[10px] text-muted-foreground">3h de aula/lab + 2h reto autónomo</p>
                        </CardContent>
                    </Card>

                    <Card class="border shadow-2xs bg-card">
                        <CardContent class="p-3.5 space-y-1">
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Total Período</span>
                                <Activity class="h-4 w-4 text-amber-500" />
                            </div>
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100">
                                100 Horas
                            </div>
                            <p class="text-[10px] text-muted-foreground">Metodología acelerada High-Yield</p>
                        </CardContent>
                    </Card>

                    <Card class="border shadow-2xs bg-card">
                        <CardContent class="p-3.5 space-y-1">
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Evaluación</span>
                                <Target class="h-4 w-4 text-purple-500" />
                            </div>
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100">
                                4 Hitos (25% c/u)
                            </div>
                            <p class="text-[10px] text-muted-foreground">Evaluación práctica continua</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- SUB-PESTAÑAS DE CONTENIDO -->
                <div class="flex items-center gap-1 border-b border-border pb-1">
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors"
                        :class="tabSub === 'syllabus' ? 'bg-primary/10 text-primary border-b-2 border-primary' : 'text-muted-foreground hover:bg-muted'"
                        @click="tabSub = 'syllabus'"
                    >
                        1. Syllabus Comprimido (4 Semanas)
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors"
                        :class="tabSub === 'cronograma' ? 'bg-primary/10 text-primary border-b-2 border-primary' : 'text-muted-foreground hover:bg-muted'"
                        @click="tabSub = 'cronograma'"
                    >
                        2. Cronograma Diario (Día a Día)
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors"
                        :class="tabSub === 'evaluacion' ? 'bg-primary/10 text-primary border-b-2 border-primary' : 'text-muted-foreground hover:bg-muted'"
                        @click="tabSub = 'evaluacion'"
                    >
                        3. Estrategia Práctica y Rúbrica
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors"
                        :class="tabSub === 'riesgos' ? 'bg-primary/10 text-primary border-b-2 border-primary' : 'text-muted-foreground hover:bg-muted'"
                        @click="tabSub = 'riesgos'"
                    >
                        4. Mitigación de Cuellos de Botella
                    </button>
                </div>

                <!-- SUB-VISTA 1: SYLLABUS COMPRIMIDO -->
                <div v-if="tabSub === 'syllabus'" class="space-y-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-900 dark:text-blue-300 flex items-center gap-2">
                        <Sparkles class="h-4 w-4 shrink-0 text-blue-600" />
                        <span><strong>Filosofía High-Yield:</strong> Cada semana concentra un núcleo temático de alto impacto, eliminando la teoría estéril para enfocar al estudiante en la construcción del producto técnico.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Card
                            v-for="(sem, idx) in syllabusSemanas"
                            :key="idx"
                            class="border shadow-xs hover:border-primary/40 transition-all"
                        >
                            <CardHeader class="pb-2">
                                <div class="flex items-center justify-between">
                                    <Badge variant="outline" class="font-bold text-[11px] uppercase tracking-wider" :class="sem.color">
                                        {{ sem.semana }}
                                    </Badge>
                                    <Badge class="bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold border-amber-500/30 text-[10px]">
                                        {{ sem.hito }}
                                    </Badge>
                                </div>
                                <CardTitle class="text-sm font-bold mt-2">
                                    {{ sem.titulo }}
                                </CardTitle>
                                <CardDescription class="text-xs">
                                    {{ sem.enfoque }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3 pt-2 text-xs">
                                <div class="p-2.5 rounded-lg bg-muted/40 border border-border/50 space-y-1">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <Target class="h-3.5 w-3.5 text-primary" />
                                        <span>Entregable: {{ sem.hitoNombre }}</span>
                                    </div>
                                    <p class="text-[11px] text-muted-foreground leading-relaxed">
                                        {{ sem.descripcionHito }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-muted-foreground pt-1 border-t">
                                    <span>Aula / Laboratorio: <strong>{{ sem.horasAula }}h</strong></span>
                                    <span>Estudio Autónomo: <strong>{{ sem.horasAuto }}h</strong></span>
                                    <span>Total: <strong>25h</strong></span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- SUB-VISTA 2: CRONOGRAMA DIARIO -->
                <div v-if="tabSub === 'cronograma'" class="space-y-4">
                    <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                        <div class="p-3 bg-muted/50 border-b flex items-center justify-between">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Cronograma Detallado — 20 Días de Inmersión Acelerada
                            </h4>
                            <span class="text-xs text-muted-foreground">3h Sincrónica / Aula + 2h Reto Práctico</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-muted/20 border-b text-[10px] text-muted-foreground uppercase font-semibold">
                                    <tr>
                                        <th class="p-3 w-14 text-center">Día</th>
                                        <th class="p-3 w-48">Tema Central</th>
                                        <th class="p-3">Sesión en Aula (3 Horas)</th>
                                        <th class="p-3">Reto Autónomo Diario (2 Horas)</th>
                                        <th class="p-3 w-36">Entregable</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr
                                        v-for="d in cronogramaDias"
                                        :key="d.dia"
                                        class="hover:bg-muted/30"
                                        :class="d.dia % 5 === 0 ? 'bg-amber-500/5 font-medium' : ''"
                                    >
                                        <td class="p-3 text-center font-bold">
                                            <span
                                                class="inline-block px-1.5 py-0.5 rounded text-[11px]"
                                                :class="d.dia % 5 === 0 ? 'bg-amber-500 text-white font-black' : 'bg-muted text-muted-foreground'"
                                            >
                                                D{{ d.dia }}
                                            </span>
                                        </td>
                                        <td class="p-3 font-semibold text-slate-900 dark:text-slate-100">
                                            {{ d.tema }}
                                        </td>
                                        <td class="p-3 text-muted-foreground leading-relaxed">
                                            {{ d.aula }}
                                        </td>
                                        <td class="p-3 text-muted-foreground leading-relaxed">
                                            {{ d.reto }}
                                        </td>
                                        <td class="p-3">
                                            <Badge
                                                :variant="d.dia % 5 === 0 ? 'default' : 'outline'"
                                                class="text-[10px]"
                                                :class="d.dia % 5 === 0 ? 'bg-amber-600 hover:bg-amber-700' : ''"
                                            >
                                                {{ d.entregable }}
                                            </Badge>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SUB-VISTA 3: ESTRATEGIA PRÁCTICA Y RÚBRICA -->
                <div v-if="tabSub === 'evaluacion'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Card class="border shadow-xs">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-sm font-bold flex items-center gap-2">
                                    <Target class="h-4 w-4 text-emerald-600" />
                                    <span>Estructura de Micro-Retos Diarios (Checkpoints)</span>
                                </CardTitle>
                                <CardDescription class="text-xs">
                                    Mecanismo anti-rezago para asegurar que ningún estudiante acumule dudas de un día para otro.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <div class="p-1 bg-emerald-500/10 text-emerald-600 rounded mt-0.5">
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <div class="font-bold">Regla de las 2 Horas Diarias</div>
                                        <p class="text-muted-foreground text-[11px]">
                                            El reto autónomo no es tarea teórica; es la codificación o modelado directo de la solución planteada en la sesión matutina.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2.5">
                                    <div class="p-1 bg-emerald-500/10 text-emerald-600 rounded mt-0.5">
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <div class="font-bold">Check-in de Apertura (15 minutos)</div>
                                        <p class="text-muted-foreground text-[11px]">
                                            Cada clase inicia con la revisión relámpago de los retos autónomos del día anterior para depurar fallas en caliente.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2.5">
                                    <div class="p-1 bg-emerald-500/10 text-emerald-600 rounded mt-0.5">
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <div class="font-bold">Repositorio de Código Obligatorio</div>
                                        <p class="text-muted-foreground text-[11px]">
                                            Cada estudiante debe mantener su bitácora técnica con commits diarios. La inactividad de más de 2 días activa alerta docente.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="border shadow-xs">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-sm font-bold flex items-center gap-2">
                                    <GraduationCap class="h-4 w-4 text-purple-600" />
                                    <span>Ponderación Formal de Calificaciones</span>
                                </CardTitle>
                                <CardDescription class="text-xs">
                                    Distribución equitativa y acumulativa para garantizar dominio secuencial.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-2.5 text-xs">
                                <div class="p-2.5 border rounded-lg flex items-center justify-between bg-card">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Hito 1: Arquitectura y Esquema DDL</span>
                                        <p class="text-[10px] text-muted-foreground">Normalización en 3FN y restricciones de integridad</p>
                                    </div>
                                    <Badge class="bg-blue-600 font-bold">25 Pts</Badge>
                                </div>

                                <div class="p-2.5 border rounded-lg flex items-center justify-between bg-card">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Hito 2: Taller de Consultas Avanzadas</span>
                                        <p class="text-[10px] text-muted-foreground">JOINS, agregaciones, subqueries y vistas analíticas</p>
                                    </div>
                                    <Badge class="bg-emerald-600 font-bold">25 Pts</Badge>
                                </div>

                                <div class="p-2.5 border rounded-lg flex items-center justify-between bg-card">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Hito 3: Procedimientos y Triggers</span>
                                        <p class="text-[10px] text-muted-foreground">PL/pgSQL, bitácora de auditoría e indexación técnica</p>
                                    </div>
                                    <Badge class="bg-amber-600 font-bold">25 Pts</Badge>
                                </div>

                                <div class="p-2.5 border rounded-lg flex items-center justify-between bg-card">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Hito 4: Proyecto Final y Defensa Técnica</span>
                                        <p class="text-[10px] text-muted-foreground">Pruebas ACID, concurrencia, backups y pitch</p>
                                    </div>
                                    <Badge class="bg-purple-600 font-bold">25 Pts</Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- SUB-VISTA 4: MITIGACIÓN DE RIESGOS -->
                <div v-if="tabSub === 'riesgos'" class="space-y-4">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-lg text-xs text-rose-900 dark:text-rose-300 flex items-center gap-2">
                        <ShieldAlert class="h-4 w-4 shrink-0 text-rose-600" />
                        <span><strong>Alerta de Rezago:</strong> En el régimen intensivo de 5 horas diarias, faltar a 2 clases equivale a perder el 10% del período. La intervención debe ocurrir dentro de las primeras 24 horas.</span>
                    </div>

                    <div class="space-y-3">
                        <Card
                            v-for="(r, idx) in riesgosCuellosBotella"
                            :key="idx"
                            class="border shadow-xs"
                        >
                            <CardContent class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 dark:text-slate-100">
                                        <AlertTriangle class="h-4 w-4 text-amber-500" />
                                        <span>{{ r.punto }}</span>
                                    </div>
                                    <Badge
                                        :variant="r.nivel === 'Crítico' ? 'destructive' : 'secondary'"
                                        class="text-[10px] font-bold"
                                    >
                                        {{ r.nivel }}
                                    </Badge>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs pt-1">
                                    <div class="p-2.5 rounded-lg bg-destructive/5 border border-destructive/20 text-muted-foreground">
                                        <strong class="text-destructive block mb-0.5">Síntoma Típico de Rezago:</strong>
                                        {{ r.alerta }}
                                    </div>

                                    <div class="p-2.5 rounded-lg bg-emerald-500/5 border border-emerald-500/20 text-muted-foreground">
                                        <strong class="text-emerald-700 dark:text-emerald-400 block mb-0.5">Protocolo Inmediato de Rescate:</strong>
                                        {{ r.solucion }}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- PESTAÑA 2: AUDITORÍA DE SECCIONES         -->
            <!-- ========================================== -->
            <div v-if="tabPrincipal === 'secciones'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            Secciones Registradas en Modalidad Intensiva
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Control de cupos, docentes asignados y estatus operativo en el período especial.
                        </p>
                    </div>

                    <Button as-child size="sm" variant="outline" class="text-xs font-medium gap-1.5">
                        <Link :href="route('seccion.create')">
                            <Layers class="h-3.5 w-3.5 text-primary" />
                            Nueva Sección Intensiva
                        </Link>
                    </Button>
                </div>

                <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-muted/50 border-b text-xs font-semibold text-muted-foreground uppercase">
                            <tr>
                                <th class="p-4">Sección Intensiva</th>
                                <th class="p-4">Malla Curricular / PNF</th>
                                <th class="p-4">Unidades Curriculares Asignadas</th>
                                <th class="p-4 text-center">Capacidad</th>
                                <th class="p-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="sec in props.secciones.data" :key="sec.id_sec" class="hover:bg-muted/30">
                                <td class="p-4 font-medium">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ sec.nombre_sec }}</div>
                                    <div class="text-xs text-muted-foreground font-mono">{{ sec.codigo_sec || 'S/C' }}</div>
                                </td>
                                <td class="p-4 text-xs text-muted-foreground">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ sec.malla_curricular?.nombre_mac || 'Sin Malla Asignada' }}
                                    </div>
                                    <div class="text-[11px] text-muted-foreground">
                                        {{ sec.malla_curricular?.pnf?.nombre_pnf || 'PNF General' }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="ud in sec.unidades_docentes"
                                            :key="ud.id_sud"
                                            variant="outline"
                                            class="text-[11px] bg-background"
                                        >
                                            {{ ud.malla_curricular_detalle?.unidad_curricular?.nombre_unc || 'Materia' }}
                                            <span v-if="ud.docente" class="text-muted-foreground ml-1">
                                                ({{ ud.docente.name }})
                                            </span>
                                        </Badge>
                                        <span v-if="!sec.unidades_docentes || sec.unidades_docentes.length === 0" class="text-xs text-muted-foreground italic">
                                            Sin materias asignadas
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center font-mono font-semibold">
                                    {{ sec.cupo_maximo_sec || 30 }}
                                </td>
                                <td class="p-4 text-center">
                                    <Badge :variant="sec.status ? 'default' : 'secondary'">
                                        {{ sec.status ? 'Abierta' : 'Cerrada' }}
                                    </Badge>
                                </td>
                            </tr>
                            <tr v-if="props.secciones.data.length === 0">
                                <td colspan="5" class="p-12 text-center text-muted-foreground text-sm space-y-2">
                                    <Layers class="h-10 w-10 mx-auto text-muted-foreground/50 mb-2" />
                                    <div class="font-semibold text-slate-700 dark:text-slate-300">
                                        No hay secciones intensivas activas en este momento
                                    </div>
                                    <p class="text-xs max-w-md mx-auto">
                                        Para registrar una nueva sección intensiva, diríjase a Programación Académica y cree una sección asignando el tipo de sección <strong>"Intensivo"</strong>.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
