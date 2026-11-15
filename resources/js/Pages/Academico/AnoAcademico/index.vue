<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Año Académico (Módulo: Estructura Académica).
 *
 * Propósito: Muestra el catálogo de años académicos permitiendo búsqueda,
 * filtrado por estatus, paginación reactiva y acciones directas (editar, alternar estatus).
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * años académicos directamente con confirmación modal.
 */
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X, Eye, Pencil, Power, Calendar } from 'lucide-vue-next';

// Layout e UI Reutilizable
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableEmpty } from '@/components/ui/table';
import DataPagination from '@/components/ui/DataPagination.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Button } from '@/components/ui/button';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

// Filtros
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';

// Composables y Tipos
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { BreadcrumbItem } from '@/types/navigation';

export interface AnoAcademicoItem {
    id_ano: number;
    nombre_ano: string;
    fecha_inicio_ano?: string | null;
    fecha_fin_ano?: string | null;
    status: boolean | number;
}

const props = defineProps<{
    anos: {
        data: AnoAcademicoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    filters?: {
        search?: string;
        status?: string;
        per_page?: number | string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Años Académicos', subtitle: 'Gestión de Años Académicos', href: route('academico.ano-academico.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const {
    isLoading,
    filterValues,
    perPage,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.anos,
    props.filters ?? {},
    { routeName: 'academico.ano-academico.index', listRouteName: 'anos', perPageDefault: 10 }
);

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    changePerPage(String(nuevaCantidad));
};

const isActivo = (item: AnoAcademicoItem) => {
    const val = item.status;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const handleToggleStatus = async (item: AnoAcademicoItem) => {
    const activo = isActivo(item);
    const accion = activo ? 'desactivar' : 'activar';
    const advertencia = activo ? ' Al desactivarlo, sus lapsos académicos, cohortes y asignaciones de sede también se desactivarán en cascada.' : '';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el año académico "${item.nombre_ano}"?${advertencia}`)) {
        router.patch(route('academico.ano-academico.alternar-estatus', item.id_ano), {}, {
            preserveScroll: true,
        });
    }
};

const eliminarRegistro = async (ruta: string) => {
    if (await confirmDialog('¿Estás seguro de que deseas eliminar este registro? Desaparecerá de la lista.')) {
        router.delete(ruta, { preserveScroll: true });
    }
};
</script>

<template>
    <Head>
        <title>Gestión de Años Académicos</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ALERTA FLASH -->
            <SuccessAlert
                v-if="showSuccessAlert"
                :message="message"
                @close="closeAlert"
            />

            <!-- ENCABEZADO DE PÁGINA -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Gestión de Años Académicos
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo y administración de períodos anuales regulares de la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.ano-academico.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Nuevo Año Académico
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="w-full sm:w-80">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar año por nombre (ej: 2026)..."
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
            <div class="bg-card rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <TableSkeleton
                    v-if="isLoading"
                    :columns="[
                        { key: 'id_ano', label: 'ID' },
                        { key: 'nombre_ano', label: 'Nombre' },
                        { key: 'fecha_inicio_ano', label: 'Fecha Inicio' },
                        { key: 'fecha_fin_ano', label: 'Fecha Fin' },
                        { key: 'status', label: 'Estatus' },
                        { key: 'actions', label: 'Acciones' },
                    ]"
                    :skeleton-rows="5"
                />
                <Table v-else class="w-full min-w-max border-collapse">
                    <TableHeader>
                        <TableRow class="hover:bg-transparent border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-16">ID</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Nombre</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Inicio</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Fin</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-40">Estatus</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-36">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="item in props.anos?.data || []"
                            :key="item.id_ano"
                            class="transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 border-b border-slate-100 dark:border-slate-900 last:border-0"
                        >
                            <TableCell class="py-3.5 px-4 text-center font-mono text-xs text-muted-foreground">
                                {{ item.id_ano }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 font-semibold text-slate-900 dark:text-slate-100">
                                {{ item.nombre_ano }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_inicio_ano || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_fin_ano || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-center">
                                <StatusSwitch
                                    :status="item.status"
                                    @toggle="handleToggleStatus(item)"
                                />
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Botón Ver Detalle -->
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-slate-600 hover:text-primary dark:text-slate-400"
                                        title="Ver detalle del año académico"
                                    >
                                        <Link :href="route('academico.ano-academico.show', item.id_ano)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>

                                    <!-- Botón Editar -->
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400"
                                        title="Editar año académico"
                                    >
                                        <Link :href="route('academico.ano-academico.edit', item.id_ano)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>

                                    <!-- Botón Eliminar -->
                                    <button
                                        type="button"
                                        @click="eliminarRegistro(route('academico.ano-academico.destroy', item.id_ano))"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-md text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950/50 transition-colors"
                                        title="Eliminar registro"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                            <line x1="10" x2="10" y1="11" y2="17" />
                                            <line x1="14" x2="14" y1="11" y2="17" />
                                        </svg>
                                    </button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <!-- Estado Vacío -->
                        <TableEmpty
                            v-if="!props.anos?.data?.length"
                            :colspan="6"
                            class="text-muted-foreground text-sm"
                        >
                            Crea el primer año académico para comenzar
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="props.anos?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="props.anos?.current_page ?? 1"
                :last-page="props.anos?.last_page ?? 1"
                :from="props.anos?.from ?? 0"
                :to="props.anos?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
