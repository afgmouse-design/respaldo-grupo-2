<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

defineProps<{
  modelValue: any;
  label: string;
  options: { value: string | number; label: string }[];
}>();

defineEmits(['update:modelValue']);
</script>

<template>
  <Select 
    :model-value="String(modelValue || '')" 
    @update:model-value="val => $emit('update:modelValue', val)"
  >
    <SelectTrigger class="w-full min-w-[170px] h-9">
      <SelectValue :placeholder="label" />
    </SelectTrigger>
    <SelectContent>
      <SelectItem v-for="opt in options" :key="opt.value" :value="String(opt.value)">
        {{ opt.label }}
      </SelectItem>
    </SelectContent>
  </Select>
</template>