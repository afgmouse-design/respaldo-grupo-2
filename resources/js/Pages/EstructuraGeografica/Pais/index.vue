<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Países (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar los países
 * registrados en el SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Baja Lógica en Cascada y Estandarización de Acciones):
 * Se reemplazó el botón alternador de estatus por el botón estandarizado de eliminación con ícono Trash2
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * países directamente desde el listado con confirmación modal e integridad en cascada.
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
    Globe,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    Layers,
    MapPin,
} from 'lucide-vue-next';

interface PaisItem {
    id_pai: number;
    nombre_pai: string;
    status: boolean;
    estados_count?: number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    paises: {
        data: PaisItem[];
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
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Países', subtitle: 'Catálogo Maestro', href: route('geografia.pais.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_pai', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_pai', label: 'Nombre Oficial del País', align: 'left' },
    { key: 'estados_count', label: 'Estados Federados', align: 'center', width: 'w-44' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones Directas', align: 'center', width: 'w-48' },
];

const {
    data: paisesData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.paises,
    props.filters,
    { routeName: 'geografia.pais.index', listRouteName: 'paises', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const paises = computed(() => props.paises);

// Sincronización explícita reactiva cuando Inertia refresca props.paises
watch(
    () => props.paises,
    (newPaises) => {
        if (newPaises) {
            paisesData.value = newPaises;
        }
    },
    { deep: true, immediate: true }
);

// Lectura reactiva directa del campo de estatus de la fila
const isActivo = (row: any) => {
    const val = row?.status ?? row?.est_pai;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const alternarEstatus = async (row: any) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const msg = activo
        ? `¿Desea desactivar el país "${row.nombre_pai}"? Sus estados, municipios, parroquias y localidades dependientes también se desactivarán en cascada.`
        : `¿Desea activar el país "${row.nombre_pai}"?`;

    if (await confirmDialog(msg, `Confirmar ${accion} país`)) {
        router.patch(route('geografia.pais.status', row.id_pai), {}, { preserveScroll: true });
    }
};

const eliminarRegistro = async (ruta: string) => {
    if (await confirmDialog('⚠️ ADVERTENCIA: ¿Estás seguro de eliminar este registro? Si tiene dependencias (hijos registrados), también serán eliminados en cascada para mantener la integridad del sistema.')) {
        router.delete(ruta);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Países - Estructura Geográfica" />

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
                            <Globe class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Países
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Nivel raíz de la división político-territorial institucional del sistema SOGAC.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('geografia.pais.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar País
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 max-w-md">
                    <SearchFilter
                        :model-value="filterValues.search"
                        placeholder="Buscar por nombre de país..."
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
                    :data="paises?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="paises?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ESTADOS COUNT -->
                    <template #estados_count="{ row }">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <Layers class="h-3.5 w-3.5 text-muted-foreground" />
                            {{ row.estados_count ?? 0 }} {{ (row.estados_count === 1) ? 'estado' : 'estados' }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con est_pai / status) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`pais-${(row || item)?.id_pai}-${((row || item)?.est_pai ?? (row || item)?.status)}`"
                            :status="(row || item)?.est_pai ?? (row || item)?.status"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE (0 MODALES) -->
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Detalle -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400" title="Ver ficha completa">
                                <Link :href="route('geografia.pais.show', row.id_pai)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400" title="Editar datos">
                                <Link :href="route('geografia.pais.edit', row.id_pai)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Eliminar País (Baja lógica en cascada) -->
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Eliminar país"
                                @click="eliminarRegistro(route('geografia.pais.destroy', row.id_pai))"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="paises?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="paises?.current_page ?? 1"
                :last-page="paises?.last_page ?? 1"
                :from="paises?.from ?? 0"
                :to="paises?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

