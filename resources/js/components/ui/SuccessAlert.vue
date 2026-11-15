<!-- components/ui/SuccessAlert.vue -->
<script setup lang="ts">
/**
 * Componente Modal de Notificación del Sistema (SuccessAlert).
 *
 * Propósito: Mostrar notificaciones de éxito, error o información del sistema
 * dentro de una ventana modal centrada (AlertDialog) en lugar de banners estáticos.
 *
 * Actualización (Conversión de Notificaciones a Modal):
 * Se transformó la alerta en línea (<Alert>) en un diálogo modal interactivo (<AlertDialog>),
 * haciendo opcionales las propiedades `show`, `id` y `entity` para garantizar compatibilidad
 * con todos los módulos del sistema SOGAC. Incluye control de instancia única para evitar
 * duplicidad cuando se monta de forma global en AppLayout y local en vistas index.
 *
 * Actualización (Compatibilidad con Toasts Flotantes):
 * Motivo: Coordinar con la transición hacia notificaciones Toast no bloqueantes con Sonner.
 * Se evita la duplicación de modales intrusivos en pantalla cuando `useFlashAlert` ya emite
 * los avisos tipo Toast, garantizando total compatibilidad hacia atrás en vistas legacy.
 */
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { CheckCircle2, AlertTriangle, Info, Eye, Pencil } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogAction,
} from '@/components/ui/alert-dialog';
import { useFlashAlert, type FlashAlertType } from '@/composables/useFlashAlert';

// Contador reactivo de nivel de módulo para evitar doble modal si la vista ya declara <SuccessAlert>
import { ref } from 'vue';
const localInstancesCount = ref(0);

const props = withDefaults(
  defineProps<{
    show?: boolean;
    id?: number | null;
    message?: string | null;
    entity?: string;
    type?: FlashAlertType;
    title?: string;
    isGlobal?: boolean;
  }>(),
  {
    show: true,
    id: null,
    message: null,
    entity: '',
    type: undefined,
    title: undefined,
    isGlobal: false,
  }
);

const emit = defineEmits<{ (e: 'close'): void }>();

const { alertType, alertTitle, message: globalMessage, closeAlert } = useFlashAlert();

onMounted(() => {
  if (!props.isGlobal) {
    localInstancesCount.value++;
  }
});

onBeforeUnmount(() => {
  if (!props.isGlobal) {
    localInstancesCount.value = Math.max(0, localInstancesCount.value - 1);
  }
});

const effectiveType = computed<FlashAlertType>(() => props.type ?? alertType.value ?? 'success');

const effectiveTitle = computed(() => {
  if (props.title) return props.title;
  if (alertTitle.value) return alertTitle.value;
  if (effectiveType.value === 'error') return 'Error en la Operación';
  if (effectiveType.value === 'info') return 'Aviso del Sistema';
  return '¡Operación Exitosa!';
});

const effectiveMessage = computed(() => {
  return (
    props.message ||
    globalMessage.value ||
    (props.entity ? `${props.entity} procesado exitosamente.` : 'La operación se realizó correctamente.')
  );
});

const shouldRender = computed(() => {
  if (props.isGlobal && localInstancesCount.value > 0) {
    return false;
  }
  return Boolean(props.show && (props.message || globalMessage.value));
});

const handleClose = () => {
  closeAlert();
  emit('close');
};
</script>

<template>
  <AlertDialog :open="shouldRender" @update:open="(val) => { if (!val) handleClose(); }">
    <AlertDialogContent class="sm:max-w-md">
      <!-- Ícono superior según el tipo de notificación -->
      <div
        :class="[
          'mx-auto mb-1 flex h-14 w-14 items-center justify-center rounded-full shadow-xs',
          effectiveType === 'error'
            ? 'bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400'
            : effectiveType === 'info'
              ? 'bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
              : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400',
        ]"
      >
        <AlertTriangle v-if="effectiveType === 'error'" class="h-8 w-8" />
        <Info v-else-if="effectiveType === 'info'" class="h-8 w-8" />
        <CheckCircle2 v-else class="h-8 w-8" />
      </div>

      <AlertDialogHeader>
        <AlertDialogTitle class="text-xl font-bold text-center text-slate-900 dark:text-slate-100">
          {{ effectiveTitle }}
        </AlertDialogTitle>
        <AlertDialogDescription class="text-sm text-muted-foreground pt-2 text-center leading-relaxed">
          {{ effectiveMessage }}
        </AlertDialogDescription>
      </AlertDialogHeader>

      <AlertDialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-center gap-2 mt-4">
        <template v-if="id && entity">
          <Button variant="outline" size="default" as-child @click="handleClose">
            <Link :href="route(`${entity}.show`, id)">
              <Eye class="h-4 w-4 mr-1.5" />
              Ver detalle
            </Link>
          </Button>
          <Button variant="outline" size="default" as-child @click="handleClose">
            <Link :href="route(`${entity}.edit`, id)">
              <Pencil class="h-4 w-4 mr-1.5" />
              Editar
            </Link>
          </Button>
        </template>

        <AlertDialogAction
          @click="handleClose"
          :class="[
            'sm:w-36 font-semibold transition-all shadow-sm cursor-pointer',
            effectiveType === 'error'
              ? 'bg-red-600 hover:bg-red-700 text-white'
              : 'bg-primary hover:bg-primary/90 text-primary-foreground',
          ]"
        >
          Aceptar
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>