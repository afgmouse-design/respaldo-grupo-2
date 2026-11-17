<script setup lang="ts">
/**
 * Vista de Listado: Maestro de Horarios (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 *
 * Propósito: Listado, filtrado reactivo y paginación de bloques de horarios de clases.
 * Integrado con CrudActionButtons, StatusSwitch y navegación SPA.
 *
 * Historial:
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarClock, Clock, Plus, School, X } from 'lucide-vue-next';
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
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
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
    () => props.horarios ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'horario.index', listRouteName: 'horarios', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
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

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <CalendarClock class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Horarios de Clases
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Planificación de bloques horarios, aulas asignadas y carga académica por sección.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('horario.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Horario
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
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
                </div>
            </div>

            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
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
                            <span>{{ row.codigo_aula_aul || row.nombre_aula || 'Sin aula' }}</span>
                        </div>
                    </template>

                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="horario.show"
                            edit-route="horario.edit"
                            delete-route="horario.destroy"
                            :record-id="row.id_hor ?? row.id"
                            :record-name="`${row.dia_semana_hor} (${formatTime(row.hora_inicio_hor)} - ${formatTime(row.hora_fin_hor)})`"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
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

