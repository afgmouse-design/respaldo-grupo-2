<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Sedes Universitarias (Módulo 2 - Organización Institucional y Sedes).
 *
 * Propósito: Página independiente para consultar, buscar, filtrar y gestionar los núcleos,
 * campus y sedes de la universidad matriz en el SOGAC. Cumple con la arquitectura Zero-Modal
 * mediante navegación directa a páginas dedicadas de creación, detalle y edición.
 *
 * Actualización (Estandarización de Acciones y Baja Lógica):
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * sedes universitarias directamente desde el listado con confirmación modal.
 *
 * Actualización (Agrupación de Columnas y Eliminación de Scroll):
 * Se unificaron la denominación, siglas y código ministerial en una misma celda vertical,
 * se integró la ubicación geográfica en dos líneas y se removió la columna redundante de
 * universidad matriz para que la tabla ajuste al 100% del contenedor sin barra de desplazamiento horizontal.
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
    Building,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    MapPin,
    GraduationCap,
    Tag,
} from 'lucide-vue-next';

interface UniversidadRelacion {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
}

interface LocalidadRelacion {
    id_loc: number;
    nombre_loc: string;
    parroquia?: {
        id_par: number;
        nombre_par: string;
        municipio?: {
            id_mun: number;
            nombre_mun: string;
            estado?: {
                id_esd: number;
                nombre_esd: string;
            };
        };
    };
}

interface CondicionRelacion {
    id_cdn: number;
    nombre_cdn: string;
}

interface SedeItem {
    id_sed: number;
    nombre_sed: string;
    siglas_sed?: string | null;
    codigo_ministerio_sed?: string | null;
    uni_id: number;
    loc_id: number;
    cdn_id?: number | null;
    status: boolean;
    est_sed?: boolean;
    universidad?: UniversidadRelacion | null;
    localidad?: LocalidadRelacion | null;
    condicion?: CondicionRelacion | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    sedes: {
        data: SedeItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    universidades: UniversidadRelacion[];
    filters: {
        search?: string;
        status?: string;
        uni_id?: string | number;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('organizacion.sede.index') },
    { title: 'Sedes', subtitle: 'Núcleos y Extensiones', href: route('organizacion.sede.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_sed', label: 'ID', align: 'center', width: 'w-14' },
    { key: 'nombre_sed', label: 'Denominación de la Sede', align: 'left' },
    { key: 'localidad', label: 'Ubicación Geográfica', align: 'left' },
    { key: 'condicion', label: 'Condición', align: 'center', width: 'w-36' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-28' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-32' },
];

const {
    data: sedesData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.sedes,
    props.filters,
    { routeName: 'organizacion.sede.index', listRouteName: 'sedes', perPageDefault: 5 }
);

const perPage = ref(Number(props.filters?.per_page ?? 5));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const sedes = computed(() => props.sedes);

watch(
    () => props.sedes,
    (newVal) => {
        if (newVal) {
            sedesData.value = newVal;
        }
    },
    { deep: true, immediate: true }
);

const isActivo = (row: any) => {
    const val = row?.status ?? row?.est_sed;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const alternarEstatus = async (row: SedeItem) => {
    const activo = isActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la sede "${row.nombre_sed}"?`, `Confirmar ${accion} sede`)) {
        const rutaStatus = route().has('organizacion.sede.status')
            ? route('organizacion.sede.status', row.id_sed)
            : route('sede.status', row.id_sed);
        router.patch(rutaStatus, {}, { preserveScroll: true });
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
        <Head title="Sedes Universitarias - Organización Institucional" />

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
                            <Building class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Sedes y Núcleos Universitarios
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo de sedes rectorales, núcleos y extensiones territoriales de la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('organizacion.sede.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Sede
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 max-w-md">
                    <SearchFilter
                        :model-value="filterValues.search"
                        placeholder="Buscar por nombre, siglas o código ministerial..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- FILTRO POR ESTATUS -->
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
                    :data="sedes?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="sedes?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: NOMBRE (AGRUPA DENOMINACIÓN, SIGLAS Y CÓDIGO MPPEU) -->
                    <template #nombre_sed="{ row }">
                        <div class="flex flex-col gap-1 py-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                    {{ row.nombre_sed }}
                                </span>
                                <span v-if="row.siglas_sed" class="font-mono text-[11px] font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ row.siglas_sed }}
                                </span>
                            </div>
                            <span v-if="row.codigo_ministerio_sed" class="text-xs text-muted-foreground">
                                MPPEU: {{ row.codigo_ministerio_sed }}
                            </span>
                        </div>
                    </template>

                    <!-- SLOT: LOCALIDAD (AGRUPA LOCALIDAD Y ENTIDAD FEDERAL) -->
                    <template #localidad="{ row }">
                        <div v-if="row.localidad" class="flex flex-col gap-0.5 text-xs text-slate-700 dark:text-slate-300 py-0.5">
                            <div class="flex items-center gap-1.5 font-medium">
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                <span>{{ row.localidad.nombre_loc }}</span>
                            </div>
                            <span v-if="row.localidad.parroquia?.municipio?.estado" class="text-muted-foreground text-[11px] pl-5">
                                {{ row.localidad.parroquia.municipio.estado.nombre_esd }}
                            </span>
                        </div>
                        <span v-else class="text-xs text-muted-foreground italic">
                            Sin asignar
                        </span>
                    </template>

                    <!-- SLOT: CONDICION -->
                    <template #condicion="{ row }">
                        <span v-if="row.condicion" class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                            <Tag class="h-3 w-3 text-muted-foreground" />
                            {{ row.condicion.nombre_cdn }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground italic">
                            No especificada
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :key="`sede-${(row || item)?.id_sed}-${((row || item)?.est_sed ?? (row || item)?.status)}`"
                            :status="(row || item)?.est_sed ?? (row || item)?.status"
                            @toggle="alternarEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES DIRECTAS STANDALONE (0 MODALES) -->
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- Ver Ficha Detallada -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400" title="Ver ficha completa">
                                <Link :href="route('organizacion.sede.show', row.id_sed)">
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Editar Sede -->
                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400" title="Editar datos">
                                <Link :href="route('organizacion.sede.edit', row.id_sed)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <!-- Eliminar Sede (Baja Lógica) -->
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                                title="Eliminar sede"
                                @click="eliminarRegistro(route().has('organizacion.sede.destroy') ? route('organizacion.sede.destroy', row.id_sed) : route('sede.destroy', row.id_sed))"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="sedes?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="sedes?.current_page ?? 1"
                :last-page="sedes?.last_page ?? 1"
                :from="sedes?.from ?? 0"
                :to="sedes?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>

