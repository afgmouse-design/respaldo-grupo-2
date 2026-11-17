<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Aulas (Programación Académica).
 * Propósito: lista, filtra (texto, sede, tipo de aula, estatus) y pagina las aulas;
 * crear/editar/ver se resuelven en vistas dedicadas. La baja de estatus es lógica (`StatusSwitch`) y la eliminación usa <CrudActionButtons />.
 *
 * Historial:
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, School, X } from 'lucide-vue-next';
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
    aulas: any;
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    tiposAula: Array<{ id_tia: number; nombre_tia: string }>;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'codigo_aula_aul', label: 'Código', align: 'left' },
    { key: 'nombre_sed', label: 'Sede', align: 'left' },
    { key: 'nombre_tia', label: 'Tipo de Aula', align: 'left' },
    { key: 'capacidad_maxima_aul', label: 'Capacidad', align: 'center' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: aulasData,
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
    () => props.aulas ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'aula.index', listRouteName: 'aulas', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el aula "${row.codigo_aula_aul}"?`, `Confirmar ${accion}`)) {
        router.delete(route('aula.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Aulas</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Aulas', subtitle: 'Gestión de Aulas', href: route('aula.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="aula" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <School class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Aulas y Ambientes Físicos
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Administración de aulas, laboratorios, talleres y capacidad instalada por sede.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('aula.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Aula
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por código o tipo de espacio..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <select :value="filterValues.sed_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('sed_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todas las sedes</option>
                        <option v-for="s in props.sedes" :key="s.id_sed" :value="s.id_sed">{{ s.nombre_sed }}</option>
                    </select>

                    <select :value="filterValues.tia_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('tia_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todos los tipos</option>
                        <option v-for="t in props.tiposAula" :key="t.id_tia" :value="t.id_tia">{{ t.nombre_tia }}</option>
                    </select>

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="aulasData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="aulasData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status ?? ''" :options="[
                            { value: '', label: 'Activos' },
                            { value: '0', label: 'Inactivos' },
                            { value: 'all', label: 'Todos' },
                        ]" default-value="" @update:model-value="(val: string) => setFilter('status', val)" />
                    </template>
                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="aula.show"
                            edit-route="aula.edit"
                            delete-route="aula.destroy"
                            :record-id="row.id_aul ?? row.id"
                            :record-name="row.codigo_aula_aul"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="aulasData?.total ?? 0" :per-page="aulasData?.per_page ?? 10"
                :current-page="aulasData?.current_page ?? 1" :last-page="aulasData?.last_page ?? 1"
                :from="aulasData?.from ?? 0" :to="aulasData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

