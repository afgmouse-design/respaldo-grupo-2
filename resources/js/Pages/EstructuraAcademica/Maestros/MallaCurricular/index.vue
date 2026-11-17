<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Mallas Curriculares (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar los planes de estudio
 * y mallas curriculares registradas en el SOGAC siguiendo el diseño estándar de PNF / Trayecto / Sede.
 *
 * Historial:
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> y botón superior "Nueva Malla Curricular".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Calendar,
    FileText,
    GraduationCap,
    Network,
    Plus,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
import type { BreadcrumbItem } from '@/types/navigation';

interface ProgramaItem {
    id_prg?: number;
    id_pnf?: number;
    codigo_prg?: string | null;
    codigo_ministerio_prg?: string | null;
    codigo_pnf?: string | null;
    nombre_prg?: string;
    nombre_pnf?: string;
}

interface MallaCurricularItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac: string | null;
    version_pensum_mac?: string | null;
    fecha_aprobacion_mac: string | null;
    observacion_mac: string | null;
    prg_id?: number;
    pnf_id?: number;
    total_credito_tsu_mac?: number | null;
    total_horas_tsu_mac?: number | null;
    total_unidades_tsu_mac?: number | null;
    total_credito_ing_mac?: number | null;
    total_horas_ing_mac?: number | null;
    total_unidades_ing_mac?: number | null;
    total_credito_prosecucion_mac?: number | null;
    creditos_minimos_tsu_mac?: number | null;
    creditos_minimos_ing_mac?: number | null;
    programa?: ProgramaItem | null;
    pnf?: ProgramaItem | null;
    status_id?: number;
    status?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    mallas: {
        data: MallaCurricularItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        status?: string;
        status_id?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Malla Curricular', subtitle: 'Planes de Estudio', href: route('academico.malla-curricular.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_mac', label: 'ID', align: 'center', width: 'w-16' },
    { key: 'nombre_mac', label: 'Nombre Malla', align: 'left' },
    { key: 'programa', label: 'Programa / PNF', align: 'left', width: 'w-64' },
    { key: 'codigo_gaceta_mac', label: 'Gaceta', align: 'center', width: 'w-36' },
    { key: 'fecha_aprobacion_mac', label: 'Fecha', align: 'center', width: 'w-32' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-28' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: mallasData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.mallas,
    props.filters,
    { routeName: 'academico.malla-curricular.index', listRouteName: 'mallas', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const mallas = computed(() => props.mallas);

watch(
    () => props.mallas,
    (newMallas) => {
        if (newMallas) {
            mallasData.value = newMallas;
        }
    },
    { deep: true, immediate: true }
);

const alternarEstatus = async (row: MallaCurricularItem) => {
    const activo = esActivo(row);
    const accion = activo ? 'desactivar' : 'activar';

    if (await confirmDialog(
        `¿Está seguro de que desea ${accion} la malla curricular "${row.nombre_mac}"?`,
        `Confirmar ${accion} de malla curricular`
    )) {
        const rutaStatus = route().has('academico.malla-curricular.status')
            ? route('academico.malla-curricular.status', row.id_mac)
            : route('malla_curricular.status', row.id_mac);
        router.patch(rutaStatus, {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mallas Curriculares - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ALERTA FLASH -->
            <SuccessAlert
                v-if="showSuccessAlert"
                :message="message"
                @close="closeAlert"
            />

            <!-- ENCABEZADO DE PÁGINA STANDALONE -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Network class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Mallas Curriculares
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Diseños curriculares, resoluciones ministeriales y planes de estudio cabecera por PNF.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('academico.malla-curricular.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Malla Curricular
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="w-full sm:w-72">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar por nombre, gaceta o PNF..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <TabFilter
                        :model-value="filterValues.status ?? 'all'"
                        :options="[
                            { value: 'all', label: 'Todos' },
                            { value: 'true', label: 'Activos' },
                            { value: 'false', label: 'Inactivos' },
                        ]"
                        default-value="all"
                        @update:model-value="(val: string) => setFilter('status', val)"
                    />

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        title="Limpiar filtros"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- TABLA DE DATOS -->
            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeleton-rows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="mallas?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="mallas?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ID -->
                    <template #id_mac="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            #{{ row.id_mac }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE MALLA -->
                    <template #nombre_mac="{ row }">
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span>{{ row.nombre_mac }}</span>
                                <Badge v-if="row.version_pensum_mac" variant="outline" class="font-mono text-[10px]">
                                    {{ row.version_pensum_mac }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-muted-foreground">
                                <span>TSU: <strong class="text-foreground">{{ row.total_credito_tsu_mac ?? row.creditos_minimos_tsu_mac ?? 0 }} UC</strong></span>
                                <span>•</span>
                                <span>Ingeniería: <strong class="text-foreground">{{ row.total_credito_ing_mac ?? row.creditos_minimos_ing_mac ?? 0 }} UC</strong></span>
                            </div>
                        </div>
                    </template>

                    <!-- SLOT: PROGRAMA / PNF -->
                    <template #programa="{ row }">
                        <div v-if="row.programa || row.pnf" class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300">
                            <GraduationCap class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <span class="truncate" :title="row.programa?.nombre_prg || row.pnf?.nombre_pnf">
                                {{ row.programa?.nombre_prg || row.pnf?.nombre_pnf }}
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground italic">
                            Sin programa asignado
                        </span>
                    </template>

                    <!-- SLOT: GACETA -->
                    <template #codigo_gaceta_mac="{ row }">
                        <span v-if="row.codigo_gaceta_mac" class="inline-flex items-center gap-1 font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            <FileText class="h-3 w-3 text-muted-foreground" />
                            {{ row.codigo_gaceta_mac }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground italic">
                            N/A
                        </span>
                    </template>

                    <!-- SLOT: FECHA APROBACIÓN -->
                    <template #fecha_aprobacion_mac="{ row }">
                        <div v-if="row.fecha_aprobacion_mac" class="flex items-center justify-center gap-1 text-xs text-muted-foreground">
                            <Calendar class="h-3 w-3" />
                            <span>{{ row.fecha_aprobacion_mac }}</span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground italic">
                            N/A
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row }">
                        <StatusSwitch
                            :key="`malla-${row.id_mac}-${row.status_id ?? row.status}`"
                            :status="esActivo(row)"
                            @toggle="alternarEstatus(row)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row, item }">
                        <CrudActionButtons
                            :show-href="route().has('academico.malla-curricular.show') ? route('academico.malla-curricular.show', (row || item).id_mac) : route('malla_curricular.show', (row || item).id_mac)"
                            :edit-href="route().has('academico.malla-curricular.edit') ? route('academico.malla-curricular.edit', (row || item).id_mac) : route('malla_curricular.edit', (row || item).id_mac)"
                            :delete-href="route().has('academico.malla-curricular.destroy') ? route('academico.malla-curricular.destroy', (row || item).id_mac) : route('malla_curricular.destroy', (row || item).id_mac)"
                            :record-id="(row || item).id_mac"
                            :record-name="(row || item).nombre_mac"
                            confirm-title="Confirmar baja lógica"
                            :confirm-message="`¿Está seguro de que desea dar de baja &quot;${(row || item).nombre_mac ?? 'esta malla curricular'}&quot;?`"
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="mallas?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="mallas?.current_page ?? 1"
                :last-page="mallas?.last_page ?? 1"
                :from="mallas?.from ?? 0"
                :to="mallas?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

