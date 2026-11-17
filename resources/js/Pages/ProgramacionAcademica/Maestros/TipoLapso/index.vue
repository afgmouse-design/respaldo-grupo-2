<script setup lang="ts">
/**
 * Vista de Listado: Tipos de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Listado, filtros y gestión CRUD del maestro `tipo_lapso(id_tla)` con estado lógico `status_id`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `tipo_lapso` (`id_tla`, `codigo_tla`, `nombre_tla`, `duracion_semanas_tla`,
 *    `nomenclatura_tla`, `status_id`), usando `esActivo` de `@/lib/status`.
 *  - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
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
    tipoLapsos: any;
    filters: any;
    registros_per_page?: number;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'codigo_tla', label: 'Código', align: 'center', width: 'w-32' },
    { key: 'nombre_tla', label: 'Nombre', align: 'left' },
    { key: 'duracion_semanas_tla', label: 'Semanas', align: 'center', width: 'w-28' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
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
    fetchData,
} = useDataFetching(
    () => props.tipoLapsos,
    props.filters ?? {},
    {
        routeName: 'tipo_lapso.index',
        listRouteName: 'tipoLapsos',
        perPageDefault: props.registros_per_page ?? 10,
    }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de lapso "${row.nombre_tla}"?`, `Confirmar ${accion}`)) {
        router.delete(route('tipo_lapso.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};
</script>

<template>
    <Head>
        <title>Tipos de Lapso Académico</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Tipos de Lapso', subtitle: 'Clasificación de Períodos Académicos', href: route('tipo_lapso.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="tipo_lapso" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Clock class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Tipos de Lapso Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo oficial de regímenes temporales (Semestral, Trimestral, Anual y Trayectos).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('tipo_lapso.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Tipo de Lapso
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter
                        class="w-full flex-1"
                        :model-value="filterValues.search ?? ''"
                        placeholder="Buscar por código, nombre o nomenclatura..."
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
                    :data="tipoLapsosData?.data || []"
                    :is-loading="isLoading"
                    :resultadosTotales="tipoLapsosData?.total ?? 0"
                    :filtrosActivos="hasActiveFilters"
                >
                    <template #header-actions>
                        <TabFilter
                            :model-value="filterValues.status || 'all'"
                            :options="[
                                { value: 'all', label: 'Todos' },
                                { value: '1', label: 'Activos' },
                                { value: '0', label: 'Inactivos' },
                            ]"
                            default-value="all"
                            @update:model-value="(val: string) => setFilter('status', val)"
                        />
                    </template>

                    <template #duracion_semanas_tla="{ value }">
                        <span>{{ value }} sem.</span>
                    </template>

                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="tipo_lapso.show"
                            edit-route="tipo_lapso.edit"
                            delete-route="tipo_lapso.destroy"
                            :record-id="row.id_tla ?? row.id"
                            :record-name="row.nombre_tla"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination
                :total="tipoLapsosData?.total ?? 0"
                :per-page="tipoLapsosData?.per_page ?? 10"
                :current-page="tipoLapsosData?.current_page ?? 1"
                :last-page="tipoLapsosData?.last_page ?? 1"
                :from="tipoLapsosData?.from ?? 0"
                :to="tipoLapsosData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
