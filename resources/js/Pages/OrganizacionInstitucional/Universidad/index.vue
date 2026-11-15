<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Universidades (Módulo 2 - Organización Institucional).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar la institución
 * universitaria matriz registrada en el SOGAC. Cumple estrictamente la regla de CERO MODALES
 * mediante navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * instituciones universitarias con confirmación modal.
 */
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useTableExport } from '@/composables/useTableExport';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Building2,
    Plus,
    Eye,
    Pencil,
    Power,
    Printer,
    FileSpreadsheet,
    FileText,
    X,
    MapPin,
    GraduationCap,
} from 'lucide-vue-next';

interface LocalidadRelacion {
    id_loc: number;
    nombre_loc: string;
    parroquia?: {
        id_par: number;
        nombre_par: string;
        municipio?: {
            id_mun: number;
            nombre_mun: string;
            estado?: {
                id_esd: number;
                nombre_esd: string;
            };
        };
    };
}

interface UniversidadItem {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
    rif_uni?: string | null;
    codigo_ministerio_uni?: string | null;
    status: boolean;
    est_uni?: boolean;
    localidad?: LocalidadRelacion | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    universidades: {
        data: UniversidadItem[];
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
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('organizacion.universidad.index') },
    { title: 'Universidad', subtitle: 'Institución Matriz', href: route('organizacion.universidad.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_uni', label: 'ID', align: 'center', width: 'w-16' },
    { key: 'nombre_uni', label: 'Institución Universitaria', align: 'left' },
    { key: 'siglas_uni', label: 'Siglas', align: 'center', width: 'w-28' },
    { key: 'rif_uni', label: 'RIF', align: 'center', width: 'w-36' },
    { key: 'localidad', label: 'Sede Rectoral (Ubicación)', align: 'left', width: 'w-64' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones Directas', align: 'center', width: 'w-48' },
];

const {
    data: universidadesData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.universidades,
    props.filters,
    { routeName: 'organizacion.universidad.index', listRouteName: 'universidades', perPageDefault: 5 }
);

const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const universidades = computed(() => props.universidades);

watch(
    () => props.universidades,
    (newUni) => {
        if (newUni) {
            universidadesData.value = newUni;
        }
    },
    { deep: true, immediate: true }
);

const isActivo = (row: any) => {
    const val = row?.status ?? row?.est_uni;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const toggleEstatus = async (row: UniversidadItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la universidad "${row.nombre_uni}"?`)) {
        router.patch(route('organizacion.universidad.status', row.id_uni), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const { exportToExcel, exportToPdf, printCurrentPage } = useTableExport();

const handleExportExcel = () => {
    exportToExcel(props.universidades?.data || [], columns, 'universidades');
};

const handleExportPdf = () => {
    exportToPdf(
        'Catálogo Maestro de Universidades',
        columns,
        props.universidades?.data || [],
        'Sistema de Organización y Gestión Académica Institucional'
    );
};

const handlePrint = () => {
    printCurrentPage();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Universidad - Organización Institucional" />

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
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Institución Universitaria
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Entidad matriz rectora de la estructura institucional y académica del sistema SOGAC.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('organizacion.universidad.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Universidad
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 max-w-md">
                    <SearchFilter
                        :model-value="filterValues.search"
                        placeholder="Buscar por nombre, siglas, RIF o código..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
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
                    :data="universidades?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="universidades?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: BOTONES DE EXPORTACIÓN E IMPRESIÓN -->
                    <template #header-actions>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30"
                            title="Exportar registros a formato Excel"
                            @click="handleExportExcel"
                        >
                            <FileSpreadsheet class="h-3.5 w-3.5" />
                            Excel
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1.5 text-xs font-medium text-rose-700 hover:text-rose-800 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30"
                            title="Exportar registros a formato PDF"
                            @click="handleExportPdf"
                        >
                            <FileText class="h-3.5 w-3.5" />
                            PDF
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1.5 text-xs font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800"
                            title="Imprimir vista de registros"
                            @click="handlePrint"
                        >
                            <Printer class="h-3.5 w-3.5" />
                            Imprimir
                        </Button>
                    </template>

                    <!-- SLOT: NOMBRE -->
                    <template #nombre_uni="{ row }">
                        <div class="flex flex-col">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ row.nombre_uni }}
                            </span>
                            <span v-if="row.codigo_ministerio_uni" class="text-xs text-muted-foreground">
                                MPPEU: {{ row.codigo_ministerio_uni }}
                            </span>
                        </div>
                    </template>

                    <!-- SLOT: SIGLAS -->
                    <template #siglas_uni="{ row }">
                        <span v-if="row.siglas_uni" class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ row.siglas_uni }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground italic">
                            N/A
                        </span>
                    </template>

                    <!-- SLOT: RIF -->
                    <template #rif_uni="{ row }">
                        <span v-if="row.rif_uni" class="font-mono text-xs text-slate-700 dark:text-slate-300">
                            {{ row.rif_uni }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground italic">
                            No registrado
                        </span>
                    </template>

                    <!-- SLOT: SEDE RECTORAL (UBICACIÓN) -->
                    <template #localidad="{ row }">
                        <div v-if="row.localidad" class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300">
                            <MapPin class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <span class="truncate">
                                {{ row.localidad.nombre_loc }}
                                <template v-if="row.localidad.parroquia?.municipio?.estado">
                                    , {{ row.localidad.parroquia.municipio.estado.nombre_esd }}
                                </template>
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground italic">
                            Sin asignar
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con est_uni / status) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`uni-${(row || item)?.id_uni}-${((row || item)?.est_uni ?? (row || item)?.status)}`"
                            :status="(row || item)?.est_uni ?? (row || item)?.status"
                            @toggle="toggleEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE (0 MODALES) -->
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400" title="Ver ficha completa">
                                <Link :href="route('organizacion.universidad.show', row.id_uni)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400" title="Editar datos">
                                <Link :href="route('organizacion.universidad.edit', row.id_uni)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Alternar Estatus / Baja Lógica -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                :class="isActivo(row) ? 'text-red-600 hover:text-red-700 dark:text-red-400' : 'text-emerald-600 hover:text-emerald-700 dark:text-emerald-400'"
                                :title="isActivo(row) ? 'Desactivar universidad' : 'Activar universidad'"
                                @click="toggleEstatus(row)"
                            >
                                <Power class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="universidades?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="universidades?.current_page ?? 1"
                :last-page="universidades?.last_page ?? 1"
                :from="universidades?.from ?? 0"
                :to="universidades?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

