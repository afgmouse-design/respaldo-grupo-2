<!-- components/ui/ViewToggle.vue -->
<script setup lang="ts">
const props = defineProps<{
  modelValue: 'table' | 'grid';
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: 'table' | 'grid'): void;
}>();
</script>

<template>
  <div class="flex justify-center rounded-lg border border-border bg-background p-1 w-22">
    <button
      @click="emit('update:modelValue', 'table')"
      class="px-3 py-1.5 text-sm rounded-md transition-all"
      :class="modelValue === 'table' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-accent'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <button
      @click="emit('update:modelValue', 'grid')"
      class="px-3 py-1.5 text-sm rounded-md transition-all"
      :class="modelValue === 'grid' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-accent'"
    >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
      </svg>

    </button>
  </div>
</template>