<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Cohortes (Programación Académica).
 * Propósito: Listado, filtrado, paginación y modales CRUD para Cohortes de ingreso.
 *
 * Actualización 2026-10-21 (Visualización Predeterminada de Todas las Cohortes y TabFilter 'all'):
 * Se modificó el TabFilter para iniciar por defecto en 'all' ('Todos'), permitiendo ver todas
 * las cohortes registradas al entrar o al seleccionar la opción 'Todos'. Se adaptó clearFilters
 * y hasActiveFilters para sincronizarse con los valores predeterminados.
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Button } from '@/components/ui/button';
import DataActionsDropdown from '@/components/ui/DataActionsDropdown.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';

import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { Column } from '@/types/dataTable';

const props = defineProps<{
    cohortes: any;
    lapsos: Array<{ id_la: number; nombre_la: string }>;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_coh', label: 'Nombre Cohorte', align: 'left' },
    { key: 'nombre_la', label: 'Lapso Académico', align: 'left' },
    { key: 'fecha_inicio_coh', label: 'Inicio', align: 'center' },
    { key: 'status', label: 'Status', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-64' },
];

const {
    data: cohortesData,
    isLoading,
    filterValues,
    perPage,
    changePerPage,
    onPageChange,
    setFilter,
    fetchData,
} = useDataFetching(
    () => props.cohortes,
    props.filters ?? {},
    { routeName: 'cohorte.index', listRouteName: 'cohortes', perPageDefault: 10 }
);

const hasActiveFilters = computed(() => {
    return Boolean(
        filterValues.value.search ||
        (filterValues.value.status && filterValues.value.status !== 'all') ||
        (filterValues.value.laa_id && filterValues.value.laa_id !== 'all')
    );
});

const clearFilters = () => {
    filterValues.value.search = '';
    filterValues.value.status = 'all';
    filterValues.value.laa_id = 'all';
    fetchData({ page: 1 });
};

const esActivo = (v: any) => v === true || v === 1 || v === '1' || v === 't';

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row.status) ? 'desactivar' : 'activar';
    const advertencia = accion === 'desactivar' ? ' Al desactivarla, sus ofertas en sedes (cohorte-sede) asociadas también se desactivarán en cascada.' : '';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la cohorte "${row.nombre_coh}"?${advertencia}`, `Confirmar ${accion}`)) {
        router.patch(route('cohorte.alternar-estatus', row.id), {}, {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Cohortes</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Cohortes', subtitle: 'Gestión de Cohortes de Ingreso', href: route('cohorte.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="cohorte" @close="closeAlert" />

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por nombre de cohorte..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <select :value="filterValues.laa_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('laa_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todos los lapsos</option>
                        <option v-for="l in props.lapsos" :key="l.id_la" :value="l.id_la">{{ l.nombre_la }}</option>
                    </select>

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('cohorte.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nuevo
                        </Link>
                    </Button>
                </div>
            </div>

            <div>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="cohortesData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="cohortesData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status || 'all'" :options="[
                            { value: 'all', label: 'Todos' },
                            { value: 'true', label: 'Activos' },
                            { value: 'false', label: 'Inactivos' },
                        ]" default-value="all" @update:model-value="(val: string) => setFilter('status', val)" />
                    </template>
                    <template #status="{ row, value }">
                        <StatusSwitch :status="value ?? row?.status" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <DataActionsDropdown
                            :is-modal="false"
                            editRoute="cohorte.edit"
                            showRoute="cohorte.show"
                            deleteRoute="cohorte.destroy"
                            :id="row.id"
                            :status="esActivo(row.status) ? 1 : 0"
                            @success="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="cohortesData?.total ?? 0" :per-page="cohortesData?.per_page ?? 10"
                :current-page="cohortesData?.current_page ?? 1" :last-page="cohortesData?.last_page ?? 1"
                :from="cohortesData?.from ?? 0" :to="cohortesData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

