<script setup lang="ts">
/**
 * Vista Standalone de Listado: Gestión de Turnos (Programación Académica).
 * Propósito: lista, filtra y pagina los turnos académicos desde PostgreSQL (`turno`).
 *
 * Historial:
 *  - 2026-11-16: Refactorizado piloto con <CrudActionButtons /> (íconos compactos Ver/Editar/Eliminar)
 *    reemplazando <DataActionsDropdown /> con texto y estandarizando la columna de acciones.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, Plus, X } from 'lucide-vue-next';
import { route } from 'ziggy-js';

import { Button } from '@/components/ui/button';
import CrudActionButtons from '@/components/ui/CrudActionButtons.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import DataTable from '@/components/ui/DataTable.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo } from '@/lib/status';
import type { Column } from '@/types/dataTable';

const props = defineProps<{
    turnos: any;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'codigo_tur', label: 'Código', align: 'center', width: 'w-28' },
    { key: 'nombre_tur', label: 'Nombre del Turno', align: 'left' },
    { key: 'hora_inicio_tur', label: 'Hora Inicio', align: 'center', width: 'w-32' },
    { key: 'hora_fin_tur', label: 'Hora Fin', align: 'center', width: 'w-32' },
    { key: 'status', label: 'Status', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: turnosData,
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
    () => props.turnos ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'turno.index', listRouteName: 'turnos', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el turno "${row.nombre_tur}"?`, `Confirmar ${accion}`)) {
        router.delete(route('turno.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};
</script>

<template>
    <Head title="Turnos Académicos" />

    <AppLayout :breadcrumbs="[{ title: 'Turnos Académicos', subtitle: 'Gestión de Jornadas y Franjas Horarias', href: route('turno.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="turno" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Clock class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Turnos Académicos
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo maestro de jornadas académicas y franjas horarias de funcionamiento.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('turno.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Turno
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter
                        class="w-full flex-1"
                        :model-value="filterValues.search ?? ''"
                        placeholder="Buscar por nombre, código o descripción..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="turnosData?.data || []"
                    :is-loading="isLoading"
                    :resultadosTotales="turnosData.total"
                    :filtrosActivos="hasActiveFilters"
                >
                    <template #header-actions>
                        <TabFilter
                            :model-value="filterValues.status ?? ''"
                            :options="[
                                { value: '', label: 'Activos' },
                                { value: '0', label: 'Inactivos' },
                                { value: 'all', label: 'Todos' },
                            ]"
                            default-value=""
                            @update:model-value="(val: string) => setFilter('status', val)"
                        />
                    </template>
                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="turno.show"
                            edit-route="turno.edit"
                            delete-route="turno.destroy"
                            :record-id="row.id"
                            :record-name="row.nombre_tur"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination
                :total="turnosData?.total ?? 0"
                :per-page="turnosData?.per_page ?? 10"
                :current-page="turnosData?.current_page ?? 1"
                :last-page="turnosData?.last_page ?? 1"
                :from="turnosData?.from ?? 0"
                :to="turnosData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

