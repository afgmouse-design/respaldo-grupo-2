<script setup lang="ts">
import { ref, watch } from 'vue';
import { Calendar as CalendarIcon, Calendar1 } from 'lucide-vue-next';
import { today, getLocalTimeZone } from '@internationalized/date'; // Importamos utilidades de fecha
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { PopoverClose } from 'reka-ui';
import { useDateRange } from '@/composables/useDateRange';

const props = defineProps<{
  label: string;
  startValue: any;
  endValue: any;
}>();

const emit = defineEmits<{
  (e: 'apply', start: string, end: string): void;
}>();

const { 
  tempStartDate, 
  tempEndDate, 
  openPopover, 
  formatDateCompact,
  clearTempDates, 
  applyDateFilter 
} = useDateRange(props.startValue, props.endValue);

// Creamos estados para controlar la apertura de los sub-popovers
const isStartPopoverOpen = ref(false);
const isEndPopoverOpen = ref(false);

// 2. Cerramos el popover automáticamente cuando se selecciona una fecha
watch(tempStartDate, () => {
  isStartPopoverOpen.value = false;
});

watch(tempEndDate, () => {
  isEndPopoverOpen.value = false;
});
// --- LÓGICA DE VALIDACIÓN ---

const ahora = today(getLocalTimeZone());

// 1. Validar Fecha de Inicio: No puede ser futura
const isStartDateDisabled = (date: any) => {
  return date.compare(ahora) > 0;
};

// 2. Validar Fecha de Fin: No puede ser futura ni menor a la de inicio
const isEndDateDisabled = (date: any) => {
  const esFutura = date.compare(ahora) > 0;
  const esMenorAlInicio = tempStartDate.value ? date.compare(tempStartDate.value) < 0 : false;
  return esFutura || esMenorAlInicio;
};

// 3. Watcher de seguridad: Si cambias el inicio y es mayor al fin actual, limpiamos el fin
watch(tempStartDate, (newStart) => {
  if (newStart && tempEndDate.value && newStart.compare(tempEndDate.value) > 0) {
    tempEndDate.value = undefined;
  }
});

const handleApply = () => {
  applyDateFilter((start: any, end: any) => {
    emit('apply', 
      start ? new Date(start).toISOString().split('T')[0] : '', 
      end ? new Date(end).toISOString().split('T')[0] : ''
    );
  });
};
</script>

<template>
  <Popover v-model:open="openPopover">
    <PopoverTrigger as-child>
       <Button variant="outline" size="sm" class="h-9 px-3">
         <Calendar1 class="h-4 w-4" />
         <!-- <span class="text-xs font-normal">
           {{ tempStartDate || tempEndDate ? 'Rango Seleccionado' : label }}
         </span> -->
       </Button>
    </PopoverTrigger>
    
    <PopoverContent class="w-auto p-4" align="end">
      <div class="space-y-4">
        <h4 class="text-sm font-medium leading-none">Seleccionar Rango</h4>
        
        <div class="flex flex-col sm:flex-row gap-2">
          
          <Popover v-model:open="isStartPopoverOpen"> <PopoverTrigger as-child>
              <Button variant="outline" size="sm" class="w-[135px] text-xs justify-start font-normal">
                <CalendarIcon class="mr-2 h-3 w-3" />
                {{ tempStartDate ? formatDateCompact(tempStartDate) : 'Desde' }}
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="start">
              <Calendar 
                v-model="tempStartDate" 
                :is-date-disabled="isStartDateDisabled"
                initial-focus 
              />
            </PopoverContent>
          </Popover>

          <Popover v-model:open="isEndPopoverOpen"> <PopoverTrigger as-child>
              <Button 
                variant="outline" 
                size="sm" 
                class="w-[135px] text-xs justify-start font-normal"
                :disabled="!tempStartDate" 
              >
                <CalendarIcon class="mr-2 h-3 w-3" />
                {{ tempEndDate ? formatDateCompact(tempEndDate) : 'Hasta' }}
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="start">
              <Calendar 
                v-model="tempEndDate" 
                :is-date-disabled="isEndDateDisabled"
                initial-focus 
              />
            </PopoverContent>
          </Popover>
        </div>

        <div class="flex justify-between items-center pt-2 border-t">
          <Button variant="ghost" size="sm" class="text-xs" @click="clearTempDates">
            Limpiar
          </Button>
          <PopoverClose as-child>
            <Button size="sm" class="text-xs" @click="handleApply">
              Aplicar Rango
            </Button>
          </PopoverClose>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>