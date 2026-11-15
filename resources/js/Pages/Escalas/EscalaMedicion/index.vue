<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Escalas de Medición (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar las escalas de calificación
 * registradas en el SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * escalas de medición directamente con confirmación modal.
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
    Calculator,
    Plus,
    Eye,
    Pencil,
    Power,
    X,
} from 'lucide-vue-next';

interface EscalaItem {
    id_esm: number;
    nombre_esm: string;
    nota_aprobatoria_esm: number;
    nota_minima_esm: number | null;
    nota_maxima_esm: number | null;
    status: boolean;
    est_esm?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    escalas: {
        data: EscalaItem[];
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
    { title: 'Escalas de Medición', subtitle: 'Catálogo Maestro', href: route().has('escalas.medicion.index') ? route('escalas.medicion.index') : (route().has('estructura_academica.escalas_medicion.index') ? route('estructura_academica.escalas_medicion.index') : route('escala_medicion.index')) },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_esm', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_esm', label: 'Nombre de la Escala', align: 'left' },
    { key: 'nota_aprobatoria_esm', label: 'Nota Aprobatoria', align: 'center', width: 'w-40' },
    { key: 'nota_minima_esm', label: 'Nota Mínima', align: 'center', width: 'w-32' },
    { key: 'nota_maxima_esm', label: 'Nota Máxima', align: 'center', width: 'w-32' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones Directas', align: 'center', width: 'w-48' },
];

const listRoute = computed(() => {
    if (route().has('escalas.medicion.index')) return 'escalas.medicion.index';
    if (route().has('estructura_academica.escalas_medicion.index')) return 'estructura_academica.escalas_medicion.index';
    return 'escala_medicion.index';
});

const {
    data: escalasData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.escalas,
    props.filters,
    { routeName: listRoute.value, listRouteName: 'escalas', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const escalas = computed(() => props.escalas);

// Sincronización explícita reactiva cuando Inertia refresca props.escalas
watch(
    () => props.escalas,
    (newEscalas) => {
        if (newEscalas) {
            escalasData.value = newEscalas;
        }
    },
    { deep: true, immediate: true }
);

// Lectura reactiva directa del campo de estatus de la fila
const isActivo = (row: any) => {
    const val = row?.status ?? row?.est_esm;
    return val === true || val === 1 || val === '1' || val === 'true';
};

// Alternar estatus con confirmación modal
const toggleEstatus = async (row: EscalaItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la escala de medición "${row.nombre_esm}"?`)) {
        const toggleRoute = route().has('escalas.medicion.toggle_status')
            ? route('escalas.medicion.toggle_status', row.id_esm)
            : (route().has('estructura_academica.escalas_medicion.toggle_status')
                ? route('estructura_academica.escalas_medicion.toggle_status', row.id_esm)
                : (route().has('escala_medicion.toggle_status')
                    ? route('escala_medicion.toggle_status', row.id_esm)
                    : route('escala_medicion.destroy', row.id_esm)));

        router.patch(toggleRoute, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const getCreateRoute = () => {
    if (route().has('escalas.medicion.create')) return route('escalas.medicion.create');
    if (route().has('estructura_academica.escalas_medicion.create')) return route('estructura_academica.escalas_medicion.create');
    return route('escala_medicion.create');
};

const getShowRoute = (id: number) => {
    if (route().has('escalas.medicion.show')) return route('escalas.medicion.show', id);
    if (route().has('estructura_academica.escalas_medicion.show')) return route('estructura_academica.escalas_medicion.show', id);
    return route('escala_medicion.show', id);
};

const getEditRoute = (id: number) => {
    if (route().has('escalas.medicion.edit')) return route('escalas.medicion.edit', id);
    if (route().has('estructura_academica.escalas_medicion.edit')) return route('estructura_academica.escalas_medicion.edit', id);
    return route('escala_medicion.edit', id);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Escalas de Medición - Estructura Académica" />

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
                            <Calculator class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Escalas de Medición
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Modelos de calificación, límites inferior y superior, y notas mínimas aprobatorias para unidades curriculares.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="getCreateRoute()">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Escala
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 max-w-md">
                    <SearchFilter
                        :model-value="filterValues.search"
                        placeholder="Buscar por nombre de escala o valor de nota..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
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
                    :data="escalas?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="escalas?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: NOMBRE ESCALA -->
                    <template #nombre_esm="{ row }">
                        <span class="font-medium text-slate-900 dark:text-slate-100">
                            {{ row.nombre_esm }}
                        </span>
                    </template>

                    <!-- SLOT: NOTA APROBATORIA -->
                    <template #nota_aprobatoria_esm="{ row }">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                            {{ Number(row.nota_aprobatoria_esm).toFixed(2) }} pts
                        </span>
                    </template>

                    <!-- SLOT: NOTA MÍNIMA -->
                    <template #nota_minima_esm="{ row }">
                        <span class="text-xs text-muted-foreground font-mono">
                            {{ row.nota_minima_esm !== null && row.nota_minima_esm !== undefined ? Number(row.nota_minima_esm).toFixed(2) : '0.00' }}
                        </span>
                    </template>

                    <!-- SLOT: NOTA MÁXIMA -->
                    <template #nota_maxima_esm="{ row }">
                        <span class="text-xs text-muted-foreground font-mono">
                            {{ row.nota_maxima_esm !== null && row.nota_maxima_esm !== undefined ? Number(row.nota_maxima_esm).toFixed(2) : '20.00' }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con est_esm / status) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`escala-${(row || item)?.id_esm}-${((row || item)?.est_esm ?? (row || item)?.status)}`"
                            :status="(row || item)?.est_esm ?? (row || item)?.status"
                            @toggle="toggleEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE (0 MODALES) -->
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400" title="Ver ficha completa">
                                <Link :href="getShowRoute(row.id_esm)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400" title="Editar datos">
                                <Link :href="getEditRoute(row.id_esm)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Alternar Estatus / Baja Lógica -->
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                :class="isActivo(row) ? 'text-red-600 hover:text-red-700 dark:text-red-400' : 'text-emerald-600 hover:text-emerald-700 dark:text-emerald-400'"
                                :title="isActivo(row) ? 'Desactivar escala' : 'Activar escala'"
                                @click="toggleEstatus(row)"
                            >
                                <Power class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="escalas?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="escalas?.current_page ?? 1"
                :last-page="escalas?.last_page ?? 1"
                :from="escalas?.from ?? 0"
                :to="escalas?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

