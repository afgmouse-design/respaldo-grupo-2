<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Tipos de Lapso (Programación Académica).
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna Status para activar y desactivar
 * registros con confirmación modal.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X } from 'lucide-vue-next';

// Layout e UI Reutilizable
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Button } from '@/components/ui/button';
import DataActionsDropdown from '@/components/ui/DataActionsDropdown.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

// Filtros
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import DateRangeFilter from '@/components/ui/filters/DateRangeFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';

// Composables y Tipos
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { Column } from '@/types/dataTable';

const props = defineProps<{
    tipoLapsos: any;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'tap_codigo', label: 'Código', align: 'center' },
    { key: 'tap_nombre', label: 'Nombre', align: 'left' },
    { key: 'tap_estatus', label: 'Status', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-64' },
];

const {
    data: tipoLapsosData,
    isLoading,
    filterValues,
    perPage,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
    fetchData
} = useDataFetching(
    props.tipoLapsos ?? { data: [], total: 0, per_page: 5, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters,
    { routeName: 'tipo_lapso.index', listRouteName: 'tipoLapsos', perPageDefault: 5 }
);

// Utilidades estéticas compartidas
const statusBadge = (status: any) => {
    return status === 'A' || status === 1 || status === '1' || status === true
        ? { text: 'Activo', variant: 'successCustom' as const }
        : { text: 'Inactivo', variant: 'destructiveCustom' as const };
};

const alternarEstatus = async (row: any) => {
    const activo = row.tap_estatus === 'A' || row.tap_estatus === 1 || row.tap_estatus === '1' || row.tap_estatus === true;
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de lapso "${row.tap_nombre}"?`, `Confirmar ${accion}`)) {
        router.delete(route('tipo_lapso.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};

const formatDate = (dateStr?: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('es-ES', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <Head>
        <title>Tipos de Lapso</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Tipos de Lapso', subtitle: 'Gestión de Tipos de Lapso Académico', href: route('tipo_lapso.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="tipo_lapso"
                @close="closeAlert" />

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por nombre o código..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <DateRangeFilter label="" :start-value="filterValues.fecha_start"
                        :end-value="filterValues.fecha_end"
                        @apply="(start: any, end: any) => { setFilter('fecha_inicio', start); setFilter('fecha_fin', end); }" />

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('tipo_lapso.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nuevo
                        </Link>
                    </Button>
                </div>
            </div>

            <div>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="tipoLapsosData?.data || []" :is-loading="isLoading" :resultadosTotales="tipoLapsosData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status" :options="[
                            { value: 'all', label: 'Todos' },
                            { value: '1', label: 'Activos' },
                            { value: '0', label: 'Inactivos' }
                        ]" :default-value="1" @update:model-value="val => setFilter('status', val)" />
                    </template>
                    <template #tap_estatus="{ row, value }">
                        <StatusSwitch
                            :status="value ?? row?.tap_estatus"
                            @toggle="alternarEstatus(row)"
                        />
                    </template>
                    <template #actions="{ row }">
                        <DataActionsDropdown
                            :is-modal="false"
                            editRoute="tipo_lapso.edit"
                            showRoute="tipo_lapso.show"
                            deleteRoute="tipo_lapso.destroy"
                            :id="row.id"
                            :status="Number(row.tap_estatus)"
                            @success="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="tipoLapsosData?.total ?? 0" :per-page="tipoLapsosData?.per_page ?? 5"
                :current-page="tipoLapsosData?.current_page ?? 1" :last-page="tipoLapsosData?.last_page ?? 1"
                :from="tipoLapsosData?.from ?? 0" :to="tipoLapsosData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>
