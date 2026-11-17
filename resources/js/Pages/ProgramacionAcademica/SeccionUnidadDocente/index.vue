<script setup lang="ts">
/**
 * Vista Standalone: Asignación Sección - Unidad Curricular - Docente (`carga_academica` / SUD).
 *
 * Propósito: Listado paginado estandarizado de la carga académica asignada por sección,
 * unidad curricular y docente, con filtros por lapso, sección, búsqueda textual y estatus.
 *
 * Historial:
 *  - 2026-11-16: Refactorización integral a CRUD estándar SOGAC con <CrudActionButtons />,
 *    <StatusSwitch />, <DataTable />, <DataPagination /> y rutas dedicadas (`create` / `edit`).
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpenCheck, Clock, Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Badge } from '@/components/ui/badge';
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
import type { BreadcrumbItem } from '@/types/navigation';

interface LapsoFilterOpt {
    id_laa: number;
    codigo_laa?: string;
    nombre_laa: string;
}

interface SeccionFilterOpt {
    id_sec: number;
    codigo_sec?: string;
    nombre_sec: string;
    semestre_sec?: number;
    trayecto_sec?: number;
    laa_id?: number;
    lapso_academico?: { id_laa: number; nombre_laa: string } | null;
    programa?: { id_prg: number; nombre_prg: string; siglas_prg?: string } | null;
}

const props = defineProps<{
    asignaciones: any;
    secciones: SeccionFilterOpt[];
    lapsos: LapsoFilterOpt[];
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Operaciones', href: '#' },
    { title: 'Sección - Unidad - Docente', subtitle: 'Carga Académica', href: route('seccion_unidad_docente.index') },
];

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'seccion', label: 'Sección / Trayecto', align: 'left' },
    { key: 'unidad_curricular', label: 'Unidad Curricular', align: 'left' },
    { key: 'docente', label: 'Docente Asignado', align: 'left' },
    { key: 'horas_semanales_caa', label: 'Carga / Contratación', align: 'center', width: 'w-40' },
    { key: 'horarios_count', label: 'Horarios', align: 'center', width: 'w-28' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: asignacionesData,
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
    () => props.asignaciones ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'seccion_unidad_docente.index', listRouteName: 'asignaciones', perPageDefault: 10 }
);

const seccionesDisponiblesFiltro = computed(() => {
    const laaFiltro = Number(filterValues.value.laa_id || 0);
    if (!laaFiltro) return props.secciones;
    return props.secciones.filter((s) => Number(s.laa_id || 0) === laaFiltro);
});

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    const nombre = row.etiqueta_registro || `la asignación #${row.id_caa ?? row.id}`;

    if (await confirmDialog(`¿Está seguro de que desea ${accion} ${nombre}?`, `Confirmar ${accion}`)) {
        router.patch(
            route('seccion_unidad_docente.alternar_estatus', row.id_caa ?? row.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => fetchData(),
            }
        );
    }
};
</script>

<template>
    <Head title="Sección - Unidad - Docente | Programación Académica" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <SuccessAlert
                :show="showSuccessAlert"
                :id="itemId"
                :message="message"
                entity="carga académica"
                @close="closeAlert"
            />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col justify-between gap-4 border-b pb-5 sm:flex-row sm:items-center dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <BookOpenCheck class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Sección - Unidad - Docente
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Gestión de asignaciones docentes por sección y unidad curricular, respetando unicidad de materia y límite de dedicación por lapso.
                    </p>
                </div>

                <Button as-child size="default" class="gap-2 shadow-sm">
                    <Link :href="route('seccion_unidad_docente.create')">
                        <Plus class="h-4 w-4" aria-hidden="true" />
                        Nueva Asignación
                    </Link>
                </Button>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col items-stretch justify-between gap-2 lg:flex-row lg:items-center">
                <div class="flex w-full items-center gap-3">
                    <SearchFilter
                        class="w-full flex-1"
                        :model-value="filterValues.search ?? ''"
                        placeholder="Buscar por sección, unidad curricular, cédula o docente..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
                </div>

                <div class="flex w-full flex-wrap items-center justify-end gap-2 sm:flex-nowrap lg:w-auto">
                    <select
                        :value="filterValues.laa_id ?? 'all'"
                        aria-label="Filtrar por lapso académico"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('laa_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todos los lapsos</option>
                        <option v-for="lapso in lapsos" :key="lapso.id_laa" :value="lapso.id_laa">
                            {{ lapso.nombre_laa }}
                        </option>
                    </select>

                    <select
                        :value="filterValues.sec_id ?? 'all'"
                        aria-label="Filtrar por sección"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('sec_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todas las secciones</option>
                        <option v-for="sec in seccionesDisponiblesFiltro" :key="sec.id_sec" :value="sec.id_sec">
                            {{ sec.codigo_sec ? `[${sec.codigo_sec}] ` : '' }}{{ sec.nombre_sec }}
                        </option>
                    </select>

                    <Button
                        v-if="hasActiveFilters"
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label="Limpiar filtros"
                        class="h-9 w-9 shrink-0 text-red-500"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />
                    </Button>
                </div>
            </div>

            <!-- TABLA ESTÁNDAR -->
            <div class="overflow-hidden rounded-xl border bg-card shadow-xs">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="asignacionesData?.data || []"
                    :is-loading="isLoading"
                    :resultadosTotales="asignacionesData.total"
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

                    <template #id="{ row }">
                        <span class="font-mono text-xs text-muted-foreground">#{{ row.id_caa ?? row.id }}</span>
                    </template>

                    <template #seccion="{ row }">
                        <div class="space-y-0.5">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                {{ row.seccion?.nombre_sec || 'Sección sin nombre' }}
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-muted-foreground">
                                <span v-if="row.seccion?.codigo_sec" class="font-mono">{{ row.seccion.codigo_sec }}</span>
                                <span aria-hidden="true">·</span>
                                <span>T{{ row.seccion?.trayecto_sec ?? 1 }}/S{{ row.seccion?.semestre_sec ?? 1 }}</span>
                                <span v-if="row.seccion?.lapso_academico?.nombre_laa" class="text-primary">
                                    · {{ row.seccion.lapso_academico.nombre_laa }}
                                </span>
                            </div>
                        </div>
                    </template>

                    <template #unidad_curricular="{ row }">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-1.5">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                    {{ row.unidad_curricular?.codigo_unc || 'S/C' }}
                                </span>
                                <span class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ row.unidad_curricular?.nombre_unc || 'Unidad sin nombre' }}
                                </span>
                            </div>
                            <div v-if="row.unidad_curricular?.tipo_unidad_curricular?.nombre_tuc" class="text-[11px] text-muted-foreground">
                                {{ row.unidad_curricular.tipo_unidad_curricular.nombre_tuc }}
                            </div>
                        </div>
                    </template>

                    <template #docente="{ row }">
                        <div class="space-y-0.5">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                {{ row.docente_nombre }}
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-muted-foreground">
                                <span class="font-mono">{{ row.docente_cedula || 'S/C' }}</span>
                                <template v-if="row.dedicacion_nombre">
                                    <span aria-hidden="true">·</span>
                                    <span class="font-medium text-primary/90">
                                        {{ row.dedicacion_nombre }} ({{ row.horas_maximas_ded ?? 0 }}h máx.)
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template #horas_semanales_caa="{ row }">
                        <div class="flex flex-col items-center gap-1">
                            <span class="font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ row.horas_semanales_caa }}h / sem
                            </span>
                            <Badge variant="outline" class="text-[11px] font-normal">
                                {{ row.tipo_contratacion_caa || 'Titular' }}
                            </Badge>
                        </div>
                    </template>

                    <template #horarios_count="{ row }">
                        <Link
                            v-if="row.sec_id"
                            :href="route('seccion.show', row.sec_id)"
                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-primary hover:bg-primary/10"
                        >
                            <Clock class="h-3.5 w-3.5" aria-hidden="true" />
                            <span>{{ row.horarios_count ?? 0 }}</span>
                        </Link>
                        <span v-else class="text-xs text-muted-foreground">{{ row.horarios_count ?? 0 }}</span>
                    </template>

                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <CrudActionButtons
                            :show-view="false"
                            edit-route="seccion_unidad_docente.edit"
                            delete-route="seccion_unidad_docente.destroy"
                            :record-id="row.id_caa ?? row.id"
                            :record-name="row.etiqueta_registro"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination
                :total="asignacionesData?.total ?? 0"
                :per-page="asignacionesData?.per_page ?? 10"
                :current-page="asignacionesData?.current_page ?? 1"
                :last-page="asignacionesData?.last_page ?? 1"
                :from="asignacionesData?.from ?? 0"
                :to="asignacionesData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
