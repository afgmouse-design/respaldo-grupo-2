<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Estados (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar los estados
 * y provincias en el sistema SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Baja Lógica en Cascada y Estandarización de Acciones):
 * Se reemplazó el botón alternador de estatus por el botón estandarizado de eliminación con ícono Trash2
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * estados directamente desde el listado con confirmación modal e integridad en cascada.
 * - 2026-11-16: Estandarización global con <CrudActionButtons />, botón "Nuevo Estado" y columna "Acciones".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Globe, MapPin, Plus, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
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
import { esActivo, resolverStatusId } from '@/lib/status';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoItem {
    id_esd: number;
    nombre_esd: string;
    iso_esd?: string | null;
    pai_id: number;
    status_id?: number;
    status?: boolean | number;
    puede_activarse?: boolean;
    pais?: PaisRef;
    municipios_count?: number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    estados: {
        data: EstadoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    paises: PaisRef[];
    filters: {
        search?: string;
        pai_id?: number | string;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Estados', subtitle: 'Entidades Federales', href: route('geografia.estado.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_esd', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_esd', label: 'Nombre del Estado / Provincia', align: 'left' },
    { key: 'pais_rel', label: 'País Perteneciente', align: 'left', width: 'w-52' },
    { key: 'municipios_count', label: 'Municipios', align: 'center', width: 'w-40' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: estadosData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.estados,
    props.filters,
    { routeName: 'geografia.estado.index', listRouteName: 'estados', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const estados = computed(() => props.estados);

// Sincronización explícita reactiva cuando Inertia refresca props.estados
watch(
    () => props.estados,
    (newEstados) => {
        if (newEstados) {
            estadosData.value = newEstados;
        }
    },
    { deep: true, immediate: true }
);

const isActivo = (row: any) => esActivo(row);

// Determina si el estado puede ser activado (su entidad superior está activa)
const puedeActivarse = (row: EstadoItem) => {
    if (row.puede_activarse !== undefined) {
        return Boolean(row.puede_activarse);
    }
    if (row.pais) {
        return esActivo(row.pais);
    }
    return true;
};

const alternarEstatus = async (row: EstadoItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const msg = activo
        ? `¿Desea desactivar el estado "${row.nombre_esd}"? Sus municipios, parroquias y localidades dependientes también se desactivarán en cascada.`
        : `¿Desea activar el estado "${row.nombre_esd}"?`;

    if (await confirmDialog(msg, `Confirmar ${accion} estado`)) {
        router.patch(route('geografia.estado.status', row.id_esd), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Estados - Estructura Geográfica" />

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
                            <MapPin class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo de Estados y Provincias
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Segundo nivel de la división territorial. Agrupa y coordina municipios institucionales.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('geografia.estado.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Estado
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <!-- Búsqueda por texto -->
                    <div class="w-full sm:w-72">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar estado o país..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <!-- Selector de filtro de País padre -->
                    <div class="w-full sm:w-60">
                        <select
                            :value="filterValues.pai_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => setFilter('pai_id', (e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todos los países</option>
                            <option
                                v-for="pais in props.paises"
                                :key="pais.id_pai"
                                :value="String(pais.id_pai)"
                            >
                                {{ pais.nombre_pai }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
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
                    :data="estados?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="estados?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: NOMBRE CON CÓDIGO ISO -->
                    <template #nombre_esd="{ row }">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ row.nombre_esd }}</span>
                            <Badge v-if="row.iso_esd" variant="outline" class="font-mono text-xs">
                                {{ row.iso_esd }}
                            </Badge>
                        </div>
                    </template>

                    <!-- SLOT: PAÍS PERTENECIENTE -->
                    <template #pais_rel="{ row }">
                        <Link
                            v-if="row.pais"
                            :href="route('geografia.pais.show', row.pais.id_pai)"
                            class="inline-flex items-center gap-1.5 font-medium text-primary hover:underline"
                        >
                            <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                            {{ row.pais.nombre_pai }}
                        </Link>
                        <span v-else class="text-xs text-muted-foreground italic">No asignado</span>
                    </template>

                    <!-- SLOT: MUNICIPIOS COUNT -->
                    <template #municipios_count="{ row }">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                            {{ row.municipios_count ?? 0 }} {{ (row.municipios_count === 1) ? 'municipio' : 'municipios' }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (`status_id`) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`estado-${(row || item)?.id_esd}-${resolverStatusId(row || item)}`"
                            :status="resolverStatusId(row || item)"
                            :disabled="!isActivo(row || item) && !puedeActivarse(row || item)"
                            :title="!isActivo(row || item) && !puedeActivarse(row || item) ? 'No se puede activar mientras su país superior esté inactivo' : undefined"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="geografia.estado.show"
                            edit-route="geografia.estado.edit"
                            delete-route="geografia.estado.destroy"
                            :record-id="row.id_esd"
                            :record-name="row.nombre_esd"
                            confirm-message="⚠️ ADVERTENCIA: ¿Estás seguro de eliminar este registro? Si tiene dependencias (hijos registrados), también serán eliminados en cascada para mantener la integridad del sistema."
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="estados?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="estados?.current_page ?? 1"
                :last-page="estados?.last_page ?? 1"
                :from="estados?.from ?? 0"
                :to="estados?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

