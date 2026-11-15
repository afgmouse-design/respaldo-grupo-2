<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Tipos de Aula (Programación Académica).
 * Propósito: lista, filtra y pagina los tipos de aula; crear/editar/ver se resuelven en modales.
 * La baja es lógica (StatusSwitch + confirmación modal) mediante el método destroy del controlador.
 */
import { ref } from 'vue';
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
    tiposAula: any;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'codigo_tia', label: 'Código', align: 'center', width: 'w-28' },
    { key: 'nombre_tia', label: 'Nombre', align: 'left' },
    { key: 'status', label: 'Status', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-64' },
];

const {
    data: tiposData,
    isLoading,
    filterValues,
    perPage,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
    fetchData,
} = useDataFetching(
    props.tiposAula ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'tipo_aula.index', listRouteName: 'tiposAula', perPageDefault: 10 }
);

const esActivo = (v: any) => v === true || v === 1 || v === '1' || v === 't';

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row.status) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de aula "${row.nombre_tia}"?`, `Confirmar ${accion}`)) {
        router.delete(route('tipo_aula.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Tipos de Aula</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Tipos de Aula', subtitle: 'Gestión de Tipos de Aula', href: route('tipo_aula.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="tipo_aula" @close="closeAlert" />

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por nombre, código o descripción..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('tipo_aula.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nuevo
                        </Link>
                    </Button>
                </div>
            </div>

            <div>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="tiposData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="tiposData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status ?? ''" :options="[
                            { value: '', label: 'Activos' },
                            { value: '0', label: 'Inactivos' },
                            { value: 'all', label: 'Todos' },
                        ]" default-value="" @update:model-value="(val: string) => setFilter('status', val)" />
                    </template>
                    <template #status="{ row, value }">
                        <StatusSwitch :status="value ?? row?.status" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <DataActionsDropdown
                            :is-modal="false"
                            editRoute="tipo_aula.edit"
                            showRoute="tipo_aula.show"
                            deleteRoute="tipo_aula.destroy"
                            :id="row.id"
                            :status="esActivo(row.status) ? 1 : 0"
                            @success="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="tiposData?.total ?? 0" :per-page="tiposData?.per_page ?? 10"
                :current-page="tiposData?.current_page ?? 1" :last-page="tiposData?.last_page ?? 1"
                :from="tiposData?.from ?? 0" :to="tiposData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>
