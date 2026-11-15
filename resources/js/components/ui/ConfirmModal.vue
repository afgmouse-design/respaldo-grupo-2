<!-- components/ui/ConfirmModal.vue -->
<script setup lang="ts">
/**
 * Componente Modal Global de Confirmación y Alerta (ConfirmModal).
 *
 * Propósito: Renderizar una ventana modal institucional (<AlertDialog>) para confirmar
 * operaciones críticas (eliminación, desactivación/activación en cascada)
 * o mostrar avisos del sistema, reemplazando los diálogos nativos del navegador.
 *
 * Historial de Modificaciones:
 * - [Corrección Crítica]: Se reemplazaron `<AlertDialogAction>` y `<AlertDialogCancel>` de reka-ui
 *   por `<Button>` controlados directamente con `@click="handleConfirm"` y `@click="handleCancel"`.
 *   Motivo: `<AlertDialogAction>` en reka-ui emitía `@update:open="false"` antes del `@click`,
 *   disparando `handleCancel()` y haciendo que la promesa resolviera siempre `false`.
 * - [Actualización]: Se incorporó el ícono `<Power>` cuando la acción es de activación o desactivación.
 */
import { AlertTriangle, Trash2, Info, CheckCircle2, Power } from 'lucide-vue-next';
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const {
  isOpen,
  dialogTitle,
  dialogMessage,
  confirmLabel,
  cancelLabel,
  dialogVariant,
  dialogIconType,
  isAlertOnlyMode,
  handleConfirm,
  handleCancel,
} = useConfirmDialog();
</script>

<template>
  <AlertDialog :open="isOpen" @update:open="(val) => { if (!val) handleCancel(); }">
    <AlertDialogContent class="sm:max-w-md">
      <!-- Ícono superior según el tipo de acción y variante -->
      <div
        :class="[
          'mx-auto mb-1 flex h-14 w-14 items-center justify-center rounded-full shadow-xs',
          dialogVariant === 'danger'
            ? 'bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400'
            : dialogVariant === 'warning'
              ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400'
              : dialogVariant === 'success'
                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400'
                : 'bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400',
        ]"
      >
        <Power v-if="dialogIconType === 'power'" class="h-7 w-7" />
        <Trash2 v-else-if="dialogIconType === 'trash' || dialogVariant === 'danger'" class="h-7 w-7" />
        <AlertTriangle v-else-if="dialogVariant === 'warning'" class="h-8 w-8" />
        <CheckCircle2 v-else-if="dialogVariant === 'success'" class="h-8 w-8" />
        <Info v-else class="h-8 w-8" />
      </div>

      <AlertDialogHeader>
        <AlertDialogTitle class="text-xl font-bold text-center text-slate-900 dark:text-slate-100">
          {{ dialogTitle }}
        </AlertDialogTitle>
        <AlertDialogDescription class="text-sm text-muted-foreground pt-2 text-center leading-relaxed">
          {{ dialogMessage }}
        </AlertDialogDescription>
      </AlertDialogHeader>

      <AlertDialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-center gap-2.5 mt-4">
        <Button
          v-if="!isAlertOnlyMode"
          type="button"
          variant="outline"
          @click="handleCancel"
          class="sm:w-32 cursor-pointer"
        >
          {{ cancelLabel }}
        </Button>

        <Button
          type="button"
          @click="handleConfirm"
          :class="[
            'sm:w-32 font-semibold transition-all shadow-sm cursor-pointer',
            dialogVariant === 'danger'
              ? 'bg-red-600 hover:bg-red-700 text-white'
              : dialogVariant === 'warning'
                ? 'bg-amber-600 hover:bg-amber-700 text-white'
                : dialogVariant === 'success'
                  ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                  : 'bg-primary hover:bg-primary/90 text-primary-foreground',
          ]"
        >
          {{ confirmLabel }}
        </Button>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

