<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { Column } from '@/types/dataTable';
import Skeleton from './skeleton/Skeleton.vue';

defineProps<{
  columns: Column[];
  skeletonRows: number;
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
  
  <div class="mt-2 p-3 rounded-lg overflow-x-auto border">
  <div class="flex items-center justify-between mb-1 px-4">
    <div class="flex items-center gap-3">
    <Skeleton class="h-5 w-40 rounded-full inline-block" />

    </div>
    <Skeleton class="h-5 w-24 rounded-full inline-block" />
  </div>

  <Table>
    <TableHeader>
      <TableRow class="hover:bg-transparent">
        <TableHead 
          v-for="(col, index) in columns" 
          :key="'sk-head-' + index" 
          :class="[alignClass(col.align), col.width]"
          class="text-muted-foreground"
        >
          <Skeleton class="h-4 w-16 inline-block fallback-bg" />
        </TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow 
        v-for="rowIndex in skeletonRows" 
        :key="'sk-row-' + rowIndex"
        class="transition-all duration-200 hover:-translate-y-0.5"
      >
        <TableCell 
          v-for="(col, colIndex) in columns" 
          :key="'sk-cell-' + colIndex" 
          :class="alignClass(col.align)"
          class=""
        >
          <Skeleton class="h-4 w-full max-w-[120px] inline-block" />
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</div>
</template>