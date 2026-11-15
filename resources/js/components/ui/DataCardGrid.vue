<!-- components/ui/DataCardGrid.vue -->
<script setup lang="ts">
defineProps<{
  items: any[];
  isLoading?: boolean;
  emptyMessage?: string;
}>();

const emit = defineEmits<{
  (e: 'clearFilters'): void;
}>();
</script>

<template>
  <div class="mt-4">
    <!-- Indicador de carga -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>

    <!-- Grid de tarjetas -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <slot name="card" :item="item" v-for="item in items" :key="item.id" />
    </div>

    <!-- Estado vacío -->
    <div v-if="!isLoading && !items.length" class="text-center py-12">
      <div class="text-muted-foreground">
        <svg class="h-12 w-12 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="text-lg">{{ emptyMessage || 'No hay elementos para mostrar' }}</p>
        <slot name="empty-actions" />
      </div>
    </div>
  </div>
</template>