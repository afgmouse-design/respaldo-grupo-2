<script setup lang="ts">
/**
 * Vista Standalone de Listado: Gestión de Tipos de Sección (Programación Académica).
 * Propósito: lista, filtra y pagina los tipos de sección desde PostgreSQL (`tipo_seccion`).
 * Actualización: Presenta los tipos de sección en formato de Cuadros (tarjetas) con las opciones
 * de acción (Ver, Editar y Eliminar en rojo) directamente afuera y visibles, junto al StatusSwitch (Activo/Inactivo).
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Plus,
    X,
    CheckCircle2,
    AlertCircle,
    LayoutGrid,
    List,
    Info,
    Eye,
    PencilLine,
    Trash2,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/ui/DataTable.vue';
import DataPagination from '@/components/ui/DataPagination.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import TableSkeleton from '@/components/ui/TableSkeleton.vue';

import SearchFilter from '@/components/ui/filters/SearchFilter.vue';
import TabFilter from '@/components/ui/filters/TabFilter.vue';

import { useDataFetching } from '@/composables/useDataFetching';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
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
    { key: 'status', label: 'Status', align: 'center', width: 'w-40' },
    { key: 'actions', label: 'Acciones', align: 'center', width: 'w-64' },
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
    props.tiposSeccion ?? { data: [], total: 0, per_page: 10, current_page: 1, last_page: 1, from: 0, to: 0 },
    props.filters ?? {},
    { routeName: 'tipo_seccion.index', listRouteName: 'tiposSeccion', perPageDefault: 10 }
);

const esActivo = (v: any) => v === true || v === 1 || v === '1' || v === 't';

const esPer = (row: any) => {
    const cod = String(row.codigo_tis || '').toUpperCase();
    const nom = String(row.nombre_tis || '').toUpperCase();
    return cod.includes('PER') || nom.includes('PER') || nom.includes('RECUPERACIÓN');
};

const cupoSugerido = (row: any) => (esPer(row) ? 20 : 35);

const alternarEstatus = async (row: any) => {
    const accion = esActivo(row.status) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el tipo de sección "${row.nombre_tis}"?`, `Confirmar ${accion}`)) {
        router.delete(route('tipo_seccion.destroy', row.id), {
            preserveScroll: true,
            onSuccess: () => fetchData(),
        });
    }
};

const eliminarRegistro = async (row: any) => {
    if (await confirmDialog(`¿Está seguro de que desea eliminar el tipo de sección "${row.nombre_tis}"?`, 'Confirmar eliminación')) {
        router.delete(route('tipo_seccion.destroy', row.id), {
            data: { eliminar: 1 },
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

            <!-- BARRA SUPERIOR DE BÚSQUEDA, FILTROS Y BOTÓN NUEVO -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch md:items-center gap-2 pt-2 mb-4">
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

                    <Button as-child size="sm" class="h-9 flex-1 sm:flex-none">
                        <Link :href="route('tipo_seccion.create')">
                            <Plus class="h-4 w-4 mr-1" /> Nuevo
                        </Link>
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
                            <!-- CABECERA DEL CUADRO: CÓDIGO, CUPO SUGERIDO Y SWITCH ACTIVO -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2">
                                    <Badge variant="secondary" class="font-mono text-xs font-bold">
                                        {{ row.codigo_tis }}
                                    </Badge>
                                    <Badge variant="outline" class="text-xs">
                                        Sugerido: {{ cupoSugerido(row) }} cupos
                                    </Badge>
                                </div>
                                <StatusSwitch :status="row.status" @toggle="alternarEstatus(row)" />
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

                        <!-- PIE DEL CUADRO: OPCIONES AFUERA (VER, EDITAR, ELIMINAR EN ROJO) -->
                        <div class="pt-3 border-t flex items-center justify-end gap-2">
                            <Button variant="outline" size="sm" as-child class="h-8 px-2.5 text-xs gap-1.5">
                                <Link :href="route('tipo_seccion.show', row.id)">
                                    <Eye class="h-3.5 w-3.5" />
                                    <span>Ver</span>
                                </Link>
                            </Button>

                            <Button variant="outline" size="sm" as-child class="h-8 px-2.5 text-xs gap-1.5">
                                <Link :href="route('tipo_seccion.edit', row.id)">
                                    <PencilLine class="h-3.5 w-3.5" />
                                    <span>Editar</span>
                                </Link>
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 px-2.5 text-xs gap-1.5 border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
                                @click="eliminarRegistro(row)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                <span>Eliminar</span>
                            </Button>
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
                    <template #status="{ row, value }">
                        <StatusSwitch :status="value ?? row?.status" @toggle="alternarEstatus(row)" />
                    </template>
                    <template #actions="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <Button variant="outline" size="sm" as-child class="h-8 px-2.5 text-xs gap-1">
                                <Link :href="route('tipo_seccion.show', row.id)">
                                    <Eye class="h-3.5 w-3.5" />
                                    <span>Ver</span>
                                </Link>
                            </Button>

                            <Button variant="outline" size="sm" as-child class="h-8 px-2.5 text-xs gap-1">
                                <Link :href="route('tipo_seccion.edit', row.id)">
                                    <PencilLine class="h-3.5 w-3.5" />
                                    <span>Editar</span>
                                </Link>
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                class="h-8 px-2.5 text-xs gap-1 border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
                                @click="eliminarRegistro(row)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                <span>Eliminar</span>
                            </Button>
                        </div>
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
