<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Filter, Plus, Search as SearchIcon, X, Calendar as CalendarIcon } from 'lucide-vue-next';
import { PopoverClose } from 'reka-ui';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupInput, InputGroupAddon } from '@/components/ui/input-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { useDateRange } from '@/composables/useDateRange';
import type { FilterConfig } from '@/types/dataTable';
import { route } from 'ziggy-js';

const props = defineProps<{
  filters: FilterConfig[];
  filterValues: Record<string, any>;
  createRoute?: string;
  createLabel?: string;
  hasActiveFilters: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:filter', key: string, value: any): void;
  (e: 'clearFilters'): void;
}>();

const searchFilter = computed(() => props.filters.find(f => f.type === 'search'));
const selectFilters = computed(() => props.filters.filter(f => f.type === 'select'));
const dateFilters = computed(() => props.filters.filter(f => f.type === 'dateRange'));

// Manejo de fechas
const dateRanges = ref<Record<string, any>>({});
props.filters.forEach(filter => {
  if (filter.type === 'dateRange') {
    dateRanges.value[filter.key] = useDateRange(
      props.filterValues[`${filter.key}_start`],
      props.filterValues[`${filter.key}_end`]
    );
  }
});

const handleApplyDate = (key: string) => {
  const range = dateRanges.value[key];
  range.applyDateFilter((start: any, end: any) => {
    // Formateamos a string antes de emitir para evitar objetos Date en el Proxy
    emit('update:filter', `${key}_start`, start ? new Date(start).toISOString().split('T')[0] : '');
    emit('update:filter', `${key}_end`, end ? new Date(end).toISOString().split('T')[0] : '');
  });
};
</script>

<template>
  <div class="flex flex-col md:flex-row items-center gap-3 mb-6">
    
    <div v-if="searchFilter" class="w-full md:flex-1">
      <InputGroup>
        <InputGroupInput 
          :model-value="filterValues[searchFilter.key]"
          @update:model-value="val => emit('update:filter', searchFilter.key, val || '')"
          :placeholder="searchFilter.placeholder || 'Buscar...'" 
        />
        <InputGroupAddon><SearchIcon class="h-4 w-4 opacity-40" /></InputGroupAddon>
      </InputGroup>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2 w-full md:w-auto">
      
      <div v-for="f in selectFilters" :key="f.key">
        <Select 
          :model-value="String(filterValues[f.key] || '')" 
          @update:model-value="val => emit('update:filter', f.key, val)"
        >
          <SelectTrigger class="w-full sm:w-[170px] h-9">
            <SelectValue :placeholder="f.label || 'Seleccionar'" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="opt in f.options" :key="opt.value" :value="String(opt.value)">
              {{ opt.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div v-for="f in dateFilters" :key="f.key">
        <Popover v-model:open="dateRanges[f.key].openPopover">
          <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="h-9 whitespace-nowrap">
              <Filter class="h-4 w-4 mr-2" /> {{ f.label || 'Fechas' }}
            </Button>
          </PopoverTrigger>
          <PopoverContent class="w-80 p-4" align="end">
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-2">
                <Popover>
                  <PopoverTrigger as-child>
                    <Button variant="outline" size="sm" class="text-xs justify-start">
                      <CalendarIcon class="mr-2 h-3 w-3" />
                      {{ dateRanges[f.key].tempStartDate ? dateRanges[f.key].formatDate(dateRanges[f.key].tempStartDate) : 'Desde' }}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-auto p-0">
                    <Calendar v-model="dateRanges[f.key].tempStartDate" />
                  </PopoverContent>
                </Popover>

                <Popover>
                  <PopoverTrigger as-child>
                    <Button variant="outline" size="sm" class="text-xs justify-start">
                      <CalendarIcon class="mr-2 h-3 w-3" />
                      {{ dateRanges[f.key].tempEndDate ? dateRanges[f.key].formatDate(dateRanges[f.key].tempEndDate) : 'Hasta' }}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-auto p-0">
                    <Calendar v-model="dateRanges[f.key].tempEndDate" />
                  </PopoverContent>
                </Popover>
              </div>

              <div class="flex justify-between items-center pt-2 border-t">
                <Button variant="ghost" size="sm" @click="dateRanges[f.key].clearTempDates()">Limpiar</Button>
                <PopoverClose as-child>
                  <Button size="sm" @click="handleApplyDate(f.key)">Aplicar</Button>
                </PopoverClose>
              </div>
            </div>
          </PopoverContent>
        </Popover>
      </div>

      <Button v-if="hasActiveFilters" variant="ghost" size="icon" class="text-red-500" @click="emit('clearFilters')">
        <X class="h-4 w-4" />
      </Button>

      <Button v-if="createRoute" size="sm" as-child>
        <Link :href="route(createRoute)">
          <Plus class="h-4 w-4 mr-1" /> {{ createLabel || 'Nuevo' }}
        </Link>
      </Button>
    </div>
  </div>
</template>