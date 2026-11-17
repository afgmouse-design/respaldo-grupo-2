<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Lapsos Académicos (Módulo: Estructura Académica).
 *
 * Propósito: Muestra el catálogo de lapsos académicos (`lapso_academico`) vinculados a sus
 * años académicos y tipos de lapso, permitiendo búsqueda, filtrado y alternado de estatus.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`id_laa`, `ana_id`, `tla_id`, `codigo_laa`,
 *    `nombre_laa`, `fecha_inicio_laa`, `fecha_fin_laa`, `status_id` con `@/lib/status`).
 *  - 2026-11-16: Estandarización de acciones de tabla con <CrudActionButtons />.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, Plus, X } from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Layout e UI Reutilizable
import { Button } from '@/components/ui/button';
import CrudActionButtons from '@/components/ui/CrudActionButtons.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableEmpty } from '@/components/ui/table';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

// Composables y Tipos
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';

export interface LapsoAcademicoItem {
    id_laa: number;
    tla_id?: number | null;
    ana_id?: number | null;
    codigo_laa?: string | null;
    nombre_laa: string;
    fecha_inicio_laa?: string | null;
    fecha_fin_laa?: string | null;
    fase_operativa_laa?: string | null;
    status_id: number;
    nombre_ana?: string | null;
    ano_status_id?: number | null;
    nombre_tla?: string | null;
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
        id_ana: number;
        nombre_ana: string;
    }>;
    filters?: {
        search?: string;
        ana_id?: number | string;
        status_id?: string;
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

const isActivo = (item: LapsoAcademicoItem) => esActivo(item);

const handleToggleStatus = async (item: LapsoAcademicoItem) => {
    const activo = isActivo(item);
    const accion = activo ? 'desactivar' : 'activar';
    const advertencia = activo ? ' Al desactivarlo, sus cohortes y asignaciones de sede asociadas también se desactivarán en cascada.' : '';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el lapso académico "${item.nombre_laa}"?${advertencia}`)) {
        router.patch(route('academico.lapso-academico.alternar-estatus', item.id_laa), {}, {
            preserveScroll: true,
        });
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
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('academico.lapso-academico.create')">
                            <Plus class="h-4 w-4" />
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
                            :value="filterValues.ana_id ?? 'all'"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            @change="setFilter('ana_id', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="all">Todos los años académicos</option>
                            <option
                                v-for="ano in props.anosSelect"
                                :key="ano.id_ana"
                                :value="ano.id_ana"
                            >
                                {{ ano.nombre_ana }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <TabFilter
                        :model-value="filterValues.status_id ?? 'all'"
                        :options="[
                            { value: 'all', label: 'Todos' },
                            { value: '1', label: 'Activos' },
                            { value: '2', label: 'Inactivos' },
                        ]"
                        default-value="all"
                        @update:model-value="(val: string) => setFilter('status_id', val)"
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
                        { key: 'id_laa', label: 'ID' },
                        { key: 'nombre_ana', label: 'Año Padre' },
                        { key: 'nombre_laa', label: 'Nombre Lapso' },
                        { key: 'fecha_inicio_laa', label: 'Inicio' },
                        { key: 'fecha_fin_laa', label: 'Fin' },
                        { key: 'status_id', label: 'Estatus' },
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
                            :key="item.id_laa"
                            class="transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 border-b border-slate-100 dark:border-slate-900 last:border-0"
                        >
                            <TableCell class="py-3.5 px-4 text-center font-mono text-xs text-muted-foreground">
                                {{ item.id_laa }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted text-foreground">
                                    {{ item.nombre_ana || 'Sin asignar' }}
                                </span>
                            </TableCell>

                            <TableCell class="py-3.5 px-4 font-semibold text-slate-900 dark:text-slate-100">
                                <div>{{ item.nombre_laa }}</div>
                                <div v-if="item.codigo_laa || item.nombre_tla" class="text-xs font-normal text-muted-foreground">
                                    {{ [item.codigo_laa, item.nombre_tla].filter(Boolean).join(' · ') }}
                                </div>
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_inicio_laa || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-muted-foreground">
                                {{ item.fecha_fin_laa || 'N/A' }}
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-center">
                                <StatusSwitch
                                    :status="isActivo(item)"
                                    @toggle="handleToggleStatus(item)"
                                />
                            </TableCell>

                            <TableCell class="py-3.5 px-4 text-center">
                                <CrudActionButtons
                                    show-route="academico.lapso-academico.show"
                                    edit-route="academico.lapso-academico.edit"
                                    delete-route="academico.lapso-academico.destroy"
                                    :record-id="item.id_laa"
                                    :record-name="item.nombre_laa"
                                    confirm-message="¿Estás seguro de que deseas eliminar este registro? Desaparecerá de la lista."
                                />
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
