<script setup lang="ts">
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
  modelValue: any;
  label?: string;
  options: { value: string | number; label: string }[];
  defaultValue?: any; // Pasamos a opcional con un estándar por detrás
}>();

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <div class="flex items-center gap-2">
    <span v-if="label" class="text-xs font-medium text-muted-foreground hidden md:inline-block whitespace-nowrap">
      {{ label }}:
    </span>

    <Tabs 
      :model-value="String(modelValue ?? props.defaultValue ?? '1')" 
      @update:model-value="val => emit('update:modelValue', val)"
      class="w-full sm:w-auto"
    >
      <TabsList class="w-full h-7 sm:w-auto justify-start p-1">
        <TabsTrigger 
          v-for="opt in options" 
          :key="opt.value" 
          :value="String(opt.value)"
          class="text-xs font-medium px-3 py-3 flex-1 sm:flex-initial data-[state=active]:!bg-background"
        >
          {{ opt.label }}
        </TabsTrigger>
      </TabsList>
    </Tabs>
  </div>
</template>