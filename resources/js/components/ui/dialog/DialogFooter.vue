<script setup lang="ts">
/**
 * Componente DialogFooter.
 *
 * Propósito: Contenedor inferior de acciones para diálogos modales.
 *
 * Actualización (Alineación Estándar de Botones):
 * Motivo: Se configuró la disposición por defecto en fila horizontal hacia la derecha
 * (`flex flex-row items-center justify-end gap-2`) para que los botones Cancelar y
 * Guardar/Actualizar permanezcan siempre juntos del lado derecho, con Cancelar
 * ubicado a la izquierda, evitando la inversión o apilado vertical no deseado.
 */
import type { HTMLAttributes } from "vue"
import { DialogClose } from "reka-ui"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'

const props = withDefaults(defineProps<{
  class?: HTMLAttributes["class"]
  showCloseButton?: boolean
}>(), {
  showCloseButton: false,
})
</script>

<template>
  <div
    data-slot="dialog-footer"
    :class="cn('flex flex-row items-center justify-end gap-2', props.class)"
  >
    <slot />
    <DialogClose v-if="showCloseButton" as-child>
      <Button variant="outline">
        Close
      </Button>
    </DialogClose>
  </div>
</template>
