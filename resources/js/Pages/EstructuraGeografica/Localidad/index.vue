<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Localidades (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar las localidades,
 * caseríos y sectores en el sistema SOGAC. Cumple estrictamente la regla de CERO MODALES mediante
 * navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Baja Lógica y Estandarización de Acciones):
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * localidades directamente desde el listado con confirmación modal.
 * Actualización (Alineación sogac_mio):
 * Se reemplaza la lectura booleana legacy `status`/`est_loc` por `status_id` mediante `@/lib/status`.
 * - 2026-11-16: Estandarización global con <CrudActionButtons />, botón "Nueva Localidad" y columna "Acciones".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Compass, Home, Plus, X } from 'lucide-vue-next';
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

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
}

interface ParroquiaRef {
    id_par: number;
    nombre_par: string;
    mun_id: number;
    municipio?: MunicipioRef;
}

interface LocalidadItem {
    id_loc: number;
    nombre_loc: string;
    par_id: number;
    status_id?: number;
    status?: boolean | number;
    est_loc?: boolean;
    puede_activarse?: boolean;
    parroquia?: ParroquiaRef;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    localidades: {
        data: LocalidadItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    municipios: MunicipioRef[];
    parroquias: ParroquiaRef[];
    filters: {
        search?: string;
        mun_id?: number | string;
        par_id?: number | string;
        status?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Localidades', subtitle: 'Sectores y Comunidades', href: route('geografia.localidad.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_loc', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'nombre_loc', label: 'Nombre de la Localidad / Caserío / Sector', align: 'left' },
    { key: 'adscripcion_rel', label: 'Parroquia / Municipio', align: 'left', width: 'w-64' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: localidadesData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.localidades,
    props.filters,
    { routeName: 'geografia.localidad.index', listRouteName: 'localidades', perPageDefault: 5 }
);

// Inicializar el valor reactivo del select leyendo el parámetro de las props (sin watchers automáticos)
const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

// Parroquias filtradas reactivamente por el municipio seleccionado en los filtros
const parroquiasFiltradas = computed(() => {
    if (!filterValues.value.mun_id) {
        return props.parroquias;
    }
    return props.parroquias.filter(p => p.mun_id === Number(filterValues.value.mun_id));
});

const onMunicipioFilterChange = (val: string) => {
    setFilter('mun_id', val);
    if (val && filterValues.value.par_id) {
        const existe = props.parroquias.some(p => p.id_par === Number(filterValues.value.par_id) && p.mun_id === Number(val));
        if (!existe) {
            setFilter('par_id', '');
        }
    }
};

// Propiedad computada reactiva ligada directamente a los props frescos de Inertia
const localidades = computed(() => props.localidades);

// Sincronización explícita reactiva cuando Inertia refresca props.localidades
watch(
    () => props.localidades,
    (newLocalidades) => {
        if (newLocalidades) {
            localidadesData.value = newLocalidades;
        }
    },
    { deep: true, immediate: true }
);

// Lectura reactiva directa del campo status_id de la fila
const isActivo = (row: unknown) => esActivo(row);

// Determina si la localidad puede ser activada (sus entidades superiores Parroquia, Municipio, Estado y País están activas)
const puedeActivarse = (row: LocalidadItem) => {
    if (row.puede_activarse !== undefined) {
        return Boolean(row.puede_activarse);
    }
    if (row.parroquia) {
        const parroquiaActiva = esActivo(row.parroquia);
        const municipioActivo = row.parroquia.municipio ? esActivo(row.parroquia.municipio) : true;
        return parroquiaActiva && municipioActivo;
    }
    return true;
};

const alternarEstatus = async (row: LocalidadItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const msg = `¿Desea ${accion} la localidad "${row.nombre_loc}"?`;

    if (await confirmDialog(msg, `Confirmar ${accion} localidad`)) {
        router.patch(route('geografia.localidad.status', row.id_loc), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Localidades - Estructura Geográfica" />

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
                            <Home class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Localidades y Sectores
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Quinto nivel de la división territorial. Caseríos, barrios y sectores residenciales del SOGAC.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('geografia.localidad.create')">
                            <Plus class="h-4 w-4" />
                            Nueva Localidad
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
                            placeholder="Buscar sector, caserío..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <!-- Filtro por Municipio -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.mun_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => onMunicipioFilterChange((e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todos los municipios</option>
                            <option
                                v-for="mun in props.municipios"
                                :key="mun.id_mun"
                                :value="String(mun.id_mun)"
                            >
                                {{ mun.nombre_mun }}
                            </option>
                        </select>
                    </div>

                    <!-- Filtro dependiente por Parroquia padre -->
                    <div class="w-full sm:w-48">
                        <select
                            :value="filterValues.par_id ?? ''"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                            @change="(e: Event) => setFilter('par_id', (e.target as HTMLSelectElement).value)"
                        >
                            <option value="">Todas las parroquias</option>
                            <option
                                v-for="parr in parroquiasFiltradas"
                                :key="parr.id_par"
                                :value="String(parr.id_par)"
                            >
                                {{ parr.nombre_par }}
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
                    :data="localidades?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="localidades?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ADSCRIPCIÓN JERÁRQUICA -->
                    <template #adscripcion_rel="{ row }">
                        <div class="space-y-0.5">
                            <Link
                                v-if="row.parroquia"
                                :href="route('geografia.parroquia.show', row.parroquia.id_par)"
                                class="inline-flex items-center gap-1.5 font-medium text-slate-900 dark:text-slate-100 hover:text-primary hover:underline"
                            >
                                <Compass class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ row.parroquia.nombre_par }}
                            </Link>
                            <span v-else class="text-xs text-muted-foreground italic">Parroquia no asignada</span>

                            <div v-if="row.parroquia?.municipio" class="text-xs text-muted-foreground flex items-center gap-1">
                                <Building2 class="h-3 w-3" />
                                <span>{{ row.parroquia.municipio.nombre_mun }}</span>
                            </div>
                        </div>
                    </template>

                    <!-- SLOT: ESTATUS (Interruptor reactivo con status_id) -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`loc-${(row || item)?.id_loc}-${resolverStatusId(row || item)}`"
                            :status="esActivo(row || item)"
                            :disabled="!isActivo(row || item) && !puedeActivarse(row || item)"
                            :title="!isActivo(row || item) && !puedeActivarse(row || item) ? 'No se puede activar mientras su parroquia o municipio superior esté inactivo' : undefined"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="geografia.localidad.show"
                            edit-route="geografia.localidad.edit"
                            delete-route="geografia.localidad.destroy"
                            :record-id="row.id_loc"
                            :record-name="row.nombre_loc"
                            confirm-message="⚠️ ADVERTENCIA: ¿Estás seguro de eliminar este registro? Si tiene dependencias (hijos registrados), también serán eliminados en cascada para mantener la integridad del sistema."
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="localidades?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="localidades?.current_page ?? 1"
                :last-page="localidades?.last_page ?? 1"
                :from="localidades?.from ?? 0"
                :to="localidades?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

