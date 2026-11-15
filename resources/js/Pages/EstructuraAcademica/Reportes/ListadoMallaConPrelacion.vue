<script setup lang="ts">
/**
 * Reporte: Listado De Malla Con Prelación.
 *
 * Módulo: Estructura Académica / Reportes.
 * Propósito: Presenta el árbol de prelaciones curriculares y dependencias académicas
 * entre asignaturas por trayecto, identificando materias requisitos y materias subsiguientes.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    GitFork,
    Printer,
    ArrowLeft,
    GraduationCap,
    CheckCircle2,
    AlertCircle,
    ArrowRight,
    Layers,
    FileSpreadsheet,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';

interface MallaItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac?: string;
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
    filters: {
        malla_id?: string | number;
    };
}>();

const mallaId = ref(props.mallaSeleccionada?.id_mac ?? '');

const cambiarMalla = () => {
    router.get(route('academico.reportes.listado-malla-prelacion'), {
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
    <Head title="Reporte: Malla Curricular con Prelaciones" />

    <AppLayout :breadcrumbs="[
        { title: 'Estructura Académica', href: route('academico.malla-curricular.index') },
        { title: 'Reportes', href: route('academico.reportes.listado-malla-prelacion') },
        { title: 'Listado De Malla Con Prelación', href: route('academico.reportes.listado-malla-prelacion') },
    ]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:border-none">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-600 dark:text-indigo-400 print:hidden">
                            <GitFork class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                Reporte: Listado De Malla Con Prelación
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Mapa de dependencias y prelaciones normativas entre unidades curriculares.
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
                        :href="route('academico.reportes.listado-malla-prelacion.excel', { malla_id: mallaId })"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 gap-1.5 cursor-pointer"
                        download
                    >
                        <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
                        <span>Excel</span>
                    </a>

                    <a
                        :href="route('academico.reportes.listado-malla-prelacion.pdf', { malla_id: mallaId })"
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

            <!-- FILTRO DE MALLA -->
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
                            {{ m.nombre_mac }} ({{ m.pnf?.nombre_pnf ?? 'Sin PNF' }})
                        </option>
                    </select>
                </div>

                <div v-if="props.mallaSeleccionada" class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span>PNF: <strong class="text-foreground">{{ props.mallaSeleccionada.pnf?.nombre_pnf }}</strong></span>
                    <span>Gaceta: <strong class="text-foreground">{{ props.mallaSeleccionada.codigo_gaceta_mac || 'S/N' }}</strong></span>
                </div>
            </div>

            <!-- ENCABEZADO INSTITUCIONAL PARA IMPRESIÓN -->
            <div class="hidden print:block text-center border-b pb-4 mb-4">
                <h2 class="text-lg font-bold uppercase tracking-wider">Universidad Politécnica Territorial del Estado Portuguesa</h2>
                <h3 class="text-md font-semibold text-slate-700">Sistema Oficial de Gestión Académica y Control de Estudios (SOGAC)</h3>
                <p class="text-xs text-slate-500 mt-1">SISTEMA Y MATRIZ DE PRELACIONES POR UNIDAD CURRICULAR</p>
            </div>

            <!-- LISTADO CON PRELACIONES POR TRAYECTO -->
            <div v-if="props.mallaSeleccionada" class="space-y-6">
                <div
                    v-for="(asignaturas, trayecto) in props.detallesPorTrayecto"
                    :key="trayecto"
                    class="bg-card border rounded-xl shadow-xs overflow-hidden"
                >
                    <div class="bg-muted/60 px-5 py-3 border-b flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            <h3 class="font-bold text-slate-900 dark:text-slate-100">
                                {{ trayecto }}
                            </h3>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground">
                            {{ asignaturas.length }} materias en este trayecto
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-muted/20 border-b text-muted-foreground">
                                <tr>
                                    <th scope="col" class="px-4 py-3 w-14 text-center">Nro.</th>
                                    <th scope="col" class="px-4 py-3 w-28 text-center">Código</th>
                                    <th scope="col" class="px-4 py-3 w-64">Unidad Curricular</th>
                                    <th scope="col" class="px-4 py-3 w-20 text-center">UC</th>
                                    <th scope="col" class="px-4 py-3">Materia(s) Requisito (Prela Por)</th>
                                    <th scope="col" class="px-4 py-3">Habilita Para (Prela A)</th>
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
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ d.unidad_curricular?.nombre_unc || 'Sin Nombre' }}
                                        </div>
                                        <div class="text-[11px] text-muted-foreground">
                                            {{ d.unidad_curricular?.tipo_unidad_curricular?.nombre_tuc || 'Obligatoria' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ d.unidades_credito_mcd }}
                                    </td>

                                    <!-- MATERIAS REQUISITO QUE DEBE APROBAR ANTES -->
                                    <td class="px-4 py-3">
                                        <div v-if="(d.prelaciones || []).length > 0" class="flex flex-wrap gap-1.5">
                                            <span
                                                v-for="pre in d.prelaciones"
                                                :key="pre.id_ucp"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50"
                                            >
                                                <AlertCircle class="h-3 w-3 shrink-0" />
                                                <strong class="font-mono">{{ pre.detalle_prelacion?.unidad_curricular?.codigo_unc }}</strong>
                                                <span>{{ pre.detalle_prelacion?.unidad_curricular?.nombre_unc }}</span>
                                            </span>
                                        </div>
                                        <span v-else class="inline-flex items-center text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                            <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                                            Sin Prelación (Libre Cursado)
                                        </span>
                                    </td>

                                    <!-- MATERIAS QUE HABILITA -->
                                    <td class="px-4 py-3">
                                        <div v-if="(d.es_requisito_de || []).length > 0" class="flex flex-wrap gap-1.5">
                                            <span
                                                v-for="hab in d.es_requisito_de"
                                                :key="hab.id_ucp"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50"
                                            >
                                                <ArrowRight class="h-3 w-3 shrink-0" />
                                                <strong class="font-mono">{{ hab.detalle_malla?.unidad_curricular?.codigo_unc }}</strong>
                                                <span>{{ hab.detalle_malla?.unidad_curricular?.nombre_unc }}</span>
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-muted-foreground italic">
                                            Fin de línea formativa
                                        </span>
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

