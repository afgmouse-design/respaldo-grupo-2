<script setup lang="ts">
/**
 * Reporte: Relación De Unidades Curriculares por Malla y PNF.
 *
 * Módulo: Estructura Académica / Reportes.
 * Propósito: Listado detallado de asignaturas por plan de estudios, filtrables por malla y trayecto,
 * con desglose de parámetros pedagógicos, cargas horarias y créditos académicos.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Table,
    Printer,
    ArrowLeft,
    Search,
    X,
    Clock,
    BookOpen,
    FileSpreadsheet,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import DataPagination from '@/components/ui/DataPagination.vue';

interface MallaOpt { id_mac: number; nombre_mac: string; pnf?: { nombre_pnf: string } }
interface TrayectoOpt { id_tra: number; nombre_tra: string }

const props = defineProps<{
    detalles: any;
    mallas: MallaOpt[];
    trayectos: TrayectoOpt[];
    filters: {
        malla_id?: string | number;
        tra_id?: string | number;
        search?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const mallaId = ref(props.filters.malla_id ?? 'all');
const traId = ref(props.filters.tra_id ?? 'all');

const aplicarFiltros = () => {
    router.get(route('academico.reportes.relacion-unidades'), {
        search: search.value || undefined,
        malla_id: mallaId.value !== 'all' ? mallaId.value : undefined,
        tra_id: traId.value !== 'all' ? traId.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    search.value = '';
    mallaId.value = 'all';
    traId.value = 'all';
    aplicarFiltros();
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <Head title="Reporte: Relación de Unidades Curriculares" />

    <AppLayout :breadcrumbs="[
        { title: 'Estructura Académica', href: route('academico.malla-curricular.index') },
        { title: 'Reportes', href: route('academico.reportes.relacion-unidades') },
        { title: 'Relación De Unidades', href: route('academico.reportes.relacion-unidades') },
    ]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:border-none">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-600 dark:text-emerald-400 print:hidden">
                            <Table class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                Reporte: Relación De Unidades Curriculares
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Parámetros académicos, horas y unidades de crédito por diseño curricular.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <Button variant="outline" size="sm" as-child class="h-9 gap-1.5">
                        <Link :href="route('academico.unidad-curricular.index')">
                            <ArrowLeft class="h-4 w-4" />
                            <span>Unidades Curriculares</span>
                        </Link>
                    </Button>

                    <a
                        :href="route('academico.reportes.relacion-unidades.excel', { malla_id: mallaId, tra_id: traId, search: search })"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 gap-1.5 cursor-pointer"
                        download
                    >
                        <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
                        <span>Excel</span>
                    </a>

                    <a
                        :href="route('academico.reportes.relacion-unidades.pdf', { malla_id: mallaId })"
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

            <!-- BARRA DE FILTROS -->
            <div class="bg-card border rounded-xl p-4 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 print:hidden">
                <div class="flex-1 relative">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por código o nombre de unidad curricular..."
                        class="w-full h-9 pl-9 pr-3 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @keyup.enter="aplicarFiltros"
                    />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                    <select
                        v-model="mallaId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="aplicarFiltros"
                    >
                        <option value="all">Todas las mallas</option>
                        <option v-for="m in props.mallas" :key="m.id_mac" :value="m.id_mac">
                            {{ m.nombre_mac }} ({{ m.pnf?.nombre_pnf ?? 'Sin PNF' }})
                        </option>
                    </select>

                    <select
                        v-model="traId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="aplicarFiltros"
                    >
                        <option value="all">Todos los trayectos</option>
                        <option v-for="t in props.trayectos" :key="t.id_tra" :value="t.id_tra">
                            {{ t.nombre_tra }}
                        </option>
                    </select>

                    <Button
                        v-if="search || mallaId !== 'all' || traId !== 'all'"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        title="Limpiar filtros"
                        @click="limpiarFiltros"
                    >
                        <X class="h-4 w-4" />
                    </Button>

                    <Button size="sm" class="h-9 px-4 shrink-0" @click="aplicarFiltros">
                        Filtrar
                    </Button>
                </div>
            </div>

            <!-- ENCABEZADO INSTITUCIONAL PARA IMPRESIÓN -->
            <div class="hidden print:block text-center border-b pb-4 mb-4">
                <h2 class="text-lg font-bold uppercase tracking-wider">Universidad Politécnica Territorial del Estado Portuguesa</h2>
                <h3 class="text-md font-semibold text-slate-700">Sistema Oficial de Gestión Académica y Control de Estudios (SOGAC)</h3>
                <p class="text-xs text-slate-500 mt-1">RELACIÓN DETALLADA DE UNIDADES CURRICULARES</p>
            </div>

            <!-- TABLA DE RELACIÓN DE UNIDADES -->
            <div class="bg-card border rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-muted/40 border-b text-muted-foreground">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-14 text-center">Nro.</th>
                                <th scope="col" class="px-4 py-3 w-28 text-center">Código</th>
                                <th scope="col" class="px-4 py-3">Unidad Curricular</th>
                                <th scope="col" class="px-4 py-3 w-48">Malla / PNF</th>
                                <th scope="col" class="px-4 py-3 w-36">Trayecto</th>
                                <th scope="col" class="px-4 py-3 w-32">Eje</th>
                                <th scope="col" class="px-4 py-3 w-16 text-center">UC</th>
                                <th scope="col" class="px-4 py-3 w-20 text-center">HTEA</th>
                                <th scope="col" class="px-4 py-3 w-20 text-center">HTEI</th>
                                <th scope="col" class="px-4 py-3 w-20 text-center font-bold">THTE</th>
                                <th scope="col" class="px-4 py-3 w-24 text-center">Semanas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="(d, idx) in props.detalles.data || []"
                                :key="d.id_mcd"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3 text-center text-xs font-mono text-muted-foreground">
                                    {{ (props.detalles.current_page - 1) * props.detalles.per_page + idx + 1 }}
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
                                <td class="px-4 py-3 text-xs">
                                    <div class="font-medium text-foreground">
                                        {{ d.malla_curricular?.nombre_mac }}
                                    </div>
                                    <div class="text-[11px] text-muted-foreground uppercase">
                                        {{ d.malla_curricular?.pnf?.nombre_pnf }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ d.trayecto?.nombre_tra || 'Sin Trayecto' }}
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
                                <td class="px-4 py-3 text-center text-xs text-muted-foreground">
                                    {{ d.duracion_semanas_mcd || 36 }} sem.
                                </td>
                            </tr>
                            <tr v-if="(props.detalles.data || []).length === 0">
                                <td colspan="11" class="px-4 py-8 text-center text-muted-foreground">
                                    No se encontraron unidades curriculares para los criterios seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <div class="print:hidden">
                <DataPagination
                    :total="props.detalles.total ?? 0"
                    :per-page="props.detalles.per_page ?? 20"
                    :current-page="props.detalles.current_page ?? 1"
                    :last-page="props.detalles.last_page ?? 1"
                    :from="props.detalles.from ?? 0"
                    :to="props.detalles.to ?? 0"
                    @page-change="(p: number) => router.get(route('academico.reportes.relacion-unidades', { ...props.filters, page: p }))"
                />
            </div>
        </div>
    </AppLayout>
</template>

