import { ref } from 'vue';
import type { DateValue } from '@internationalized/date';
import { getLocalTimeZone } from '@internationalized/date';

export function useDateRange(initialStart?: DateValue, initialEnd?: DateValue) {
  const startDate = ref<DateValue | undefined>(initialStart);
  const endDate = ref<DateValue | undefined>(initialEnd);
  const tempStartDate = ref<DateValue | undefined>(initialStart);
  const tempEndDate = ref<DateValue | undefined>(initialEnd);
  const openPopover = ref(false);

  // Formateador largo (el que ya tenías)
  const dfLong = new Intl.DateTimeFormat('es-ES', { dateStyle: 'long' });
  
  // Nuevo formateador compacto: dd/mm/aaaa
  const dfCompact = new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });

  const formatDate = (date?: DateValue): string => {
    if (!date) return '';
    return dfLong.format(date.toDate(getLocalTimeZone()));
  };

  /**
   * Nueva función: Retorna formato dd/mm/aaaa
   */
  const formatDateCompact = (date?: DateValue): string => {
    if (!date) return '';
    return dfCompact.format(date.toDate(getLocalTimeZone()));
  };

  const applyDateFilter = (onApply: (start?: DateValue, end?: DateValue) => void) => {
    startDate.value = tempStartDate.value;
    endDate.value = tempEndDate.value;
    onApply(startDate.value, endDate.value);
    openPopover.value = false;
  };

  const clearTempDates = () => {
    tempStartDate.value = undefined;
    tempEndDate.value = undefined;
  };

  const resetDates = () => {
    startDate.value = undefined;
    endDate.value = undefined;
    tempStartDate.value = undefined;
    tempEndDate.value = undefined;
  };

  return {
    startDate,
    endDate,
    tempStartDate,
    tempEndDate,
    openPopover,
    formatDate,
    formatDateCompact, // <-- Exportamos la nueva función
    applyDateFilter,
    clearTempDates,
    resetDates,
  };
}