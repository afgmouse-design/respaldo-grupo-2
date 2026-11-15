<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Ficha técnica y de consulta completa de una Malla Curricular registrada,
 * mostrando datos del plan de estudio, PNF asociado, resolución ministerial y unidades curriculares.
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Network,
    ArrowLeft,
    Pencil,
    GraduationCap,
    FileText,
    Calendar,
    BookOpen,
    Layers,
    Clock,
    Award,
    CheckCircle2,
    XCircle,
    Info,
} from 'lucide-vue-next';

interface PnfItem {
    id_pnf: number;
    codigo_pnf?: string | null;
    nombre_pnf: string;
    status?: boolean;
}

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc?: string | null;
    nombre_unc: string;
}

interface TrayectoItem {
    id_tra: number;
    nombre_tra: string;
    codigo_tra?: string | null;
}

interface EjeCurricularItem {
    id_ecu: number;
    nombre_ecu: string;
}

interface EscalaMedicionItem {
    id_esm: number;
    nombre_esm: string;
}

interface MallaCurricularDetalleItem {
    id_mcd: number;
    unidades_credito_mcd?: number | null;
    htea_mcd?: number | null;
    htei_mcd?: number | null;
    thte_mcd?: number | null;
    duracion_semanas_mcd?: number | null;
    unidad_curricular?: UnidadCurricularItem | null;
    trayecto?: TrayectoItem | null;
    eje_curricular?: EjeCurricularItem | null;
    escala_medicion?: EscalaMedicionItem | null;
    status?: boolean;
    status_id?: number;
}

interface MallaCurricularItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac: string | null;
    version_pensum_mac?: string | null;
    fecha_aprobacion_mac: string | null;
    observacion_mac: string | null;
    pnf_id: number;
    creditos_minimos_tsu_mac?: number | null;
    horas_minimas_tsu_mac?: number | null;
    materias_obligatorias_tsu_mac?: number | null;
    creditos_minimos_ing_mac?: number | null;
    horas_minimas_ing_mac?: number | null;
    materias_obligatorias_ing_mac?: number | null;
    creditos_minimos_prosecucion_mac?: number | null;
    pnf?: PnfItem | null;
    detalles?: MallaCurricularDetalleItem[] | null;
    status_id?: number;
    status?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    malla: MallaCurricularItem;
}>();

