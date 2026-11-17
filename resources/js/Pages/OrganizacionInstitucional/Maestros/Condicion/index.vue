<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Condiciones de Sede (Organización Institucional).
 * Propósito: lista, filtra (texto, estatus) y pagina registros de `condicion_sede`.
 * La baja es lógica (`status_id` vía `StatusSwitch` + confirmación).
 *
 * Historial:
 *  - 2026-11-14: Alineado con `condicion_sede` (`id_cse`, `nombre_cse`, `descripcion_cse`, `status_id`)
 *    del esquema `sogac_mio.sql`, retirando el filtro legacy `tiposCondicion`.
 *  - 2026-11-16: Estandarización global con <CrudActionButtons /> (Ver detalles / Editar / Eliminar)
 *    en reemplazo de DataActionsDropdown, manteniendo StatusSwitch exclusivo en Estatus.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Tag, X } from 'lucide-vue-next';
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
    condiciones: any;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_cse', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_cse', label: 'Nombre', align: 'left' },
    { key: 'descripcion_cse', label: 'Descripción', align: 'left' },
    { key: 'status_id', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: condicionesData,
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
    () => props.condiciones ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'condicion.index', listRouteName: 'condiciones', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la condición "${row.nombre_cse}"?`, `Confirmar ${accion}`)) {
        router.delete(route('condicion.destroy', row.id_cse ?? row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Condiciones de Sede</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Condiciones', subtitle: 'Gestión de Condiciones de Sede', href: route('condicion.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="condicion" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Tag class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Condiciones de Sede
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo de clasificación operativa de la universidad matriz y sus sedes territoriales.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('condicion.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Condición
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por nombre o descripción..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="condicionesData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="condicionesData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status ?? ''" :options="[
                            { value: '', label: 'Activos' },
                            { value: '2', label: 'Inactivos' },
                            { value: 'all', label: 'Todos' },
                        ]" default-value="" @update:model-value="(val: string) => setFilter('status', val)" />
                    </template>
                    <template #status_id="{ row }">
                        <StatusSwitch :status="esActivo(row)" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="condicion.show"
                            edit-route="condicion.edit"
                            delete-route="condicion.destroy"
                            :record-id="row.id_cse ?? row.id"
                            :record-name="row.nombre_cse"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="condicionesData?.total ?? 0" :per-page="condicionesData?.per_page ?? 10"
                :current-page="condicionesData?.current_page ?? 1" :last-page="condicionesData?.last_page ?? 1"
                :from="condicionesData?.from ?? 0" :to="condicionesData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

