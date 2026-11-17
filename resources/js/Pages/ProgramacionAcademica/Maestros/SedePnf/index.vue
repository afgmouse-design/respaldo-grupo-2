<script setup lang="ts">
/**
 * Vista de Listado: Gestión de ofertas Sede PNF (Programación Académica).
 * Propósito: lista, filtra (texto, sede, PNF, estatus) y pagina las ofertas sede-PNF;
 * crear/editar/ver se resuelven en modales. La baja es lógica (StatusSwitch + confirmación).
 * Historial:
 *  - 2026-11-15: Se agrega importación faltante de `computed` desde `vue` para evitar ReferenceError en tiempo de ejecución.
 *  - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Plus, X } from 'lucide-vue-next';
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
import {
    obtenerIdPrograma,
    obtenerNombrePrograma,
} from '@/lib/academico';
import type { ProgramaCatalogoItem } from '@/lib/academico';
import { esActivo } from '@/lib/status';
import type { Column } from '@/types/dataTable';

type SedeOpt = { id_sed: number; nombre_sed: string };

const props = defineProps<{
    sedesPnf?: any;
    sedesProgramas?: any;
    sedes: SedeOpt[];
    programas?: ProgramaCatalogoItem[];
    pnfs?: ProgramaCatalogoItem[];
    filters: any;
}>();

const catalogoProgramas = computed(() => props.programas ?? props.pnfs ?? []);
const listadoInicial = computed(
    () => props.sedesProgramas ?? props.sedesPnf ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 }
);

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_sed', label: 'Sede', align: 'left' },
    { key: 'nombre_prg', label: 'Programa Académico', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: sedesPnfData,
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
    () => props.sedesProgramas ?? props.sedesPnf ?? listadoInicial.value,
    props.filters ?? {},
    { routeName: 'sede_pnf.index', listRouteName: 'sedesProgramas', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    const nombrePrograma = row.nombre_prg ?? row.nombre_pnf ?? '';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la oferta "${row.nombre_sed} - ${nombrePrograma}"?`, `Confirmar ${accion}`)) {
        router.delete(route('sede_pnf.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};


</script>

<template>
    <Head>
        <title>Sede PNF</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Sede PNF', subtitle: 'Oferta académica por sede', href: route('sede_pnf.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="sede_pnf" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Building2 class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Oferta Sede - Programa
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Vinculación de programas de formación ofertados por cada sede física de la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('sede_pnf.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Oferta Sede-Programa
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por sede, PNF o código..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <select :value="filterValues.sed_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('sed_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todas las sedes</option>
                        <option v-for="s in props.sedes" :key="s.id_sed" :value="s.id_sed">{{ s.nombre_sed }}</option>
                    </select>

                    <select :value="filterValues.prg_id ?? filterValues.pnf_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('prg_id', ($event.target as HTMLSelectElement).value)">
                        <option value="all">Todos los programas</option>
                        <option v-for="p in catalogoProgramas" :key="obtenerIdPrograma(p)" :value="obtenerIdPrograma(p)">
                            {{ obtenerNombrePrograma(p) }}
                        </option>
                    </select>

                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="sedesPnfData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="sedesPnfData.total" :filtrosActivos="hasActiveFilters">
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
                            show-route="sede_pnf.show"
                            edit-route="sede_pnf.edit"
                            delete-route="sede_pnf.destroy"
                            :record-id="row.id_spr ?? row.id_sep ?? row.id"
                            :record-name="`${row.nombre_sed} - ${row.nombre_prg ?? row.nombre_pnf ?? ''}`"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="sedesPnfData?.total ?? 0" :per-page="sedesPnfData?.per_page ?? 10"
                :current-page="sedesPnfData?.current_page ?? 1" :last-page="sedesPnfData?.last_page ?? 1"
                :from="sedesPnfData?.from ?? 0" :to="sedesPnfData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

