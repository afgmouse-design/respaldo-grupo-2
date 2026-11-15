<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';

const props = defineProps<{
  total: number;
  perPage: number;
  currentPage: number;
  lastPage: number;
  from: number;
  to: number;
  registros_per_page: number;
}>();

const emit = defineEmits<{
  (e: 'pageChange', page?: number): void;
  (e: 'cantidadRegistros', cantidad?: string | number | null): void;
}>();

const internalPage = ref(props.currentPage);

watch(() => props.currentPage, (newPage) => {
  internalPage.value = newPage;
});

watch(internalPage, (newPage) => {
  if (newPage !== props.currentPage) {
    emit('pageChange', newPage);
  }
});
</script>

<template>
  <div class="flex items-center justify-between md:flex-row flex-col gap-4 mx-5 mt-4">
    <div>
      <span class="text-sm text-muted-foreground">
        Mostrando {{ props.from }} a {{ props.to }} de {{ props.total }} resultados
      </span>
    </div>
    <div class="flex items-center gap-4 sm:flex-row flex-col justify-center">
      <div class="flex items-center gap-2">
        <label class="text-sm text-muted-foreground">Mostrar</label>
        <select
          :value="String(registros_per_page || perPage || '5')"
          @change="$emit('cantidadRegistros', ($event.target as HTMLSelectElement).value)"
          class="h-9 w-20 rounded-md border border-input bg-background px-2.5 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring cursor-pointer dark:bg-slate-900 dark:border-slate-800"
        >
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
      </div>
      <Pagination :total="total" :items-per-page="perPage" v-model:page="internalPage">
        <PaginationContent v-slot="{ items }">
          <PaginationPrevious :disabled="internalPage === 1" />
          <template v-for="(item, index) in items" :key="index">
            <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === internalPage">
              {{ item.value }}
            </PaginationItem>
            <PaginationEllipsis v-else :index="index" />
          </template>
          <PaginationNext :disabled="internalPage === lastPage" />
        </PaginationContent>
      </Pagination>
    </div>
  </div>
</template>