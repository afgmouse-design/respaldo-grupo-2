<script setup lang="ts">
/**
 * Vista de Listado: Maestro de Horarios (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 *
 * Propósito: Listado, filtrado reactivo y paginación de bloques de horarios de clases.
 * Integrado con DataActionsDropdown, StatusSwitch y navegación SPA.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X, CalendarClock, Clock, School, Layers } from 'lucide-vue-next';

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
    horarios: any;
    cargasAcademicas: Array<{ id_caa: number; descripcion_caa: string }>;
    aulas: Array<{ id_aul: number; codigo_aula_aul: string; tipo_espacio_aul?: string }>;
    diasSemana: string[];
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'dia_semana_hor', label: 'Día', align: 'center', width: 'w-28' },
    { key: 'horas', label: 'Horario', align: 'center', width: 'w-36' },
    { key: 'seccion_materia', label: 'Sección / Asignatura', align: 'left' },
    { key: 'nombre_aula', label: 'Aula / Ambiente', align: 'center', width: 'w-36' },
    { key: 'status', label: 'Status', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-48' },
];

const {
    data: horariosData,
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
    props.horarios ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'horario.index', listRouteName: 'horarios', perPageDefault: 10 }
);

const esActivo = (v: any) => v === true || v === 1 || v === '1' || v === 't';

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row.status) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} este bloque de horario?`, `Confirmar ${accion}`)) {
        router.delete(route('horario.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};

const formatTime = (timeStr?: string) => {
    if (!timeStr) return '--:--';
    return timeStr.substring(0, 5);
};
</script>

<template>
    <Head title="Maestro de Horarios - SOGAC" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Programación Académica', href: '#' },
        { title: 'Horarios', href: route('horario.index') }
    ]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="horario" @close="closeAlert" />

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter class="w-full flex-1" :model-value="filterValues.search ?? ''"
                        @update:model-value="(val: string) => setFilter('search', val)"
                        placeholder="Buscar por día, sección, asignatura o aula..." />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters">
                        <X class="h-4 w-4" />
                    </Button>

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('horario.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nuevo Horario
                        </Link>
                    </Button>
                </div>
            </div>

            <div>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable v-else :columns="columns" :data="horariosData?.data || []" :is-loading="isLoading"
                    :resultadosTotales="horariosData.total" :filtrosActivos="hasActiveFilters">
                    <template #header-actions>
                        <TabFilter :model-value="filterValues.status ?? ''" :options="[
                            { value: '', label: 'Activos' },
                            { value: '0', label: 'Inactivos' },
                            { value: 'all', label: 'Todos' },
                        ]" default-value="" @update:model-value="(val: string) => setFilter('status', val)" />
                    </template>

                    <template #dia_semana_hor="{ value }">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            {{ value }}
                        </span>
                    </template>

                    <template #horas="{ row }">
                        <span class="inline-flex items-center gap-1 text-xs font-mono font-semibold text-slate-700 dark:text-slate-300">
                            <Clock class="h-3 w-3 text-muted-foreground" />
                            {{ formatTime(row.hora_inicio_hor) }} - {{ formatTime(row.hora_fin_hor) }}
                        </span>
                    </template>

                    <template #seccion_materia="{ row }">
                        <div class="flex flex-col">
                            <span class="font-medium text-slate-900 dark:text-slate-100">
                                {{ row.nombre_unc || 'Asignatura no especificada' }}
                            </span>
                            <span class="text-xs text-muted-foreground">
                                Sección: {{ row.nombre_sec || row.codigo_sec || 'N/A' }}
                            </span>
                        </div>
                    </template>

                    <template #nombre_aula="{ row }">
                        <div class="inline-flex items-center gap-1 text-xs font-medium text-slate-800 dark:text-slate-200">
                            <School class="h-3.5 w-3.5 text-primary shrink-0" />
                            <span>{{ row.nombre_aula || 'Sin aula' }}</span>
                        </div>
                    </template>

                    <template #status="{ row, value }">
                        <StatusSwitch :status="value ?? row?.status" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <DataActionsDropdown
                            :is-modal="false"
                            editRoute="horario.edit"
                            showRoute="horario.show"
                            deleteRoute="horario.destroy"
                            :id="row.id"
                            :status="esActivo(row.status) ? 1 : 0"
                            @success="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination :total="horariosData?.total ?? 0" :per-page="horariosData?.per_page ?? 10"
                :current-page="horariosData?.current_page ?? 1" :last-page="horariosData?.last_page ?? 1"
                :from="horariosData?.from ?? 0" :to="horariosData?.to ?? 0" :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))" @page-change="onPageChange(Number($event))" />
        </div>
    </AppLayout>
</template>