const esActivo = computed(() => {
    return props.malla.status ?? (props.malla.status_id === 1);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Mallas Curriculares', subtitle: 'Planes de Estudio', href: route('academico.malla-curricular.index') },
    { title: `Ficha: ${props.malla.nombre_mac}`, subtitle: 'Detalle', href: route('academico.malla-curricular.show', props.malla.id_mac) },
];

const totalCreditos = computed(() => {
    if (!props.malla.detalles) return 0;
    return props.malla.detalles.reduce((acc, det) => acc + (Number(det.unidades_credito_mcd) || 0), 0);
});

const totalHoras = computed(() => {
    if (!props.malla.detalles) return 0;
    return props.malla.detalles.reduce((acc, det) => acc + (Number(det.thte_mcd) || 0), 0);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Malla: ${malla.nombre_mac} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO SUPERIOR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.malla-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Malla Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle del plan de estudios y diseño curricular ministerial.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.malla-curricular.edit', malla.id_mac)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Malla</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHA PRINCIPAL -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-5xl mx-auto">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                                <Network class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ malla.nombre_mac }}
                                </CardTitle>
                                <CardDescription>Código de registro: #{{ malla.id_mac }}</CardDescription>
                            </div>
                        </div>

                        <Badge :variant="esActivo ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3 w-fit">
                            <CheckCircle2 v-if="esActivo" class="h-3.5 w-3.5 text-emerald-500" />
                            <XCircle v-else class="h-3.5 w-3.5 text-slate-400" />
                            {{ esActivo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- DATOS BÁSICOS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Network class="h-3.5 w-3.5" /> Denominación del Plan
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ malla.nombre_mac }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <GraduationCap class="h-3.5 w-3.5" /> Programa (PNF) Asignado
                            </span>
                            <p v-if="malla.pnf" class="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span>{{ malla.pnf.nombre_pnf }}</span>
                                <span v-if="malla.pnf.codigo_pnf" class="text-xs font-mono px-2 py-0.5 rounded bg-muted text-muted-foreground">
                                    {{ malla.pnf.codigo_pnf }}
                                </span>
                            </p>
                            <p v-else class="text-sm italic text-muted-foreground">
                                Sin PNF asignado
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="h-3.5 w-3.5" /> Gaceta Oficial / Resolución
                            </span>
                            <p class="text-base font-mono text-slate-900 dark:text-slate-100">
                                {{ malla.codigo_gaceta_mac || 'No especificada' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5" /> Fecha de Aprobación
                            </span>
                            <p class="text-base text-slate-900 dark:text-slate-100">
                                {{ malla.fecha_aprobacion_mac || 'No registrada' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Award class="h-3.5 w-3.5" /> Versión o Código del Pensum
                            </span>
                            <p class="text-base font-semibold text-primary">
                                {{ malla.version_pensum_mac || 'No asignada' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5" /> Duración General Estimada
                            </span>
                            <p class="text-base text-slate-900 dark:text-slate-100">
                                4 Trayectos Conducentes (8 Semestres)
                            </p>
                        </div>

                        <div class="md:col-span-2 space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Info class="h-3.5 w-3.5" /> Observaciones
                            </span>
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                {{ malla.observacion_mac || 'Sin observaciones registradas.' }}
                            </p>
                        </div>
                    </div>

                    <!-- REQUISITOS GLOBALES DE EGRESO (TSU vs INGENIERÍA) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- TSU -->
                        <div class="p-4 rounded-xl border border-amber-200 bg-amber-500/5 dark:border-amber-900/40 space-y-3">
                            <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
                                <Award class="h-5 w-5" />
                                <h4 class="font-bold text-sm uppercase tracking-wider">Requisitos Salida Intermedia (TSU)</h4>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center pt-1">
                                <div class="bg-background/80 p-2.5 rounded-lg border">
                                    <div class="text-[11px] text-muted-foreground">Créditos (UC)</div>
                                    <div class="text-lg font-bold text-amber-600 dark:text-amber-400">
                                        {{ malla.creditos_minimos_tsu_mac || 96 }}
                                    </div>
                                </div>
                                <div class="bg-background/80 p-2.5 rounded-lg border">
                                    <div class="text-[11px] text-muted-foreground">Horas Totales</div>
                                    <div class="text-lg font-bold text-foreground">
                                        {{ malla.horas_minimas_tsu_mac || 2880 }}h
                                    </div>
                                </div>
                                <div class="bg-background/80 p-2.5 rounded-lg border">
                                    <div class="text-[11px] text-muted-foreground">Materias Oblig.</div>
                                    <div class="text-lg font-bold text-foreground">
                                        {{ malla.materias_obligatorias_tsu_mac || 20 }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INGENIERÍA -->
                        <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-500/5 dark:border-indigo-900/40 space-y-3">
                            <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400">
                                <GraduationCap class="h-5 w-5" />
                                <h4 class="font-bold text-sm uppercase tracking-wider">Requisitos Título Final (Ingeniería)</h4>
                            </div>
                            <div class="grid grid-cols-4 gap-2 text-center pt-1">
                                <div class="bg-background/80 p-2 rounded-lg border">
                                    <div class="text-[10px] text-muted-foreground">Total UC</div>
                                    <div class="text-base font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ malla.creditos_minimos_ing_mac || 180 }}
                                    </div>
                                </div>
                                <div class="bg-background/80 p-2 rounded-lg border">
                                    <div class="text-[10px] text-muted-foreground">Horas Totales</div>
                                    <div class="text-base font-bold text-foreground">
                                        {{ malla.horas_minimas_ing_mac || 5400 }}h
                                    </div>
                                </div>
                                <div class="bg-background/80 p-2 rounded-lg border">
                                    <div class="text-[10px] text-muted-foreground">Materias</div>
                                    <div class="text-base font-bold text-foreground">
                                        {{ malla.materias_obligatorias_ing_mac || 38 }}
                                    </div>
                                </div>
                                <div class="bg-background/80 p-2 rounded-lg border">
                                    <div class="text-[10px] text-muted-foreground">Prosecución</div>
                                    <div class="text-base font-bold text-foreground">
                                        {{ malla.creditos_minimos_prosecucion_mac || 84 }} UC
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN CURRICULAR / MATERIAS ASOCIADAS -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5 text-primary" />
                                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                    Unidades Curriculares del Plan de Estudio
                                </h3>
                            </div>
                            <div v-if="malla.detalles && malla.detalles.length > 0" class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md">
                                    <Layers class="h-3.5 w-3.5 text-primary" /> {{ malla.detalles.length }} materias
                                </span>
                                <span class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md">
                                    <Award class="h-3.5 w-3.5 text-amber-500" /> {{ totalCreditos }} UC Totales
                                </span>
                                <span class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md">
                                    <Clock class="h-3.5 w-3.5 text-blue-500" /> {{ totalHoras }} Horas Totales
                                </span>
                            </div>
                        </div>

                        <!-- TABLA DE DETALLES -->
                        <div v-if="malla.detalles && malla.detalles.length > 0" class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5">Código / Materia</th>
                                        <th class="px-4 py-2.5">Trayecto</th>
                                        <th class="px-4 py-2.5">Eje Curricular</th>
                                        <th class="px-4 py-2.5 text-center">UC</th>
                                        <th class="px-4 py-2.5 text-center">Horas</th>
                                        <th class="px-4 py-2.5 text-center">Escala</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="det in malla.detalles" :key="det.id_mcd" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="px-4 py-2.5">
                                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ det.unidad_curricular?.nombre_unc || 'N/A' }}
                                            </div>
                                            <div v-if="det.unidad_curricular?.codigo_unc" class="text-[11px] font-mono text-muted-foreground">
                                                {{ det.unidad_curricular.codigo_unc }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">
                                            {{ det.trayecto?.nombre_tra || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">
                                            {{ det.eje_curricular?.nombre_ecu || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-semibold text-slate-900 dark:text-slate-100">
                                            {{ det.unidades_credito_mcd ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-slate-700 dark:text-slate-300">
                                            {{ det.thte_mcd ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center text-slate-700 dark:text-slate-300">
                                            {{ det.escala_medicion?.nombre_esm || 'N/A' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ESTADO VACÍO -->
                        <div v-else class="text-center py-8 px-4 border border-dashed rounded-lg border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <BookOpen class="h-8 w-8 text-muted-foreground mx-auto mb-2 opacity-50" />
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                No hay materias cargadas en esta malla curricular
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                Las materias y unidades curriculares pueden vincularse a través del submódulo Detalle de Malla.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

