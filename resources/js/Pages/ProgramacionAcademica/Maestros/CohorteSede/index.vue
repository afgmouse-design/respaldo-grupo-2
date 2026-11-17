<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Cohortes por Sede (Programación Académica).
 * Propósito: Listado, filtros por cohorte/oferta y modales CRUD para la asignación de sedes.
 *
 * Actualización 2026-10-21 (Filtro por Oferta Sede-PNF y tipado robusto):
 * Se añadió el selector para filtrar por oferta Sede-PNF (`sep_id`) y se tiparon los handlers de edición/vista
 * con `string | number` para evitar conflictos de tipo con `DataActionsDropdown`.
 *
 * Actualización 2026-10-21 (Visualización Predeterminada de Todas las Ofertas y TabFilter 'all'):
 * Se modificó el TabFilter para iniciar por defecto en 'all' ('Todos'), permitiendo ver todas
 * las asignaciones al entrar o al seleccionar la opción 'Todos'.
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Layers, Plus, X } from 'lucide-vue-next';
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

interface OfertaSelectOpt {
    id_spr?: number;
    id_sep?: number;
    descripcion_oferta: string;
}

const props = defineProps<{
    cohortesSede: any;
    cohortes: Array<{ id_coh: number; nombre_coh: string }>;
    ofertasSedePnf: OfertaSelectOpt[];
    filters: any;
}>();

const resolverOfertaId = (o: OfertaSelectOpt) => o.id_spr ?? o.id_sep ?? 0;

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_cos', label: 'Nombre Grupo / Sede', align: 'left' },
    { key: 'nombre_coh', label: 'Cohorte', align: 'left' },
    { key: 'oferta_academica', label: 'Oferta Académica (Sede - Programa)', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: cohortesSedeData,
    isLoading,
    filterValues,
    perPage,
    changePerPage,
    onPageChange,
    setFilter,
    fetchData,
} = useDataFetching(
    () => props.cohortesSede,
    props.filters ?? {},
    { routeName: 'cohorte_sede.index', listRouteName: 'cohortesSede', perPageDefault: 10 }
);

const hasActiveFilters = computed(() => {
    return Boolean(
        filterValues.value.search ||
        (filterValues.value.status && filterValues.value.status !== 'all') ||
        (filterValues.value.coh_id && filterValues.value.coh_id !== 'all') ||
        (filterValues.value.sep_id && filterValues.value.sep_id !== 'all')
    );
});

const clearFilters = () => {
    filterValues.value.search = '';
    filterValues.value.status = 'all';
    filterValues.value.coh_id = 'all';
    filterValues.value.sep_id = 'all';
    fetchData({ page: 1 });
};

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el registro "${row.nombre_cos}"?`, `Confirmar ${accion}`)) {
        router.delete(route('cohorte_sede.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Cohortes por Sede</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Cohortes por Sede', subtitle: 'Distribución de Cohortes por Oferta de Sede', href: route('cohorte_sede.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="cohorte_sede" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Layers class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Cohortes por Sede
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Distribución de cohortes de ingreso por oferta de sede y programa académico.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('cohorte_sede.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Cohorte por Sede
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por grupo, cohorte o sede..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <select :value="filterValues.coh_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('coh_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todas las cohortes</option>
                        <option v-for="c in props.cohortes" :key="c.id_coh" :value="c.id_coh">{{ c.nombre_coh }}</option>
                    </select>

                    <select :value="filterValues.sep_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('sep_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todas las ofertas (Sede - Programa)</option>
                        <option v-for="o in props.ofertasSedePnf" :key="resolverOfertaId(o)" :value="resolverOfertaId(o)">{{ o.descripcion_oferta }}</option>
                    </select>

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="cohortesSedeData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="cohortesSedeData.total" :filtrosActivos="hasActiveFilters">
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
                            show-route="cohorte_sede.show"
                            edit-route="cohorte_sede.edit"
                            delete-route="cohorte_sede.destroy"
                            :record-id="row.id_cos ?? row.id"
                            :record-name="row.nombre_cos"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="cohortesSedeData?.total ?? 0" :per-page="cohortesSedeData?.per_page ?? 10"
                :current-page="cohortesSedeData?.current_page ?? 1" :last-page="cohortesSedeData?.last_page ?? 1"
                :from="cohortesSedeData?.from ?? 0" :to="cohortesSedeData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

