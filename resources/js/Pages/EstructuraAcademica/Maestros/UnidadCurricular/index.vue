<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Unidades Curriculares (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar las materias y unidades
 * curriculares registradas en el SOGAC siguiendo el diseño estándar de PNF / Trayecto / Sede.
 */
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    BookOpen,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    Tag,
} from 'lucide-vue-next';

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
}

interface TrayectoItem {
    id_tra: number;
    nombre_tra: string;
}

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc: string | null;
    nombre_unc: string;
    tuc_id: number | null;
    semestre_unc?: number | null;
    tra_id?: number | null;
    unidades_credito_unc?: number | null;
    htea_unc?: number | null;
    htei_unc?: number | null;
    thte_unc?: number | null;
    tipo_unidad_curricular?: TipoUnidadCurricularItem | null;
    trayecto?: TrayectoItem | null;
    status_id: number;
    status: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    unidades: {
        data: UnidadCurricularItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Unidades Curriculares', subtitle: 'Materias y Asignaturas', href: route('academico.unidad-curricular.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();

const columns: Column[] = [
    { key: 'id_unc', label: 'ID', align: 'center', width: 'w-16' },
    { key: 'codigo_unc', label: 'Código', align: 'center', width: 'w-28' },
    { key: 'nombre_unc', label: 'Nombre de la Materia / Asignatura', align: 'left' },
    { key: 'semestre_unc', label: 'Semestre', align: 'center', width: 'w-28' },
    { key: 'trayecto', label: 'Trayecto', align: 'left', width: 'w-44' },
    { key: 'unidades_credito_unc', label: 'UC', align: 'center', width: 'w-20' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-24' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: unidadesData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.unidades,
    props.filters,
    { routeName: 'academico.unidad-curricular.index', listRouteName: 'unidades', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const unidades = computed(() => props.unidades);

watch(
    () => props.unidades,
    (newUnidades) => {
        if (newUnidades) {
            unidadesData.value = newUnidades;
        }
    },
    { deep: true, immediate: true }
);

const eliminarRegistro = (ruta: string) => { 
    if (confirm('⚠️ ADVERTENCIA: ¿Estás seguro de que deseas eliminar este registro? Desaparecerá de la lista.')) { 
        router.delete(ruta); 
    } 
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Unidades Curriculares - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ALERTA FLASH -->
            <SuccessAlert
                v-if="showSuccessAlert"
                :message="message"
                @close="closeAlert"
            />

            <!-- ENCABEZADO DE PÁGINA STANDALONE -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Unidades Curriculares
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo universal de materias, proyectos, talleres y asignaturas formativas de la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.unidad-curricular.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Unidad Curricular
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="w-full sm:w-72">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar por código, nombre o tipo..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <TabFilter
                        :model-value="filterValues.status ?? 'all'"
                        :options="[
                            { value: 'all', label: 'Todos' },
                            { value: 'true', label: 'Activos' },
                            { value: 'false', label: 'Inactivos' },
                        ]"
                        default-value="all"
                        @update:model-value="(val: string) => setFilter('status', val)"
                    />

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        title="Limpiar filtros"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- TABLA DE DATOS -->
            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeleton-rows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="unidades?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="unidades?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ID -->
                    <template #id_unc="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            #{{ row.id_unc }}
                        </span>
                    </template>

                    <!-- SLOT: CÓDIGO -->
                    <template #codigo_unc="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            {{ row.codigo_unc || 'N/A' }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE UNIDAD -->
                    <template #nombre_unc="{ row }">
                        <span class="font-medium text-slate-900 dark:text-slate-100">
                            {{ row.nombre_unc }}
                        </span>
                    </template>

                    <!-- SLOT: SEMESTRE -->
                    <template #semestre_unc="{ row }">
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-semibold bg-primary/10 text-primary">
                            Semestre {{ row.semestre_unc || 1 }}
                        </span>
                    </template>

                    <!-- SLOT: TRAYECTO -->
                    <template #trayecto="{ row }">
                        <span v-if="row.trayecto" class="text-xs text-foreground font-medium">
                            {{ row.trayecto.nombre_tra }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground italic">
                            Sin trayecto
                        </span>
                    </template>

                    <!-- SLOT: UNIDADES DE CRÉDITO -->
                    <template #unidades_credito_unc="{ row }">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                            {{ row.unidades_credito_unc ?? 3 }} UC
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row }">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                            Activo
                        </span>
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE -->
                    <template #actions="{ row, item }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button
                                as-child
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/40"
                                title="Ver detalles"
                            >
                                <Link :href="route('academico.unidad-curricular.show', (row || item).id_unc)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar -->
                            <Button
                                as-child
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400"
                                title="Editar datos"
                            >
                                <Link :href="route('academico.unidad-curricular.edit', (row || item).id_unc)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Eliminar (Baja Lógica) -->
                            <button
                                type="button"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-950/50 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors"
                                title="Eliminar unidad curricular"
                                @click="eliminarRegistro(route('academico.unidad-curricular.destroy', (row || item).id_unc))"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="unidades?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="unidades?.current_page ?? 1"
                :last-page="unidades?.last_page ?? 1"
                :from="unidades?.from ?? 0"
                :to="unidades?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

