<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Parroquias (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar las parroquias
 * en el sistema SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Baja Lógica en Cascada y Estandarización de Acciones):
 * Se reemplazó el botón alternador de estatus por el botón estandarizado de eliminación con ícono Trash2
 * Actualización (2026-11-16 - Estandarización UI):
 * Se ajusta el contenedor de la tabla a `overflow-hidden` consistente con Pais/Estado/Municipio/Localidad.
 * Actualización (Alineación sogac_mio):
 * Se reemplaza la lectura booleana legacy `status`/`est_par` por `status_id` mediante `@/lib/status`.
 * - 2026-11-16: Estandarización global con <CrudActionButtons />, botón "Nueva Parroquia" y columna "Acciones".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Compass, Home, MapPin, Plus, X } from 'lucide-vue-next';
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

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
}

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
    esd_id: number;
    estado?: EstadoRef;
}

interface ParroquiaItem {
    id_par: number;
    nombre_par: string;
    mun_id: number;
    status_id?: number;
    status?: boolean | number;
    est_par?: boolean;
    puede_activarse?: boolean;
    municipio?: MunicipioRef;
    localidades_count?: number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    parroquias: {
        data: ParroquiaItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    estados: EstadoRef[];
    municipios: MunicipioRef[];
    filters: {
        search?: string;
        esd_id?: number | string;
        mun_id?: number | string;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Parroquias', subtitle: 'Catálogo de Parroquias', href: route('geografia.parroquia.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_par', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_par', label: 'Nombre de la Parroquia', align: 'left' },
    { key: 'adscripcion_rel', label: 'Municipio / Estado', align: 'left', width: 'w-60' },
    { key: 'localidades_count', label: 'Localidades', align: 'center', width: 'w-36' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: parroquiasData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.parroquias,
    props.filters,
    { routeName: 'geografia.parroquia.index', listRouteName: 'parroquias', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Municipios filtrados reactivamente por el estado seleccionado en los filtros
const municipiosFiltrados = computed(() => {
    if (!filterValues.value.esd_id) {
        return props.municipios;
    }
    return props.municipios.filter(m => m.esd_id === Number(filterValues.value.esd_id));
});

const onEstadoFilterChange = (val: string) => {
    setFilter('esd_id', val);
    if (val && filterValues.value.mun_id) {
        const existe = props.municipios.some(m => m.id_mun === Number(filterValues.value.mun_id) && m.esd_id === Number(val));
        if (!existe) {
            setFilter('mun_id', '');
        }
    }
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const parroquias = computed(() => props.parroquias);

// Sincronización explícita reactiva cuando Inertia refresca props.parroquias
watch(
    () => props.parroquias,
    (newParroquias) => {
        if (newParroquias) {
            parroquiasData.value = newParroquias;
        }
    },
    { deep: true, immediate: true }
);

// Lectura reactiva directa del campo status_id de la fila
const isActivo = (row: unknown) => esActivo(row);

// Determina si la parroquia puede ser activada (sus entidades superiores Municipio, Estado y País están activas)
const puedeActivarse = (row: ParroquiaItem) => {
    if (row.puede_activarse !== undefined) {
        return Boolean(row.puede_activarse);
    }
    if (row.municipio) {
        const municipioActivo = esActivo(row.municipio);
        const estadoActivo = row.municipio.estado ? esActivo(row.municipio.estado) : true;
        return municipioActivo && estadoActivo;
    }
    return true;
};

const alternarEstatus = async (row: ParroquiaItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const msg = activo
        ? `¿Desea desactivar la parroquia "${row.nombre_par}"? Sus localidades dependientes también se desactivarán en cascada.`
        : `¿Desea activar la parroquia "${row.nombre_par}"?`;

    if (await confirmDialog(msg, `Confirmar ${accion} parroquia`)) {
        router.patch(route('geografia.parroquia.status', row.id_par), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Parroquias - Estructura Geográfica" />

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
                            <Compass class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Parroquias
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Cuarto nivel de la división territorial. Base para la asignación de caseríos, sectores y recintos.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('geografia.parroquia.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Parroquia
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
                            placeholder="Buscar parroquia, municipio..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <!-- Filtro por Estado -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.esd_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => onEstadoFilterChange((e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todos los estados</option>
                            <option
                                v-for="estado in props.estados"
                                :key="estado.id_esd"
                                :value="String(estado.id_esd)"
                            >
                                {{ estado.nombre_esd }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro dependiente por Municipio padre -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.mun_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => setFilter('mun_id', (e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todos los municipios</option>
                            <option
                                v-for="mun in municipiosFiltrados"
                                :key="mun.id_mun"
                                :value="String(mun.id_mun)"
                            >
                                {{ mun.nombre_mun }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <TabFilter
                        :model-value="filterValues.status ?? 'all'"
                        :options="[
                            { value: 'all', label: 'Todos' },
                            { value: 'true', label: 'Activas' },
                            { value: 'false', label: 'Inactivas' },
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
                    :data="parroquias?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="parroquias?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ADSCRIPCIÓN JERÁRQUICA -->
                    <template #adscripcion_rel="{ row }">
                        <div class="space-y-0.5">
                            <Link
                                v-if="row.municipio"
                                :href="route('geografia.municipio.show', row.municipio.id_mun)"
                                class="inline-flex items-center gap-1.5 font-medium text-slate-900 dark:text-slate-100 hover:text-primary hover:underline"
                            >
                                <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ row.municipio.nombre_mun }}
                            </Link>
                            <span v-else class="text-xs text-muted-foreground italic">Municipio no asignado</span>

                            <div v-if="row.municipio?.estado" class="text-xs text-muted-foreground flex items-center gap-1">
                                <MapPin class="h-3 w-3" />
                                <span>{{ row.municipio.estado.nombre_esd }}</span>
                            </div>
                        </div>
                    </template>

                    <!-- SLOT: LOCALIDADES COUNT -->
                    <template #localidades_count="{ row }">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <Home class="h-3.5 w-3.5 text-muted-foreground" />
                            {{ row.localidades_count ?? 0 }} {{ (row.localidades_count === 1) ? 'localidad' : 'localidades' }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con status_id) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`par-${(row || item)?.id_par}-${resolverStatusId(row || item)}`"
                            :status="esActivo(row || item)"
                            :disabled="!isActivo(row || item) && !puedeActivarse(row || item)"
                            :title="!isActivo(row || item) && !puedeActivarse(row || item) ? 'No se puede activar mientras su municipio o estado superior esté inactivo' : undefined"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="geografia.parroquia.show"
                            edit-route="geografia.parroquia.edit"
                            delete-route="geografia.parroquia.destroy"
                            :record-id="row.id_par"
                            :record-name="row.nombre_par"
                            confirm-message="⚠️ ADVERTENCIA: ¿Estás seguro de eliminar este registro? Si tiene dependencias (hijos registrados), también serán eliminados en cascada para mantener la integridad del sistema."
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="parroquias?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="parroquias?.current_page ?? 1"
                :last-page="parroquias?.last_page ?? 1"
                :from="parroquias?.from ?? 0"
                :to="parroquias?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

