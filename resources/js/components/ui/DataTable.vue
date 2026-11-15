<script setup lang="ts">
/**
 * Componente: DataTable
 * Propósito: Tabla de datos interactiva reutilizable para listados del sistema SOGAC.
 * 
 * Actualización (Soporte Responsive y Prevención de Truncamiento):
 * Se reemplazó `overflow-hidden` por `overflow-x-auto` en el contenedor de la tabla para garantizar
 * que en pantallas de menor resolución o cuando la barra lateral esté expandida, todas las columnas
 * (incluyendo la columna de acciones) se puedan visualizar y acceder sin que queden cortadas.
 *
 * Actualización (Ajuste de Ancho Fluido y Eliminación de Scroll Innecesario):
 * Se retiró `min-w-max` de la tabla para que las columnas se distribuyan proporcionalmente dentro del
 * ancho del contenedor (100%), evitando la aparición forzada de la barra de desplazamiento horizontal.
 */
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { Column } from '@/types/dataTable';
import TableSkeleton from './TableSkeleton.vue';
import { User } from 'lucide-vue-next';
import Badge from './badge/Badge.vue';
import ScrollArea from './scroll-area/ScrollArea.vue';
import ScrollBar from './scroll-area/ScrollBar.vue';

const props = withDefaults(
  defineProps<{
    columns: Column[];
    data: any[];
    isLoading?: boolean;
    emptyTitle?: string;
    emptyMessage?: string;
    skeletonRows?: number; // Mantenemos la prop para transferirla al skeleton
    resultadosTotales?: number;
    filtrosActivos?: boolean;
  }>(),
  {
    skeletonRows: 5,
  }
);

const emit = defineEmits<{
  (e: 'rowClick', row: any): void;
}>();

const alignClass = (align?: string) => {
  switch (align) {
    case 'center': return 'text-center';
    case 'right': return 'text-right';
    default: return 'text-left';
  }
};
</script>

<template>
  <div>
    <TableSkeleton v-if="isLoading" :columns="columns" :skeleton-rows="skeletonRows" />

    <div v-else-if="data.length" class="mt-2">
      <div class="flex items-center justify-between mb-3 px-1">
        <div class="flex items-center gap-3">
          <slot name="header-actions" />
        </div>
        <Badge variant="outline" class="text-slate-500 border-slate-200 dark:border-slate-800 dark:text-slate-400 font-medium text-xs">
          {{ resultadosTotales || data.length }} resultados
        </Badge>
      </div>
     
      <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 overflow-x-auto shadow-sm">
        <Table class="w-full border-collapse">
          <TableHeader>
            <TableRow class="hover:bg-transparent border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/50">
              <TableHead v-for="col in columns" :key="col.key" :class="[alignClass(col.align), col.width]"
                class="text-slate-500 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider py-3.5 px-4 h-auto">
                {{ col.label }}
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="(row, index) in data" :key="row.id_tpd ?? row.id ?? row.id_pai ?? row.id_esd ?? row.id_mun ?? row.id_par ?? row.id_loc ?? row.id_uni ?? row.id_sed ?? index" @click="emit('rowClick', row)"
              class="transition-all duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 cursor-pointer border-b border-slate-100 dark:border-slate-900 last:border-0">
              <TableCell v-for="col in columns" :key="col.key"
                :class="[alignClass(col.align), col.maxWidth ? `${col.maxWidth} truncate` : '']" 
                class="py-3 px-4 text-sm text-slate-700 dark:text-slate-300">
                <slot :name="col.key" :row="row" :item="row" :value="row[col.key]">
                  {{ row[col.key] }}
                </slot>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <div v-else-if="!filtrosActivos" class="rounded-lg border bg-elevation-card p-5 mt-2">
      <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="rounded-full p-4 ring-1 mb-4 ring-border/50 bg-muted">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 256 256"
            class="size-6 text-muted-foreground">
            <path
              d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,176H96a8,8,0,0,1,0-16h64a8,8,0,0,1,0,16Zm0-32H96a8,8,0,0,1,0-16h64a8,8,0,0,1,0,16Zm-8-56V44l44,44Z">
            </path>
          </svg>
        </div>

        <p class="font-medium text-foreground mb-1 tabular-nums">
          {{ emptyTitle || 'No hay registros' }}
        </p>
        <p class="text-sm text-muted-foreground mb-6 max-w-sm">
          {{ emptyMessage || 'Crea el primer registro para comenzar.' }}
        </p>

        <slot name="empty-action" />
      </div>
    </div>
    <div v-else class="rounded-lg border bg-elevation-card p-3 mt-2">
      <div class="flex items-center justify-between mb-1 px-2">
        <div class="flex items-center items-center gap-3">
          <slot name="header-actions" />

        </div>
        <Badge variant="outline" class="text-muted-foreground font-sm">{{ resultadosTotales }} resultados</Badge>
      </div>
      <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="rounded-full p-4 ring-1 mb-4 ring-border/50 bg-muted">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 256 256"
            class="size-6 text-muted-foreground">
            <path
              d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,176H96a8,8,0,0,1,0-16h64a8,8,0,0,1,0,16Zm0-32H96a8,8,0,0,1,0-16h64a8,8,0,0,1,0,16Zm-8-56V44l44,44Z">
            </path>
          </svg>
        </div>

        <p class="font-medium text-foreground mb-1 tabular-nums">
          {{ emptyTitle || '¡Ups! No hay resultados' }}
        </p>
        <p class="text-sm text-muted-foreground mb-6 max-w-sm">
          {{ emptyMessage || 'Intenta ajustar los filtros de búsqueda.' }}
        </p>

        <slot name="empty-action" />
      </div>
    </div>
  </div>
</template>