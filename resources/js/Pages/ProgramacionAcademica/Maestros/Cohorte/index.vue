<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Cohortes (Programación Académica).
 * Propósito: Listado, filtrado, paginación y modales CRUD para Cohortes de ingreso.
 *
 * Actualización 2026-10-21 (Visualización Predeterminada de Todas las Cohortes y TabFilter 'all'):
 * Se modificó el TabFilter para iniciar por defecto en 'all' ('Todos'), permitiendo ver todas
 * las cohortes registradas al entrar o al seleccionar la opción 'Todos'. Se adaptó clearFilters
 * y hasActiveFilters para sincronizarse con los valores predeterminados.
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de
 *   DataActionsDropdown, columna "Estatus" exclusiva para StatusSwitch y botón superior "Nueva Cohorte".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';
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
    cohortes: any;
    lapsos: Array<{ id_laa?: number; id_la?: number; nombre_laa?: string; nombre_la?: string }>;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_coh', label: 'Nombre Cohorte', align: 'left' },
    { key: 'nombre_laa', label: 'Lapso Académico', align: 'left' },
    { key: 'fecha_inicio_coh', label: 'Inicio', align: 'center' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
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

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
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

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Cohortes Académicas
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Administración de cohortes de ingreso estudiantil vinculadas a lapsos académicos.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('cohorte.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Cohorte
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
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
                        <option v-for="l in props.lapsos" :key="l.id_laa ?? l.id_la" :value="l.id_laa ?? l.id_la">{{ l.nombre_laa ?? l.nombre_la }}</option>
                    </select>

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
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
                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="cohorte.show"
                            edit-route="cohorte.edit"
                            delete-route="cohorte.destroy"
                            :record-id="row.id_coh ?? row.id"
                            :record-name="row.nombre_coh"
                            confirm-message="¿Está seguro de que desea eliminar esta cohorte? Sus asignaciones por sede (cohorte-sede) asociadas también serán desactivadas en cascada."
                            @deleted="fetchData()"
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

