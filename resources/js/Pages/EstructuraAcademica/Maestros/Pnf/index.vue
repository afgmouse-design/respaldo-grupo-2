<script setup lang="ts">
/**
 * Vista Standalone: Listado General de PNF (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar los Programas Nacionales
 * de Formación registrados en el SOGAC siguiendo el diseño estándar de Trayecto / Sede.
 *
 * Historial:
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> y botón superior "Nuevo PNF".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { GraduationCap, Plus, X } from 'lucide-vue-next';
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
import { esActivo } from '@/lib/status';
import type { Column } from '@/types/dataTable';
import type { BreadcrumbItem } from '@/types/navigation';

interface PnfItem {
    id_prg: number;
    id_pnf?: number;
    codigo_prg?: string | null;
    codigo_ministerio_prg?: string | null;
    codigo_pnf?: string | null;
    nombre_prg: string;
    nombre_pnf?: string;
    siglas_prg?: string | null;
    nombre_tpg?: string | null;
    status_id?: number;
    status?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    pnfs?: {
        data: PnfItem[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
    };
    programas?: {
        data: PnfItem[];
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
        status_id?: string;
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'PNF / Programas', subtitle: 'Oferta Académica', href: route('academico.pnf.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_prg', label: 'ID', align: 'center', width: 'w-20' },
    { key: 'codigo_prg', label: 'Código', align: 'center', width: 'w-36' },
    { key: 'nombre_prg', label: 'Nombre del Programa / PNF', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-32' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const obtenerId = (row: PnfItem) => row.id_prg ?? row.id_pnf ?? 0;
const obtenerNombre = (row: PnfItem) => row.nombre_prg ?? row.nombre_pnf ?? 'Programa';
const obtenerCodigo = (row: PnfItem) => row.codigo_prg ?? row.codigo_ministerio_prg ?? row.codigo_pnf ?? null;

const {
    data: pnfsData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.programas ?? props.pnfs!,
    props.filters,
    { routeName: 'academico.pnf.index', listRouteName: 'programas', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const pnfs = computed(() => props.programas ?? props.pnfs);

watch(
    () => props.programas ?? props.pnfs,
    (newPnfs) => {
        if (newPnfs) {
            pnfsData.value = newPnfs;
        }
    },
    { deep: true, immediate: true }
);

const alternarEstatus = async (row: PnfItem) => {
    const activo = esActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    const advertenciaCascada = activo
        ? ' Esto también desactivará automáticamente todas sus mallas curriculares asociadas.'
        : '';

    if (await confirmDialog(
        `¿Está seguro de que desea ${accion} el programa "${obtenerNombre(row)}"?${advertenciaCascada}`,
        `Confirmar ${accion} de programa`
    )) {
        const id = obtenerId(row);
        const rutaStatus = route().has('academico.pnf.status')
            ? route('academico.pnf.status', id)
            : route('pnf.status', id);
        router.patch(rutaStatus, {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Programas Nacionales de Formación (PNF)" />

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
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Programas Nacionales de Formación y carreras académicas ofertadas en la institución.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('academico.pnf.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo PNF
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE FILTROS -->
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="w-full sm:w-72">
                        <SearchFilter
                            :model-value="filterValues.search"
                            placeholder="Buscar por código o nombre..."
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
            <div class="bg-card rounded-xl border shadow-xs overflow-hidden">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeleton-rows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="pnfs?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="pnfs?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ID -->
                    <template #id_prg="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            #{{ obtenerId(row) }}
                        </span>
                    </template>

                    <!-- SLOT: CÓDIGO -->
                    <template #codigo_prg="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            {{ obtenerCodigo(row) || 'N/A' }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE PNF -->
                    <template #nombre_prg="{ row }">
                        <div class="flex flex-col">
                            <span class="font-medium text-slate-900 dark:text-slate-100">
                                {{ obtenerNombre(row) }}
                            </span>
                            <span v-if="row.siglas_prg || row.nombre_tpg" class="text-[11px] text-muted-foreground">
                                {{ [row.siglas_prg, row.nombre_tpg].filter(Boolean).join(' · ') }}
                            </span>
                        </div>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row }">
                        <StatusSwitch
                            :key="`pnf-${obtenerId(row)}-${row.status_id ?? row.status}`"
                            :status="esActivo(row)"
                            @toggle="alternarEstatus(row)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row, item }">
                        <CrudActionButtons
                            show-route="academico.pnf.show"
                            edit-route="academico.pnf.edit"
                            delete-route="academico.pnf.destroy"
                            :record-id="obtenerId(row || item)"
                            :record-name="obtenerNombre(row || item)"
                            confirm-title="Confirmar baja lógica"
                            :confirm-message="`¿Está seguro de que desea dar de baja &quot;${obtenerNombre(row || item)}&quot;? Sus mallas curriculares asociadas también quedarán inactivas.`"
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="pnfs?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="pnfs?.current_page ?? 1"
                :last-page="pnfs?.last_page ?? 1"
                :from="pnfs?.from ?? 0"
                :to="pnfs?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
