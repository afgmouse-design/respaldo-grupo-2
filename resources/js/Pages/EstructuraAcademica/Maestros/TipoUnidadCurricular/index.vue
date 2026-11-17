<script setup lang="ts">
/**
 * Vista Standalone: Listado General de Tipos de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Página para consultar, buscar, filtrar y gestionar los tipos de unidad curricular
 * registrados en el SOGAC siguiendo el diseño estándar de Trayecto / Sede.
 *
 * Historial:
 * - 2026-11-16: Estandarización global con <CrudActionButtons /> y botón superior "Nuevo Tipo de UC".
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, Plus, X } from 'lucide-vue-next';
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

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
    status_id?: number;
    status?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    tipos: {
        data: TipoUnidadCurricularItem[];
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
        per_page?: number | string;
        registros_per_page?: number | string;
    };
    perPage?: number | string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipo de Unidad Curricular', subtitle: 'Clasificación', href: route('academico.tipo-unidad-curricular.index') },
];

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const columns: Column[] = [
    { key: 'id_tuc', label: 'ID', align: 'center', width: 'w-24' },
    { key: 'nombre_tuc', label: 'Tipo de Unidad Curricular', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-36' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: tiposData,
    isLoading,
    filterValues,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
} = useDataFetching(
    () => props.tipos,
    props.filters,
    { routeName: 'academico.tipo-unidad-curricular.index', listRouteName: 'tipos', perPageDefault: 10 }
);

const perPage = ref(Number(props.filters?.per_page ?? 10));

const cambiarPorPagina = (nuevaCantidad: string | number) => {
    perPage.value = Number(nuevaCantidad);
    changePerPage(String(nuevaCantidad));
};

const tipos = computed(() => props.tipos);

watch(
    () => props.tipos,
    (newTipos) => {
        if (newTipos) {
            tiposData.value = newTipos;
        }
    },
    { deep: true, immediate: true }
);

const toggleEstatus = async (row: TipoUnidadCurricularItem) => {
    const activo = esActivo(row);
    const accion = activo ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de UC "${row.nombre_tuc}"?`)) {
        router.patch(route('academico.tipo-unidad-curricular.toggle_status', row.id_tuc), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tipos de Unidad Curricular - Estructura Académica" />

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
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo Maestro de Tipos de Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Clasificación y tipologías de unidades curriculares (obligatorias, electivas, proyectos, acreditables).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('academico.tipo-unidad-curricular.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Tipo de UC
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
                            placeholder="Buscar por tipo de unidad..."
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
                    :data="tipos?.data || []"
                    :is-loading="isLoading"
                    :resultados-totales="tipos?.total ?? 0"
                    :filtros-activos="hasActiveFilters"
                >
                    <!-- SLOT: ID -->
                    <template #id_tuc="{ row }">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-muted/60 text-foreground">
                            #{{ row.id_tuc }}
                        </span>
                    </template>

                    <!-- SLOT: NOMBRE TUC -->
                    <template #nombre_tuc="{ row }">
                        <span class="font-medium text-slate-900 dark:text-slate-100">
                            {{ row.nombre_tuc }}
                        </span>
                    </template>

                    <!-- SLOT: ESTATUS -->
                    <template #status="{ row, item }">
                        <StatusSwitch
                            :status="esActivo(row || item)"
                            @toggle="toggleEstatus(row || item)"
                        />
                    </template>

                    <!-- SLOT: ACCIONES ESTÁNDAR -->
                    <template #actions="{ row, item }">
                        <CrudActionButtons
                            show-route="academico.tipo-unidad-curricular.show"
                            edit-route="academico.tipo-unidad-curricular.edit"
                            delete-route="academico.tipo-unidad-curricular.destroy"
                            :record-id="(row || item).id_tuc"
                            :record-name="(row || item).nombre_tuc"
                        />
                    </template>
                </DataTable>
            </div>

            <!-- PAGINACIÓN -->
            <DataPagination
                :total="tipos?.total ?? 0"
                :per-page="Number(perPage)"
                :current-page="tipos?.current_page ?? 1"
                :last-page="tipos?.last_page ?? 1"
                :from="tipos?.from ?? 0"
                :to="tipos?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidad-registros="cambiarPorPagina"
                @page-change="onPageChange(Number($event))"
            />
        </div>
    </AppLayout>
</template>
