<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Municipios (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar los municipios
 * en el sistema SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Baja Lógica en Cascada y Estandarización de Acciones):
 * Se reemplazó el botón alternador de estatus por el botón estandarizado de eliminación con ícono Trash2
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * municipios directamente desde el listado con confirmación modal e integridad en cascada.
 * Actualización (Alineación sogac_mio):
 * Se reemplaza la lectura booleana legacy `status`/`est_mun` por `status_id` mediante `@/lib/status`.
 * - 2026-11-16: Estandarización global con <CrudActionButtons />, botón "Nuevo Municipio" y columna "Acciones".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Compass, Globe, MapPin, Plus, X } from 'lucide-vue-next';
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
import { esActivo, resolverStatusId } from '@/lib/status';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
    pai_id: number;
    pais?: PaisRef;
}

interface MunicipioItem {
    id_mun: number;
    nombre_mun: string;
    esd_id: number;
    status_id?: number;
    status?: boolean | number;
    est_mun?: boolean;
    puede_activarse?: boolean;
    estado?: EstadoRef;
    parroquias_count?: number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    municipios: {
        data: MunicipioItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    paises: PaisRef[];
    estados: EstadoRef[];
    filters: {
        search?: string;
        pai_id?: number | string;
        esd_id?: number | string;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Municipios', subtitle: 'Catálogo de Municipios', href: route('geografia.municipio.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_mun', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_mun', label: 'Nombre del Municipio', align: 'left' },
    { key: 'ubicacion_rel', label: 'Estado / País', align: 'left', width: 'w-60' },
    { key: 'parroquias_count', label: 'Parroquias', align: 'center', width: 'w-36' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: municipiosData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.municipios,
    props.filters,
    { routeName: 'geografia.municipio.index', listRouteName: 'municipios', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Estados filtrados reactivamente por el país seleccionado en los filtros
const estadosFiltrados = computed(() => {
    if (!filterValues.value.pai_id) {
        return props.estados;
    }
    return props.estados.filter(e => e.pai_id === Number(filterValues.value.pai_id));
});

const onPaisFilterChange = (val: string) => {
    setFilter('pai_id', val);
    // Si el estado seleccionado ya no pertenece al nuevo país, limpiarlo
    if (val && filterValues.value.esd_id) {
        const estadoExiste = props.estados.some(e => e.id_esd === Number(filterValues.value.esd_id) && e.pai_id === Number(val));
        if (!estadoExiste) {
            setFilter('esd_id', '');
        }
    }
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const municipios = computed(() => props.municipios);

// Sincronización explícita reactiva cuando Inertia refresca props.municipios
watch(
    () => props.municipios,
    (newMunicipios) => {
        if (newMunicipios) {
            municipiosData.value = newMunicipios;
        }
    },
    { deep: true, immediate: true }
);

// Lectura reactiva directa del campo status_id de la fila
const isActivo = (row: unknown) => esActivo(row);

// Determina si el municipio puede ser activado (sus entidades superiores Estado y País están activas)
const puedeActivarse = (row: MunicipioItem) => {
    if (row.puede_activarse !== undefined) {
        return Boolean(row.puede_activarse);
    }
    if (row.estado) {
        const estadoActivo = esActivo(row.estado);
        const paisActivo = row.estado.pais ? esActivo(row.estado.pais) : true;
        return estadoActivo && paisActivo;
    }
    return true;
};

const alternarEstatus = async (row: MunicipioItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const msg = activo
        ? `¿Desea desactivar el municipio "${row.nombre_mun}"? Sus parroquias y localidades dependientes también se desactivarán en cascada.`
        : `¿Desea activar el municipio "${row.nombre_mun}"?`;

    if (await confirmDialog(msg, `Confirmar ${accion} municipio`)) {
        router.patch(route('geografia.municipio.status', row.id_mun), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Municipios - Estructura Geográfica" />

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
                            <Building2 class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Municipios
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Tercer nivel de la división territorial. Agrupa y coordina parroquias y sectores locales.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('geografia.municipio.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Municipio
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS JERÁRQUICOS -->
            <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1 flex-wrap">
                    <!-- Búsqueda por texto -->
                    <div class="w-full sm:w-64">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar municipio, estado..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <!-- Filtro por País padre -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.pai_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => onPaisFilterChange((e.target as HTMLSelectElement).value)"
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

                    <!-- Filtro dependiente por Estado padre -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.esd_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => setFilter('esd_id', (e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todos los estados</option>
                            <option
                                v-for="estado in estadosFiltrados"
                                :key="estado.id_esd"
                                :value="String(estado.id_esd)"
                            >
                                {{ estado.nombre_esd }}
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
                    :data="municipios?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="municipios?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: UBICACIÓN JERÁRQUICA -->
                    <template #ubicacion_rel="{ row }">
                        <div class="space-y-0.5">
                            <Link
                                v-if="row.estado"
                                :href="route('geografia.estado.show', row.estado.id_esd)"
                                class="inline-flex items-center gap-1.5 font-medium text-slate-900 dark:text-slate-100 hover:text-primary hover:underline"
                            >
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ row.estado.nombre_esd }}
                            </Link>
                            <span v-else class="text-xs text-muted-foreground italic">Estado no asignado</span>

                            <div v-if="row.estado?.pais" class="text-xs text-muted-foreground flex items-center gap-1">
                                <Globe class="h-3 w-3" />
                                <span>{{ row.estado.pais.nombre_pai }}</span>
                            </div>
                        </div>
                    </template>

                    <!-- SLOT: PARROQUIAS COUNT -->
                    <template #parroquias_count="{ row }">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <Compass class="h-3.5 w-3.5 text-muted-foreground" />
                            {{ row.parroquias_count ?? 0 }} {{ (row.parroquias_count === 1) ? 'parroquia' : 'parroquias' }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con status_id) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`mun-${(row || item)?.id_mun}-${resolverStatusId(row || item)}`"
                            :status="esActivo(row || item)"
                            :disabled="!isActivo(row || item) && !puedeActivarse(row || item)"
                            :title="!isActivo(row || item) && !puedeActivarse(row || item) ? 'No se puede activar mientras su estado o país superior esté inactivo' : undefined"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="geografia.municipio.show"
                            edit-route="geografia.municipio.edit"
                            delete-route="geografia.municipio.destroy"
                            :record-id="row.id_mun"
                            :record-name="row.nombre_mun"
                            confirm-message="⚠️ ADVERTENCIA: ¿Estás seguro de eliminar este registro? Si tiene dependencias (hijos registrados), también serán eliminados en cascada para mantener la integridad del sistema."
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="municipios?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="municipios?.current_page ?? 1"
                :last-page="municipios?.last_page ?? 1"
                :from="municipios?.from ?? 0"
                :to="municipios?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

