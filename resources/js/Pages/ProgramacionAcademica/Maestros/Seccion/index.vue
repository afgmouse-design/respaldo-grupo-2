<script setup lang="ts">
/**
 * Vista Standalone: Maestro de Secciones y Horarios (Programación Académica).
 * Propósito: Listado paginado con filtros por lapso, PNF, sede y turno.
 * Navegación 100% en vistas para Crear, Editar y Detalle/Horarios. Solo alertas y confirmaciones en modales.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X, Calendar, Clock, Users } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DataActionsDropdown from '@/components/ui/DataActionsDropdown.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';

import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { Column } from '@/types/dataTable';

type LapsoOpt = { id_la: number; nombre_la: string };
type PnfOpt = { id_pnf: number; nombre_pnf: string; codigo_pnf: string };
type SedeOpt = { id_sed: number; nombre_sed: string };

const props = defineProps<{
    secciones: any;
    lapsos: LapsoOpt[];
    pnfs: PnfOpt[];
    sedes: SedeOpt[];
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_sec', label: 'Sección', align: 'left' },
    { key: 'tipo_seccion', label: 'Tipo', align: 'center', width: 'w-24' },
    { key: 'nombre_pnf', label: 'PNF / Carrera', align: 'left' },
    { key: 'nivel_sec', label: 'Nivel', align: 'center', width: 'w-24' },
    { key: 'turno_sec', label: 'Turno', align: 'center', width: 'w-28' },
    { key: 'cupo_maximo_sec', label: 'Cupos', align: 'center', width: 'w-20' },
    { key: 'nombre_sed', label: 'Sede', align: 'left' },
    { key: 'nombre_la', label: 'Lapso', align: 'left' },
    { key: 'status', label: 'Status', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-64' },
];

const {
    data: seccionesData,
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
    props.secciones ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'seccion.index', listRouteName: 'secciones', perPageDefault: 10 }
);

const esActivo = (v: any) => v === true || v === 1 || v === '1' || v === 't';

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row.status) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la sección "${row.nombre_sec}"?`, `Confirmar ${accion}`)) {
        router.delete(route('seccion.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};
</script>

<template>
    <Head>
        <title>Secciones y Oferta Académica</title>
    </Head>

    <AppLayout :breadcrumbs="[{ title: 'Secciones', subtitle: 'Gestión y Planificación de Secciones', href: route('seccion.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- NOTIFICACIÓN FLASH EN MODAL/ALERTA -->
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="seccion" @close="closeAlert" />

            <!-- BARRA DE FILTROS Y ACCIONES -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter
                        class="w-full flex-1"
                        :model-value="filterValues.search ?? ''"
                        placeholder="Buscar por sección, lapso, PNF, turno o sede..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <select
                        :value="filterValues.laa_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('laa_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todos los lapsos</option>
                        <option v-for="l in props.lapsos" :key="l.id_la" :value="l.id_la">{{ l.nombre_la }}</option>
                    </select>

                    <select
                        :value="filterValues.pnf_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('pnf_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todos los PNF</option>
                        <option v-for="p in props.pnfs" :key="p.id_pnf" :value="p.id_pnf">{{ p.nombre_pnf }}</option>
                    </select>

                    <select
                        :value="filterValues.sed_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('sed_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todas las sedes</option>
                        <option v-for="s in props.sedes" :key="s.id_sed" :value="s.id_sed">{{ s.nombre_sed }}</option>
                    </select>

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                    </Button>

                    <Button as-child variant="outline" size="sm" class="h-9 gap-1.5 shrink-0">
                        <Link :href="route('seccion.horario_estudiante')">
                            <Clock class="h-4 w-4 text-primary" />
                            <span>Horarios</span>
                        </Link>
                    </Button>

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('seccion.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nueva Sección
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- TABLA DE SECCIONES (NAVEGACIÓN A VISTAS) -->
            <div>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="seccionesData?.data || []"
                    :is-loading="isLoading"
                    :resultadosTotales="seccionesData.total"
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

                    <template #nombre_sec="{ row }">
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ row.nombre_sec }}</div>
                            <div v-if="row.codigo_sec" class="text-[11px] font-mono text-muted-foreground">{{ row.codigo_sec }}</div>
                        </div>
                    </template>

                    <template #tipo_seccion="{ value }">
                        <Badge variant="outline" class="text-xs font-normal">
                            {{ value || 'Regular' }}
                        </Badge>
                    </template>

                    <template #nivel_sec="{ row }">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                            T{{ row.trayecto_sec ?? row.nivel_sec ?? 1 }} / S{{ row.semestre_sec ?? 1 }}
                        </span>
                    </template>

                    <template #status="{ row, value }">
                        <StatusSwitch :status="value ?? row?.status" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <DataActionsDropdown
                            :is-modal="false"
                            editRoute="seccion.edit"
                            showRoute="seccion.show"
                            deleteRoute="seccion.destroy"
                            :id="row.id"
                            :status="esActivo(row.status) ? 1 : 0"
                            @success="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination
                :total="seccionesData?.total ?? 0"
                :per-page="seccionesData?.per_page ?? 10"
                :current-page="seccionesData?.current_page ?? 1"
                :last-page="seccionesData?.last_page ?? 1"
                :from="seccionesData?.from ?? 0"
                :to="seccionesData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
