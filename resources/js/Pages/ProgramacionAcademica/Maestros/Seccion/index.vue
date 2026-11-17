<script setup lang="ts">
/**
 * Vista Standalone: Maestro de Secciones y Horarios (Programación Académica).
 * Propósito: Listado paginado con filtros por lapso, PNF, sede y turno.
 * Navegación 100% en vistas para Crear, Editar y Detalle/Horarios. Solo alertas y confirmaciones en modales.
 * Historial:
 *  - 2026-11-15: Se agrega importación faltante de `computed` desde `vue` (corrige ReferenceError en tiempo de ejecución al cargar /secciones).
 *  - 2026-11-16: Estandarización global con <CrudActionButtons /> en reemplazo de DataActionsDropdown.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, Plus, Users, X } from 'lucide-vue-next';
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
import {
    obtenerIdPrograma,
    obtenerNombrePrograma,
} from '@/lib/academico';
import type { ProgramaCatalogoItem } from '@/lib/academico';
import { esActivo } from '@/lib/status';
import type { Column } from '@/types/dataTable';

type LapsoOpt = { id_laa?: number; id_la?: number; nombre_laa?: string; nombre_la?: string };
type SedeOpt = { id_sed: number; nombre_sed: string };

const props = defineProps<{
    secciones: any;
    lapsos: LapsoOpt[];
    programas?: ProgramaCatalogoItem[];
    pnfs?: ProgramaCatalogoItem[];
    sedes: SedeOpt[];
    filters: any;
}>();

const catalogoProgramas = computed(() => props.programas ?? props.pnfs ?? []);
const obtenerIdLapsoOpt = (l: LapsoOpt) => l.id_laa ?? l.id_la ?? 0;
const obtenerNombreLapsoOpt = (l: LapsoOpt) => l.nombre_laa ?? l.nombre_la ?? '';

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'nombre_sec', label: 'Sección', align: 'left' },
    { key: 'tipo_seccion', label: 'Tipo', align: 'center', width: 'w-24' },
    { key: 'nombre_prg', label: 'Programa Académico', align: 'left' },
    { key: 'nivel_sec', label: 'Nivel', align: 'center', width: 'w-24' },
    { key: 'turno_sec', label: 'Turno', align: 'center', width: 'w-28' },
    { key: 'cupo_maximo_sec', label: 'Cupos', align: 'center', width: 'w-20' },
    { key: 'nombre_sed', label: 'Sede', align: 'left' },
    { key: 'nombre_laa', label: 'Lapso', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
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
    () => props.secciones ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'seccion.index', listRouteName: 'secciones', perPageDefault: 10 }
);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
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

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Users class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Secciones y Oferta Académica
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Gestión y planificación de secciones por lapso académico, programa, sede y turno.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="default" class="gap-1.5 shrink-0">
                        <Link :href="route('seccion.horario_estudiante')">
                            <Clock class="h-4 w-4 text-primary" />
                            <span>Horarios</span>
                        </Link>
                    </Button>

                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('seccion.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Sección
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS Y ACCIONES -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
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
                        <option v-for="l in props.lapsos" :key="obtenerIdLapsoOpt(l)" :value="obtenerIdLapsoOpt(l)">{{ obtenerNombreLapsoOpt(l) }}</option>
                    </select>

                    <select
                        :value="filterValues.prg_id ?? filterValues.pnf_id ?? 'all'"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="setFilter('prg_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">Todos los programas</option>
                        <option v-for="p in catalogoProgramas" :key="obtenerIdPrograma(p)" :value="obtenerIdPrograma(p)">{{ obtenerNombrePrograma(p) }}</option>
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

                </div>
            </div>

            <!-- TABLA DE SECCIONES (NAVEGACIÓN A VISTAS) -->
            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
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

                    <template #tipo_seccion="{ row, value }">
                        <Badge variant="outline" class="text-xs font-normal">
                            {{ row.nombre_tis || value || 'Regular' }}
                        </Badge>
                    </template>

                    <template #nombre_prg="{ row }">
                        <span>{{ row.nombre_prg ?? row.nombre_pnf ?? 'N/A' }}</span>
                    </template>

                    <template #turno_sec="{ row, value }">
                        <span>{{ row.nombre_tur || value || 'N/A' }}</span>
                    </template>

                    <template #nombre_laa="{ row }">
                        <span>{{ row.nombre_laa ?? row.nombre_la ?? 'N/A' }}</span>
                    </template>

                    <template #nivel_sec="{ row }">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                            T{{ row.trayecto_sec ?? row.nivel_sec ?? 1 }} / S{{ row.semestre_sec ?? 1 }}
                        </span>
                    </template>

                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>

                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="seccion.show"
                            edit-route="seccion.edit"
                            delete-route="seccion.destroy"
                            :record-id="row.id_sec ?? row.id"
                            :record-name="row.nombre_sec"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
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
