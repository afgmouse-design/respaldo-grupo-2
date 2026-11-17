<script setup lang="ts">
/**
 * Vista de Listado: Gestión de Tipos de Lapso / Período (Módulo: Estructura Académica).
 *
 * Propósito: Muestra el catálogo de `tipo_lapso` permitiendo búsqueda, filtrado,
 * paginación y acciones directas sobre cada registro.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `tipo_lapso` (`id_tla`, `codigo_tla`, `nombre_tla`, `nomenclatura_tla`,
 *    `duracion_semanas_tla`, `descripcion_tla`, `status_id`) mediante `@/lib/status`.
 *  - 2026-11-16: Estandarización de acciones de tabla con <CrudActionButtons />.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

// Layout e UI Reutilizable
import Badge from '@/components/ui/badge/Badge.vue';
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

export interface TipoPeriodoItem {
    id_tla?: number;
    id_tpd: number;
    nombre_tla?: string;
    nombre_tpd: string;
    codigo_tla?: string | null;
    codigo_tpd?: string | null;
    nomenclatura_tla?: string | null;
    nomenclatura?: string | null;
    duracion_semanas_tla?: number | null;
    duracion_semanas_tpd?: number | null;
    descripcion_tla?: string | null;
    descripcion_tpd?: string | null;
    status_id?: number;
    status?: boolean;
    est_tpd?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    tiposPeriodo?: {
        data: TipoPeriodoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    records?: {
        data: TipoPeriodoItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    semestres?: any;
    filters?: any;
    registros_per_page?: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipos de Período', subtitle: 'Gestión de Tipos de Período', href: route().has('academico.tipo-periodo.index') ? route('academico.tipo-periodo.index') : route('semestres.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

// Obtener paginación unificada de props
const paginatedData = computed(() => {
    return props.tiposPeriodo ?? props.records ?? props.semestres ?? {
        data: [],
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
    };
});

const defaultRouteName = route().has('academico.tipo-periodo.index')
    ? 'academico.tipo-periodo.index'
    : 'semestres.index';

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
    () => paginatedData.value,
    props.filters ?? {},
    { routeName: defaultRouteName, listRouteName: 'tiposPeriodo', perPageDefault: 10 }
);

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    changePerPage(String(nuevaCantidad));
};

const isActivo = (item: unknown) => esActivo(item);

const obtenerId = (item: TipoPeriodoItem) => item.id_tla ?? item.id_tpd;

const handleToggleStatus = async (item: TipoPeriodoItem) => {
    const id = obtenerId(item);
    const nombre = item.nombre_tla ?? item.nombre_tpd;
    const activo = isActivo(item);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de período "${nombre}"?`, `Confirmar ${accion}`)) {
        if (route().has('academico.tipo-periodo.toggle_status')) {
            router.patch(route('academico.tipo-periodo.toggle_status', id), {}, { preserveScroll: true });
        } else {
            router.delete(route('semestres.destroy', id), { preserveScroll: true });
        }
    }
};

const getCreateRoute = () => {
    if (route().has('academico.tipo-periodo.create')) {
        return route('academico.tipo-periodo.create');
    }
    return route('semestres.create');
};

const getEditRoute = (id_tpd: number) => {
    if (route().has('academico.tipo-periodo.edit')) {
        return route('academico.tipo-periodo.edit', id_tpd);
    }
    return route('semestres.edit', id_tpd);
};

const getDeleteRoute = (id_tpd: number) => {
    if (route().has('academico.tipo-periodo.destroy')) {
        return route('academico.tipo-periodo.destroy', id_tpd);
    }
    return route('semestres.destroy', id_tpd);
};
</script>

<template>
    <Head>
        <title>Gestión de Tipos de Período</title>
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
                            Gestión de Tipos de Período
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Administración y catálogo de modalidades temporales académicas.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="getCreateRoute()">
                            <Plus class="h-4 w-4" />
                            Nuevo Tipo de Período
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
                            placeholder="Buscar tipo de período por nombre o código..."
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
                        { key: 'nombre_tpd', label: 'Nombre' },
                        { key: 'codigo_tpd', label: 'Código' },
                        { key: 'duracion_semanas_tpd', label: 'Duración (Semanas)' },
                        { key: 'descripcion_tpd', label: 'Descripción' },
                        { key: 'status', label: 'Estatus' },
                        { key: 'actions', label: 'Acciones' },
                    ]"
                    :skeleton-rows="5"
                />
                <Table v-else class="w-full min-w-max border-collapse">
                    <TableHeader>
                        <TableRow class="hover:bg-transparent border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Nombre</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Código</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center">Nomenclatura</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center">Duración (Semanas)</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">Descripción</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-40">Estatus</TableHead>
                            <TableHead class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto text-center w-36">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="item in (tiposPeriodo?.data ?? records?.data ?? paginatedData?.data ?? [])"
                            :key="item.id_tpd"
                            class="transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 border-b border-slate-100 dark:border-slate-900 last:border-0"
                        >
                            <TableCell class="py-3 px-4 font-medium text-slate-900 dark:text-slate-100">
                                {{ item.nombre_tpd }}
                            </TableCell>

                            <TableCell class="py-3 px-4">
                                <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted text-foreground">
                                    {{ item.codigo_tpd || 'N/A' }}
                                </span>
                            </TableCell>

                            <TableCell class="py-3 px-4 text-center">
                                <Badge
                                    v-if="item.nomenclatura"
                                    variant="outline"
                                    class="font-semibold text-xs px-2.5 py-0.5 shadow-none"
                                    :class="{
                                        'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900': item.nomenclatura === 'Semestre',
                                        'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-900': item.nomenclatura === 'Trimestre',
                                        'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900': item.nomenclatura === 'Anual',
                                    }"
                                >
                                    {{ item.nomenclatura }}
                                </Badge>
                                <span v-else class="text-muted-foreground text-xs italic">
                                    No definida
                                </span>
                            </TableCell>

                            <TableCell class="py-3 px-4 text-center">
                                <span v-if="item.duracion_semanas_tpd != null" class="inline-flex items-center justify-center font-medium">
                                    {{ item.duracion_semanas_tpd }} semanas
                                </span>
                                <span v-else class="text-muted-foreground text-xs italic">
                                    No definida
                                </span>
                            </TableCell>

                            <TableCell class="py-3 px-4 max-w-xs truncate text-muted-foreground">
                                {{ item.descripcion_tpd || 'Sin descripción' }}
                            </TableCell>

                            <TableCell class="py-3 px-4 text-center">
                                <StatusSwitch
                                    :status="isActivo(item)"
                                    @toggle="handleToggleStatus(item)"
                                />
                            </TableCell>

                            <TableCell class="py-3 px-4 text-center">
                                <CrudActionButtons
                                    :show-view="false"
                                    :edit-href="getEditRoute(obtenerId(item))"
                                    :delete-href="getDeleteRoute(obtenerId(item))"
                                    :record-id="obtenerId(item)"
                                    :record-name="item.nombre_tla ?? item.nombre_tpd"
                                    confirm-message="¿Estás seguro de que deseas eliminar este registro? Desaparecerá de la lista."
                                />
                            </TableCell>
                        </TableRow>

                        <!-- Estado Vacío -->
                        <TableEmpty
                            v-if="!(tiposPeriodo?.data?.length || records?.data?.length || paginatedData?.data?.length)"
                            :colspan="6"
                            class="text-muted-foreground text-sm"
                        >
                            Crea el primer tipo de período para comenzar
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="paginatedData?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="paginatedData?.current_page ?? 1"
                :last-page="paginatedData?.last_page ?? 1"
                :from="paginatedData?.from ?? 0"
                :to="paginatedData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
