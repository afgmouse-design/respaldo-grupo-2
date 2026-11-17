<script setup lang="ts">
/**
 * Reporte: Listado De Malla Curricular.
 *
 * Módulo: Estructura Académica / Reportes.
 * Propósito: Muestra el plan de estudios completo de una Malla Curricular seleccionada,
 * desglosado cronológicamente por trayectos con código, unidad curricular, tipo, horas y unidades de crédito.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    FileText,
    Printer,
    ArrowLeft,
    GraduationCap,
    Clock,
    BookOpen,
    Layers,
    Award,
    CheckCircle2,
    FileSpreadsheet,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';

interface MallaItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac?: string;
    fecha_aprobacion_mac?: string;
    programa?: {
        id_prg: number;
        nombre_prg: string;
        codigo_prg?: string;
        codigo_ministerio_prg?: string;
    };
    pnf?: {
        id_pnf: number;
        nombre_pnf: string;
        codigo_pnf: string;
    };
}

const props = defineProps<{
    mallas: MallaItem[];
    mallaSeleccionada: any;
    detallesPorTrayecto: Record<string, any[]>;
    totales: {
        unidades: number;
        creditos: number;
        htea: number;
        htei: number;
        thte: number;
    };
    filters: {
        malla_id?: string | number;
    };
}>();

const mallaId = ref(props.mallaSeleccionada?.id_mac ?? '');

const cambiarMalla = () => {
    router.get(route('academico.reportes.listado-malla'), {
        malla_id: mallaId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <Head title="Reporte: Listado de Malla Curricular" />

    <AppLayout :breadcrumbs="[
        { title: 'Estructura Académica', href: route('academico.malla-curricular.index') },
        { title: 'Reportes', href: route('academico.reportes.listado-malla') },
        { title: 'Listado De Malla', href: route('academico.reportes.listado-malla') },
    ]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- CABECERA Y ACCIONES -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:border-none">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary print:hidden">
                            <FileText class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                Reporte: Listado De Malla Curricular
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Diseño curricular oficial estructurado por trayectos, unidades de crédito y carga horaria.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <Button variant="outline" size="sm" as-child class="h-9 gap-1.5">
                        <Link :href="route('academico.malla-curricular.index')">
                            <ArrowLeft class="h-4 w-4" />
                            <span>Volver a Mallas</span>
                        </Link>
                    </Button>

                    <a
                        :href="route('academico.reportes.listado-malla.excel', { malla_id: mallaId })"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 gap-1.5 cursor-pointer"
                        download
                    >
                        <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
                        <span>Excel</span>
                    </a>

                    <a
                        :href="route('academico.reportes.listado-malla.pdf', { malla_id: mallaId })"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 gap-1.5 cursor-pointer"
                        target="_blank"
                    >
                        <FileText class="h-4 w-4 text-rose-600" />
                        <span>Descargar PDF</span>
                    </a>

                    <Button variant="outline" size="sm" class="h-9 gap-1.5 cursor-pointer" @click="imprimir">
                        <Printer class="h-4 w-4" />
                        <span>Imprimir</span>
                    </Button>
                </div>
            </div>

            <!-- FILTRO DE MALLA CURRICULAR -->
            <div class="bg-card border rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-sm font-semibold text-foreground whitespace-nowrap">
                        Seleccionar Malla / Pensum:
                    </label>
                    <select
                        v-model="mallaId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary w-full sm:w-80"
                        @change="cambiarMalla"
                    >
                        <option v-for="m in props.mallas" :key="m.id_mac" :value="m.id_mac">
                            {{ m.nombre_mac }} ({{ m.programa?.nombre_prg ?? m.pnf?.nombre_pnf ?? 'Sin Programa' }})
                        </option>
                    </select>
                </div>

                <div v-if="props.mallaSeleccionada" class="text-xs text-muted-foreground">
                    Gaceta: <strong class="text-foreground">{{ props.mallaSeleccionada.codigo_gaceta_mac || 'S/N' }}</strong>
                </div>
            </div>

            <!-- ENCABEZADO INSTITUCIONAL PARA IMPRESIÓN -->
            <div class="hidden print:block text-center border-b pb-4 mb-4">
                <h2 class="text-lg font-bold uppercase tracking-wider">Universidad Politécnica Territorial del Estado Portuguesa</h2>
                <h3 class="text-md font-semibold text-slate-700">Sistema Oficial de Gestión Académica y Control de Estudios (SOGAC)</h3>
                <p class="text-xs text-slate-500 mt-1">PLAN DE ESTUDIOS Y LISTADO DE MALLA CURRICULAR</p>
            </div>

            <!-- TARJETA INFORMATIVA DE LA MALLA SELECCIONADA -->
            <div v-if="props.mallaSeleccionada" class="bg-card border rounded-xl p-5 shadow-xs space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b pb-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-primary">Programa Nacional de Formación</span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 uppercase">
                            {{ props.mallaSeleccionada.programa?.nombre_prg || props.mallaSeleccionada.pnf?.nombre_pnf || props.mallaSeleccionada.nombre_mac }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="font-mono text-xs">
                            Cód. Programa: {{ props.mallaSeleccionada.programa?.codigo_prg || props.mallaSeleccionada.programa?.codigo_ministerio_prg || props.mallaSeleccionada.pnf?.codigo_pnf || 'N/A' }}
                        </Badge>
                        <Badge variant="secondary" class="text-xs">
                            Gaceta: {{ props.mallaSeleccionada.codigo_gaceta_mac || 'Aprobada' }}
                        </Badge>
                    </div>
                </div>

                <!-- RESUMEN DE TOTALES -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1">
                    <div class="p-3 rounded-lg bg-muted/40 border text-center">
                        <div class="text-xs text-muted-foreground">Total Materias</div>
                        <div class="text-xl font-bold text-primary">{{ props.totales.unidades }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-muted/40 border text-center">
                        <div class="text-xs text-muted-foreground">Total Créditos (UC)</div>
                        <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ props.totales.creditos }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-muted/40 border text-center">
                        <div class="text-xs text-muted-foreground">Horas Asistidas (HTEA)</div>
                        <div class="text-xl font-bold text-foreground">{{ props.totales.htea }} hrs</div>
                    </div>
                    <div class="p-3 rounded-lg bg-muted/40 border text-center">
                        <div class="text-xs text-muted-foreground">Horas Autónomas (HTEI)</div>
                        <div class="text-xl font-bold text-foreground">{{ props.totales.htei }} hrs</div>
                    </div>
                    <div class="p-3 rounded-lg bg-muted/40 border text-center col-span-2 sm:col-span-1">
                        <div class="text-xs text-muted-foreground">Total Horas (THTE)</div>
                        <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ props.totales.thte }} hrs</div>
                    </div>
                </div>
            </div>

            <!-- LISTADO DE ASIGNATURAS POR TRAYECTO -->
            <div v-if="props.mallaSeleccionada" class="space-y-6">
                <div
                    v-for="(asignaturas, trayecto) in props.detallesPorTrayecto"
                    :key="trayecto"
                    class="bg-card border rounded-xl shadow-xs overflow-hidden"
                >
                    <div class="bg-muted/60 px-5 py-3 border-b flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-primary" />
                            <h3 class="font-bold text-slate-900 dark:text-slate-100">
                                {{ trayecto }}
                            </h3>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground">
                            {{ asignaturas.length }} {{ asignaturas.length === 1 ? 'materia' : 'materias' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-muted/20 border-b text-muted-foreground">
                                <tr>
                                    <th scope="col" class="px-4 py-3 w-16 text-center">Nro.</th>
                                    <th scope="col" class="px-4 py-3 w-28 text-center">Código</th>
                                    <th scope="col" class="px-4 py-3">Unidad Curricular</th>
                                    <th scope="col" class="px-4 py-3 w-36 text-center">Tipo</th>
                                    <th scope="col" class="px-4 py-3 w-40">Eje Curricular</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center">UC</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center">HTEA</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center">HTEI</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center font-bold">THTE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="(d, idx) in asignaturas"
                                    :key="d.id_mcd"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-4 py-3 text-center text-xs font-mono text-muted-foreground">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Badge variant="secondary" class="font-mono text-xs font-bold">
                                            {{ d.unidad_curricular?.codigo_unc || 'S/C' }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                        {{ d.unidad_curricular?.nombre_unc || 'Sin Nombre' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Badge variant="outline" class="text-xs">
                                            {{ d.unidad_curricular?.tipo_unidad_curricular?.nombre_tuc || 'Obligatoria' }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ d.eje_curricular?.nombre_ecu || 'General' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ d.unidades_credito_mcd }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                        {{ d.htea_mcd }}h
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                        {{ d.htei_mcd }}h
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs font-bold text-foreground">
                                        {{ d.thte_mcd }}h
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-else class="bg-card border rounded-xl p-10 text-center text-muted-foreground">
                No se encontraron mallas curriculares registradas.
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    body {
        background: white !important;
        color: black !important;
    }
}
</style>

