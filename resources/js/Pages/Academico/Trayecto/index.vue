<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Trayectos (Módulo: Estructura Académica / Régimen).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar los trayectos
 * y niveles académicos registrados en el SOGAC. Cumple la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * trayectos académicos directamente con confirmación modal.
 */
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
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
    GraduationCap,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    Layers,
    Calendar,
} from 'lucide-vue-next';

interface TipoPeriodoItem {
    id_tpd: number;
    nombre_tpd: string;
    codigo_tpd?: string;
    nomenclatura?: string;
    status?: boolean;
}

interface TrayectoItem {
    id_tra: number;
    codigo_tra?: string;
    nombre_tra: string;
    numero_orden_tra: number;
    tpd_id: number;
    tipo_periodo?: TipoPeriodoItem | null;
    status: boolean;
    est_tra?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    trayectos: {
        data: TrayectoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    tiposPeriodo: TipoPeriodoItem[];
    filters: {
        search?: string;
        tpd_id?: string | number;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Trayectos', subtitle: 'Niveles Académicos', href: route('academico.trayecto.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_tra', label: 'ID', align: 'center', width: 'w-16' },
    { key: 'numero_orden_tra', label: 'Orden', align: 'center', width: 'w-20' },
    { key: 'codigo_tra', label: 'Código', align: 'center', width: 'w-28' },
    { key: 'nombre_tra', label: 'Nombre del Trayecto / Nivel', align: 'left' },
    { key: 'tipo_periodo', label: 'Modalidad Académica', align: 'left', width: 'w-56' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones Directas', align: 'center', width: 'w-44' },
];

const {
    data: trayectosData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.trayectos,
    props.filters,
    { routeName: 'academico.trayecto.index', listRouteName: 'trayectos', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const trayectos = computed(() => props.trayectos);

watch(
    () => props.trayectos,
    (newTrayectos) => {
        if (newTrayectos) {
            trayectosData.value = newTrayectos;
        }
    },
    { deep: true, immediate: true }
);

const isActivo = (row: any) => {
    const val = row?.est_tra ?? row?.status;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const alternarEstatus = async (row: TrayectoItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el trayecto "${row.nombre_tra}"?`, `Confirmar ${accion} trayecto`)) {
        router.patch(route('academico.trayecto.toggle_status', row.id_tra), {}, { preserveScroll: true });
    }
};

const eliminarRegistro = async (ruta: string) => {
    if (await confirmDialog('¿Estás seguro de que deseas eliminar este registro? Desaparecerá de la lista.')) {
        router.delete(ruta);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Trayectos - Estructura Académica" />

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
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Trayectos
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Niveles, tramos y trayectos formativos organizados por modalidad y tipo de período académico.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.trayecto.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Trayecto
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
                            placeholder="Buscar por trayecto o código..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <div class="w-full sm:w-64">
                        <select
                            :value="filterValues.tpd_id ?? 'all'"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            @change="setFilter('tpd_id', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="all">Todas las Modalidades</option>
                            <option
                                v-for="tpd in tiposPeriodo"
                                :key="tpd.id_tpd"
                                :value="tpd.id_tpd"
                            >
                                {{ tpd.nombre_tpd }} {{ tpd.nomenclatura ? `(${tpd.nomenclatura})` : '' }}
                            </option>
                        </select>
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
                    :data="trayectos?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="trayectos?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ORDEN -->
                    <template #numero_orden_tra="{ row }">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-muted text-muted-foreground">
                            {{ row.numero_orden_tra }}
                        </span>
                    </template>

                    <!-- SLOT: CÓDIGO -->
                    <template #codigo_tra="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            {{ row.codigo_tra || 'N/A' }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE TRAYECTO -->
                    <template #nombre_tra="{ row }">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-900 dark:text-slate-100">
                                {{ row.nombre_tra }}
                            </span>
                            <Badge
                                v-if="/semestre|sem/i.test(row.nombre_tra) || row.tipo_periodo?.nomenclatura === 'Semestre'"
                                variant="outline"
                                class="text-[10px] px-1.5 py-0 bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 font-semibold"
                            >
                                Semestre
                            </Badge>
                        </div>
                    </template>

                    <!-- SLOT: MODALIDAD / TIPO DE PERÍODO -->
                    <template #tipo_periodo="{ row }">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                <span class="text-sm text-foreground">
                                    {{ row.tipo_periodo?.nombre_tpd || 'No asignada' }}
                                </span>
                            </div>
                            <span v-if="row.tipo_periodo?.nomenclatura" class="text-xs text-muted-foreground ml-5 font-medium">
                                Nomenclatura: {{ row.tipo_periodo?.nomenclatura }}
                            </span>
                        </div>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`trayecto-${(row || item)?.id_tra}-${((row || item)?.est_tra ?? (row || item)?.status)}`"
                            :status="(row || item)?.est_tra ?? (row || item)?.status"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE -->
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button
                                as-child
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400"
                                title="Ver ficha"
                            >
                                <Link :href="route('academico.trayecto.show', row.id_tra)">
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
                                <Link :href="route('academico.trayecto.edit', row.id_tra)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Eliminar -->
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Eliminar trayecto"
                                @click="eliminarRegistro(route('academico.trayecto.destroy', row.id_tra))"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="trayectos?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="trayectos?.current_page ?? 1"
                :last-page="trayectos?.last_page ?? 1"
                :from="trayectos?.from ?? 0"
                :to="trayectos?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
