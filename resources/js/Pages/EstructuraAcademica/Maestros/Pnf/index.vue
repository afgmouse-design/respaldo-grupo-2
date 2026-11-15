<script setup lang="ts">
/**
 * Vista Standalone: Listado General de PNF (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar los Programas Nacionales
 * de Formación registrados en el SOGAC siguiendo el diseño estándar de Trayecto / Sede.
 */
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    GraduationCap,
    Plus,
    Eye,
    Pencil,
    Trash2,
    Power,
    X,
} from 'lucide-vue-next';

interface PnfItem {
    id_pnf: number;
    codigo_pnf: string | null;
    nombre_pnf: string;
    status_id: number;
    status: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    pnfs: {
        data: PnfItem[];
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
    { title: 'PNF', subtitle: 'Oferta Académica', href: route('academico.pnf.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_pnf', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'codigo_pnf', label: 'Código', align: 'center', width: 'w-36' },
    { key: 'nombre_pnf', label: 'Nombre del PNF / Carrera', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: pnfsData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.pnfs,
    props.filters,
    { routeName: 'academico.pnf.index', listRouteName: 'pnfs', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const pnfs = computed(() => props.pnfs);

watch(
    () => props.pnfs,
    (newPnfs) => {
        if (newPnfs) {
            pnfsData.value = newPnfs;
        }
    },
    { deep: true, immediate: true }
);

const alternarEstatus = async (row: PnfItem) => {
    const activo = row.status ?? (row.status_id === 1);
    const accion = activo ? 'desactivar' : 'activar';
    const advertenciaCascada = activo
        ? ' Esto también desactivará automáticamente todas sus mallas curriculares asociadas.'
        : '';

    if (await confirmDialog(
        `¿Está seguro de que desea ${accion} el programa "${row.nombre_pnf}"?${advertenciaCascada}`,
        `Confirmar ${accion} de programa`
    )) {
        const rutaStatus = route().has('academico.pnf.status')
            ? route('academico.pnf.status', row.id_pnf)
            : route('pnf.status', row.id_pnf);
        router.patch(rutaStatus, {}, { preserveScroll: true });
    }
};

const eliminarRegistro = async (ruta: string, row?: PnfItem) => { 
    const nombre = row?.nombre_pnf ?? 'este programa';
    if (await confirmDialog(
        `¿Está seguro de que desea dar de baja "${nombre}"? Sus mallas curriculares asociadas también quedarán inactivas.`,
        'Confirmar baja lógica'
    )) { 
        router.delete(ruta, { preserveScroll: true }); 
    } 
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Programas Nacionales de Formación (PNF)" />

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
                            Catálogo Maestro de PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Programas Nacionales de Formación y carreras académicas ofertadas en la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.pnf.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar PNF
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
                            placeholder="Buscar por código o nombre..."
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
                    :data="pnfs?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="pnfs?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ID -->
                    <template #id_pnf="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            #{{ row.id_pnf }}
                        </span>
                    </template>

                    <!-- SLOT: CÓDIGO -->
                    <template #codigo_pnf="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            {{ row.codigo_pnf || 'N/A' }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE PNF -->
                    <template #nombre_pnf="{ row }">
                        <span class="font-medium text-slate-900 dark:text-slate-100">
                            {{ row.nombre_pnf }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row }">
                        <StatusSwitch
                            :key="`pnf-${row.id_pnf}-${row.status ?? row.status_id}`"
                            :status="row.status ?? (row.status_id === 1)"
                            @toggle="alternarEstatus(row)"
                        />
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
                                <Link :href="route('academico.pnf.show', (row || item).id_pnf)">
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
                                <Link :href="route('academico.pnf.edit', (row || item).id_pnf)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Desactivar / Baja Lógica -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                :title="(row || item)?.status ? 'Desactivar programa' : 'Activar programa'"
                                @click="alternarEstatus(row || item)"
                            >
                                <Power class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="pnfs?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="pnfs?.current_page ?? 1"
                :last-page="pnfs?.last_page ?? 1"
                :from="pnfs?.from ?? 0"
                :to="pnfs?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
