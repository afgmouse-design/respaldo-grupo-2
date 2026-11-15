<script setup lang="ts">
import { InputGroup, InputGroupInput, InputGroupAddon } from '@/components/ui/input-group';
import { Search as SearchIcon } from 'lucide-vue-next';

defineProps<{
  modelValue: string;
  placeholder?: string;
}>();

defineEmits(['update:modelValue']);
</script>

<template>
  <div class="w-full flex-1">
    <InputGroup>
      <InputGroupInput 
        :model-value="modelValue"
        @update:model-value="(val: string) => $emit('update:modelValue', val || '')"
        :placeholder="placeholder || 'Buscar...'" 
      />
      <InputGroupAddon><SearchIcon class="h-4 w-4 opacity-40" /></InputGroupAddon>
    </InputGroup>
  </div>
</template>