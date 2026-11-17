<script setup lang="ts">
/**
 * Vista Standalone de Listado: Gestión de Tipos de Sección (Programación Académica).
 * Propósito: lista, filtra y pagina los tipos de sección desde PostgreSQL (`tipo_seccion`).
 * Actualización: Presenta los tipos de sección en formato de Cuadros (tarjetas) con las opciones
 * de acción (Ver, Editar y Eliminar en rojo) directamente afuera y visibles, junto al StatusSwitch (Activo/Inactivo).
 * Historial:
 *  - 2026-11-15: Se elimina referencia a helpers inexistentes (`cupoSugerido`, `esPer` no definido en script) para evitar TypeError en vista Cuadros tras alinear `tipo_seccion` con `esquema_bd2.sql`.
 *  - 2026-11-16: Estandarización global con <CrudActionButtons /> (cuadros y tabla) en reemplazo de botones custom y PencilLine.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CheckCircle2,
    Info,
    LayoutGrid,
    List,
    Plus,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
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

const props = defineProps<{
    tiposSeccion: any;
    filters: any;
}>();

const { showSuccessAlert, itemId, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

// Modo de visualización: por defecto 'cuadros' (tarjetas como antes)
const viewMode = ref<'cuadros' | 'tabla'>('cuadros');

const columns: Column[] = [
    { key: 'id', label: 'Nro.', align: 'center', width: 'w-16' },
    { key: 'codigo_tis', label: 'Código', align: 'center', width: 'w-32' },
    { key: 'nombre_tis', label: 'Nombre del Tipo de Sección', align: 'left' },
    { key: 'descripcion_tis', label: 'Descripción', align: 'left' },
    { key: 'status', label: 'Estatus', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-36' },
];

const {
    data: tiposData,
    isLoading,
    filterValues,
    perPage,
    clearFilters,
    hasActiveFilters,
    changePerPage,
    onPageChange,
    setFilter,
    fetchData,
} = useDataFetching(
    () => props.tiposSeccion ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'tipo_seccion.index', listRouteName: 'tiposSeccion', perPageDefault: 10 }
);

const esPer = (row: any): boolean =>
    /\bPER\b/i.test(`${row?.codigo_tis ?? ''} ${row?.nombre_tis ?? ''}`);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de sección "${row.nombre_tis}"?`, `Confirmar ${accion}`)) {
        router.delete(route('tipo_seccion.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};
</script>

<template>
    <Head title="Tipos de Sección" />

    <AppLayout :breadcrumbs="[{ title: 'Tipos de Sección', subtitle: 'Catálogo de Naturaleza de Secciones', href: route('tipo_seccion.index') }]">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <SuccessAlert :show="showSuccessAlert" :id="itemId" :message="message" entity="tipo_seccion" @close="closeAlert" />

            <!-- ENCABEZADO ESTÁNDAR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <LayoutGrid class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Tipos de Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Catálogo de modalidades de sección académica (Regular, PER, Repitiente, Dirigido, Intensivo).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child size="default" class="gap-2 shadow-sm">
                        <Link :href="route('tipo_seccion.create')">
                            <Plus class="h-4 w-4" />
                            Nuevo Tipo de Sección
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- BARRA SUPERIOR DE BÚSQUEDA Y FILTROS -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2">
                <div class="flex items-center gap-3 w-full">
                    <SearchFilter
                        class="w-full flex-1"
                        :model-value="filterValues.search ?? ''"
                        placeholder="Buscar por nombre, código o descripción..."
                        @update:model-value="(val: string) => setFilter('search', val)"
                    />
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center justify-end gap-2 w-full lg:w-auto">
                    <TabFilter
                        :model-value="filterValues.status ?? ''"
                        :options="[
                            { value: '', label: 'Activos' },
                            { value: '0', label: 'Inactivos' },
                            { value: 'all', label: 'Todos' },
                        ]"
                        default-value=""
                        @update:model-value="(val: string) => setFilter('status', val)"
                    />

                    <!-- Selector Cuadros / Tabla -->
                    <div class="inline-flex items-center rounded-lg border bg-muted/40 p-0.5 h-9 shrink-0">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all cursor-pointer"
                            :class="viewMode === 'cuadros' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                            @click="viewMode = 'cuadros'"
                            title="Ver como cuadros"
                        >
                            <LayoutGrid class="h-3.5 w-3.5" />
                            <span>Cuadros</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition-all cursor-pointer"
                            :class="viewMode === 'tabla' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                            @click="viewMode = 'tabla'"
                            title="Ver como tabla"
                        >
                            <List class="h-3.5 w-3.5" />
                            <span>Tabla</span>
                        </button>
                    </div>

                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="icon"
                        class="text-red-500 h-9 w-9 shrink-0"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" />
                    </Button>

                </div>
            </div>

            <!-- VISTA EN CUADROS (CON OPCIONES VER, EDITAR Y ELIMINAR AFUERA) -->
            <div v-if="viewMode === 'cuadros'">
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="3" />

                <div
                    v-else-if="(tiposData?.data || []).length === 0"
                    class="bg-card border rounded-xl p-10 text-center text-muted-foreground text-sm"
                >
                    No se encontraron tipos de sección con los filtros seleccionados.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="row in tiposData?.data || []"
                        :key="row.id"
                        class="bg-card border rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-primary/50 transition-all"
                    >
                        <div>
                            <!-- CABECERA DEL CUADRO: CÓDIGO Y SWITCH ACTIVO -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2">
                                    <Badge variant="secondary" class="font-mono text-xs font-bold">
                                        {{ row.codigo_tis }}
                                    </Badge>
                                </div>
                                <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                            </div>

                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">
                                {{ row.nombre_tis }}
                            </h2>

                            <p class="text-sm text-muted-foreground mb-3 leading-relaxed">
                                {{ row.descripcion_tis || 'Sin descripción registrada.' }}
                            </p>

                            <div class="mb-3">
                                <span v-if="esPer(row)" class="inline-flex items-center text-xs text-amber-600 font-medium">
                                    <AlertCircle class="h-3.5 w-3.5 mr-1 shrink-0" />
                                    Acompañamiento PER
                                </span>
                                <span v-else class="inline-flex items-center text-xs text-emerald-600 font-medium">
                                    <CheckCircle2 class="h-3.5 w-3.5 mr-1 shrink-0" />
                                    Secuencia Regular
                                </span>
                            </div>
                        </div>

                        <!-- PIE DEL CUADRO: ACCIONES ESTÁNDAR -->
                        <div class="pt-3 border-t flex items-center justify-end">
                            <CrudActionButtons
                                show-route="tipo_seccion.show"
                                edit-route="tipo_seccion.edit"
                                delete-route="tipo_seccion.destroy"
                                :record-id="row.id_tis ?? row.id"
                                :record-name="row.nombre_tis"
                                :delete-payload="{ eliminar: 1 }"
                                @deleted="fetchData()"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA EN TABLA (TAMBIÉN CON VER, EDITAR Y ELIMINAR AFUERA) -->
            <div v-else>
                <TableSkeleton v-if="isLoading" :columns="columns" :skeletonRows="5" />
                <DataTable
                    v-else
                    :columns="columns"
                    :data="tiposData?.data || []"
                    :is-loading="isLoading"
                    :resultadosTotales="tiposData.total"
                    :filtrosActivos="hasActiveFilters"
                >
                    <template #codigo_tis="{ value }">
                        <Badge variant="secondary" class="font-mono text-xs font-bold">
                            {{ value }}
                        </Badge>
                    </template>
                    <template #nombre_tis="{ value }">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md border bg-muted/40 font-semibold text-slate-900 dark:text-slate-100">
                            {{ value }}
                        </span>
                    </template>
                    <template #status="{ row }">
                        <StatusSwitch :status="esActivo(row) ? 1 : 0" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <CrudActionButtons
                            show-route="tipo_seccion.show"
                            edit-route="tipo_seccion.edit"
                            delete-route="tipo_seccion.destroy"
                            :record-id="row.id_tis ?? row.id"
                            :record-name="row.nombre_tis"
                            :delete-payload="{ eliminar: 1 }"
                            @deleted="fetchData()"
                        />
                    </template>
                </DataTable>
            </div>

            <DataPagination
                :total="tiposData?.total ?? 0"
                :per-page="tiposData?.per_page ?? 10"
                :current-page="tiposData?.current_page ?? 1"
                :last-page="tiposData?.last_page ?? 1"
                :from="tiposData?.from ?? 0"
                :to="tiposData?.to ?? 0"
                :registros_per_page="Number(perPage)"
                @cantidadRegistros="changePerPage(String($event))"
                @page-change="onPageChange(Number($event))"
            />

            <!-- NOTA NORMATIVA -->
            <div class="p-4 rounded-xl bg-muted/40 border border-muted flex items-start gap-3 text-xs text-muted-foreground">
                <Info class="h-5 w-5 text-primary shrink-0 mt-0.5" />
                <div>
                    <strong class="font-semibold text-foreground">Regulación Curricular Institucional:</strong>
                    Los tipos de sección son aplicables a cualquier sede y programa nacional de formación (PNF). Al aperturar secciones en el lapso académico activo, la selección del tipo determina el algoritmo de priorización en la inscripción del estudiante y la conformación de actas finales de notas.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
