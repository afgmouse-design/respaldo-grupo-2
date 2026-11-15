<script setup lang="ts">
/**
 * Reporte: Relación De Unidades General (Catálogo Maestro Institucional).
 *
 * Módulo: Estructura Académica / Reportes.
 * Propósito: Listado general transversal de todas las asignaturas de la universidad,
 * indicando su naturaleza pedagógica y los planes de estudio (mallas y PNF) donde se imparten.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Library,
    Printer,
    ArrowLeft,
    Search,
    X,
    BookOpen,
    Layers,
    FileSpreadsheet,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import DataPagination from '@/components/ui/DataPagination.vue';

interface TipoOpt { id_tuc: number; nombre_tuc: string }

const props = defineProps<{
    unidades: any;
    tipos: TipoOpt[];
    filters: {
        search?: string;
        tuc_id?: string | number;
    };
}>();

const search = ref(props.filters.search ?? '');
const tucId = ref(props.filters.tuc_id ?? 'all');

const aplicarFiltros = () => {
    router.get(route('academico.reportes.relacion-unidades-general'), {
        search: search.value || undefined,
        tuc_id: tucId.value !== 'all' ? tucId.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    search.value = '';
    tucId.value = 'all';
    aplicarFiltros();
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <Head title="Reporte: Relación General de Unidades Curriculares" />

    <AppLayout :breadcrumbs="[
        { title: 'Estructura Académica', href: route('academico.unidad-curricular.index') },
        { title: 'Reportes', href: route('academico.reportes.relacion-unidades-general') },
        { title: 'Relación De Unidades General', href: route('academico.reportes.relacion-unidades-general') },
    ]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:border-none">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-purple-500/10 rounded-lg text-purple-600 dark:text-purple-400 print:hidden">
                            <Library class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                Reporte: Relación De Unidades General
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                Catálogo institucional unificado de todas las unidades curriculares ofertadas en la institución.
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
                        :href="route('academico.reportes.relacion-unidades-general.excel', { search: search, tuc_id: tucId })"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 gap-1.5 cursor-pointer"
                        download
                    >
                        <FileSpreadsheet class="h-4 w-4 text-emerald-600" />
                        <span>Excel</span>
                    </a>

                    <a
                        :href="route('academico.reportes.relacion-unidades-general.pdf')"
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

            <!-- FILTROS -->
            <div class="bg-card border rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 print:hidden">
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

                <div class="flex items-center gap-2">
                    <select
                        v-model="tucId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="aplicarFiltros"
                    >
                        <option value="all">Todos los tipos</option>
                        <option v-for="t in props.tipos" :key="t.id_tuc" :value="t.id_tuc">
                            {{ t.nombre_tuc }}
                        </option>
                    </select>

                    <Button
                        v-if="search || tucId !== 'all'"
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
                <p class="text-xs text-slate-500 mt-1">CATÁLOGO MAESTRO INSTITUCIONAL DE UNIDADES CURRICULARES</p>
            </div>

            <!-- TABLA GENERAL DE ASIGNATURAS -->
            <div class="bg-card border rounded-xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-muted/40 border-b text-muted-foreground">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-16 text-center">Nro.</th>
                                <th scope="col" class="px-4 py-3 w-32 text-center">Código</th>
                                <th scope="col" class="px-4 py-3">Nombre de la Unidad Curricular</th>
                                <th scope="col" class="px-4 py-3 w-48 text-center">Tipo de Unidad</th>
                                <th scope="col" class="px-4 py-3 w-36 text-center">Presencia en Mallas</th>
                                <th scope="col" class="px-4 py-3">Programas (PNF) que la imparten</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="(u, idx) in props.unidades.data || []"
                                :key="u.id_unc"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <td class="px-4 py-3 text-center text-xs font-mono text-muted-foreground">
                                    {{ (props.unidades.current_page - 1) * props.unidades.per_page + idx + 1 }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge variant="secondary" class="font-mono text-xs font-bold">
                                        {{ u.codigo_unc }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                    {{ u.nombre_unc }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge variant="outline" class="text-xs">
                                        {{ u.tipo_unidad_curricular?.nombre_tuc || 'Obligatoria' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                        {{ u.detalles_malla_count }} {{ u.detalles_malla_count === 1 ? 'Malla' : 'Mallas' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="(u.detalles_malla || []).length > 0" class="flex flex-wrap gap-1">
                                        <span
                                            v-for="dm in u.detalles_malla"
                                            :key="dm.id_mcd"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-muted/60 text-foreground border"
                                        >
                                            {{ dm.malla_curricular?.pnf?.nombre_pnf || dm.malla_curricular?.nombre_mac }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground italic">
                                        No vinculada a ningún plan activo
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="(props.unidades.data || []).length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No se encontraron unidades curriculares registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINACIÓN -->
            <div class="print:hidden">
                <DataPagination
                    :total="props.unidades.total ?? 0"
                    :per-page="props.unidades.per_page ?? 25"
                    :current-page="props.unidades.current_page ?? 1"
                    :last-page="props.unidades.last_page ?? 1"
                    :from="props.unidades.from ?? 0"
                    :to="props.unidades.to ?? 0"
                    @page-change="(p: number) => router.get(route('academico.reportes.relacion-unidades-general', { ...props.filters, page: p }))"
                />
            </div>
        </div>
    </AppLayout>
</template>

