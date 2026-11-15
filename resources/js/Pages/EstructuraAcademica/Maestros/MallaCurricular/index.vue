<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Mallas Curriculares (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar los planes de estudio
 * y mallas curriculares registradas en el SOGAC siguiendo el diseño estándar de PNF / Trayecto / Sede.
 */
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Network,
    Plus,
    Eye,
    Pencil,
    Trash2,
    Power,
    X,
    GraduationCap,
    Calendar,
    FileText,
} from 'lucide-vue-next';

interface PnfItem {
    id_pnf: number;
    codigo_pnf?: string | null;
    nombre_pnf: string;
}

interface MallaCurricularItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac: string | null;
    version_pensum_mac?: string | null;
    fecha_aprobacion_mac: string | null;
    observacion_mac: string | null;
    pnf_id: number;
    creditos_minimos_tsu_mac?: number | null;
    horas_minimas_tsu_mac?: number | null;
    materias_obligatorias_tsu_mac?: number | null;
    creditos_minimos_ing_mac?: number | null;
    horas_minimas_ing_mac?: number | null;
    materias_obligatorias_ing_mac?: number | null;
    creditos_minimos_prosecucion_mac?: number | null;
    pnf?: PnfItem | null;
    status_id: number;
    status: boolean;
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
    { key: 'pnf', label: 'PNF', align: 'left', width: 'w-64' },
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
    const activo = (row as any).status ?? ((row as any).status_id === 1);
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

const eliminarRegistro = async (ruta: string, row?: MallaCurricularItem) => { 
    const nombre = row?.nombre_mac ?? 'esta malla curricular';
    if (await confirmDialog(
        `¿Está seguro de que desea dar de baja "${nombre}"?`,
        'Confirmar baja lógica'
    )) { 
        router.delete(ruta, { preserveScroll: true }); 
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
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.malla-curricular.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Malla Curricular
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
                                <span>TSU: <strong class="text-foreground">{{ row.creditos_minimos_tsu_mac || 96 }} UC</strong></span>
                                <span>•</span>
                                <span>Ingeniería: <strong class="text-foreground">{{ row.creditos_minimos_ing_mac || 180 }} UC</strong></span>
                            </div>
                        </div>
                    </template>

                    <!-- SLOT: PNF -->
                    <template #pnf="{ row }">
                        <div v-if="row.pnf" class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300">
                            <GraduationCap class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <span class="truncate" :title="row.pnf.nombre_pnf">
                                {{ row.pnf.nombre_pnf }}
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground italic">
                            Sin PNF asignado
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
                            :key="`malla-${row.id_mac}-${row.status ?? row.status_id}`"
                            :status="row.status ?? (row.status_id === 1)"
                            @toggle="alternarEstatus(row)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE -->
                    <template #actions="{ row, item }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button
                                as-child
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/40"
                                title="Ver detalles"
                            >
                                <Link :href="route().has('academico.malla-curricular.show') ? route('academico.malla-curricular.show', (row || item).id_mac) : route('malla_curricular.show', (row || item).id_mac)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar -->
                            <Button
                                as-child
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400"
                                title="Editar datos"
                            >
                                <Link :href="route().has('academico.malla-curricular.edit') ? route('academico.malla-curricular.edit', (row || item).id_mac) : route('malla_curricular.edit', (row || item).id_mac)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Desactivar / Baja Lógica -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                :title="(row || item)?.status ? 'Desactivar malla' : 'Activar malla'"
                                @click="alternarEstatus(row || item)"
                            >
                                <Power class="h-4 w-4" />
                            </Button>
                        </div>
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

