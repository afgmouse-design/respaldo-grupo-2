<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Lapsos Académicos (Módulo: Estructura Académica).
 *
 * Propósito: Muestra el catálogo de lapsos académicos vinculados a sus respectivos
 * años académicos, permitiendo búsqueda, filtrado por año y estatus, paginación y acciones.
 *
 * Actualización (Interruptor de Estado - StatusSwitch):
 * Se incorporó el suiche interactivo en la columna de Estatus para activar y desactivar
 * lapsos académicos directamente con confirmación modal.
 */
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Plus, X, Eye, Pencil, Power, Clock } from 'lucide-vue-next';

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

export interface LapsoAcademicoItem {
    id_la: number;
    ano_id?: number | null;
    nombre_la: string;
    fecha_inicio_la?: string | null;
    fecha_fin_la?: string | null;
    estado_la: boolean | number;
    nombre_ano?: string | null;
    ano_status?: boolean | number;
}

const props = defineProps<{
    lapsos: {
        data: LapsoAcademicoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    anosSelect?: Array<{
        id_ano: number;
        nombre_ano: string;
    }>;
    filters?: {
        search?: string;
        ano_id?: number | string;
        estado_la?: string;
        per_page?: number | string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
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
    () => props.lapsos,
    props.filters ?? {},
    { routeName: 'academico.lapso-academico.index', listRouteName: 'lapsos', perPageDefault: 10 }
);

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    changePerPage(String(nuevaCantidad));
};

const isActivo = (item: LapsoAcademicoItem) => {
    const val = item.estado_la;
    return val === true || val === 1 || val === '1' || val === 'true';
};

const handleToggleStatus = async (item: LapsoAcademicoItem) => {
    const activo = isActivo(item);
    const accion = activo ? 'desactivar' : 'activar';
    const advertencia = activo ? ' Al desactivarlo, sus cohortes y asignaciones de sede asociadas también se desactivarán en cascada.' : '';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el lapso académico "${item.nombre_la}"?${advertencia}`)) {
        router.patch(route('academico.lapso-academico.alternar-estatus', item.id_la), {}, {
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
        <title>Gestión de Lapsos Académicos</title>
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
                            <Clock class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Gestión de Lapsos Académicos
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Listado y administración de los períodos lectivos asociados a un año académico.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="shadow-sm">
                        <Link :href="route('academico.lapso-academico.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Nuevo Lapso Académico
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="w-full sm:w-72">
                        <SearchFilter
                            :model-value="filterValues.search ?? ''"
                            placeholder="Buscar por lapso (ej: Lapso I)..."
                            @update:model-value="(val: string) => setFilter('search', val)"
                        />
                    </div>

                    <div class="w-full sm:w-60">
                        <select
                            :value="filterValues.ano_id ?? 'all'"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            @change="setFilter('ano_id', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="all">Todos los años académicos</option>
                            <option
                                v-for="ano in props.anosSelect"
                                :key="ano.id_ano"
                                :value="ano.id_ano"
                            >
                                {{ ano.nombre_ano }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <TabFilter
                        :model-value="filterValues.estado_la ?? 'all'"
                        :options="[
                            { value: 'all', label: 'Todos' },
                            { value: 'true', label: 'Activos' },
                            { value: 'false', label: 'Inactivos' },
                        ]"
                        default-value="all"
                        @update:model-value="(val: string) => setFilter('estado_la', val)"
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
                        { key: 'id_la', label: 'ID' },
                        { key: 'nombre_ano', label: 'Año Padre' },
                        { key: 'nombre_la', label: 'Nombre Lapso' },
                        { key: 'fecha_inicio_la', label: 'Inicio' },
                        { key: 'fecha_fin_la', label: 'Fin' },
                        { key: 'estado_la', label: 'Estatus' },
                        { key: 'actions', label: 'Acciones' },
                    ]"
                    :skeleton-rows="5"
                />
                <Table v-else class="w-full min-w-max border-collapse">
                    <TableHeader>
                        <TableRow class="hover:bg-transparent border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-16">ID</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto w-44">Año Padre</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Nombre Lapso</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Inicio</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Fin</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-40">Estatus</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-36">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="item in props.lapsos?.data || []"
                            :key="item.id_la"
                            class="transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 border-b border-slate-100 dark:border-slate-900 last:border-0"
                        >
                            <TableCell class="py-3.5 px-4 text-center font-mono text-xs text-muted-foreground">
                                {{ item.id_la }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted text-foreground">
                                    {{ item.nombre_ano || 'Sin asignar' }}
                                </span>
                            </TableCell>

                            <TableCell class="py-3.5 px-4 font-semibold text-slate-900 dark:text-slate-100">
                                {{ item.nombre_la }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_inicio_la || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_fin_la || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-center">
                                <StatusSwitch
                                    :status="item.estado_la"
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
                                        title="Ver detalle del lapso académico"
                                    >
                                        <Link :href="route('academico.lapso-academico.show', item.id_la)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>

                                    <!-- Botón Editar -->
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-amber-600 hover:text-amber-700 dark:text-amber-400"
                                        title="Editar lapso académico"
                                    >
                                        <Link :href="route('academico.lapso-academico.edit', item.id_la)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>

                                    <!-- Botón Eliminar -->
                                    <button
                                        type="button"
                                        @click="eliminarRegistro(route('academico.lapso-academico.destroy', item.id_la))"
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
                            v-if="!props.lapsos?.data?.length"
                            :colspan="7"
                            class="text-muted-foreground text-sm"
                        >
                            Crea el primer lapso académico para comenzar
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="props.lapsos?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="props.lapsos?.current_page ?? 1"
                :last-page="props.lapsos?.last_page ?? 1"
                :from="props.lapsos?.from ?? 0"
                :to="props.lapsos?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
