// composables/useFlashAlert.ts
/**
 * Composable de Notificaciones Flash (Modal de Notificación).
 *
 * Propósito: Capturar automáticamente mensajes enviados por el backend vía Inertia
 * (flash.success, flash.error, flash.message o errors.error) y exponer el estado
 * reactivo para mostrarlos dentro de un Modal de Notificación en lugar de alertas planas.
 *
 * Actualización (Conversión de Notificaciones a Modal Global):
 * Se eliminó la restricción que exigía `newFlash?.id` para mostrar la notificación,
 * dado que los controladores emiten mensajes `with('success', ...)` o `withErrors(['error' => ...])`
 * sin ID adjunto. Además, se centralizó el estado reactivo para permitir su invocación
 * consistente tanto desde las vistas como desde el layout principal.
 *
 * Actualización (Transición a Notificaciones Flotantes Toast con Sonner):
 * Motivo: Reemplazo de modales intrusivos en el centro de la pantalla por notificaciones
 * Toast no bloqueantes en la esquina de la pantalla. El código anterior obligaba al usuario
 * a cerrar manualmente un modal AlertDialog en cada operación CRUD (crear, editar, eliminar),
 * interrumpiendo el flujo de trabajo en la SPA. Se integró `vue-sonner` manteniendo
 * compatibilidad hacia atrás con los valores reactivos existentes.
 */
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export type FlashAlertType = 'success' | 'error' | 'info';

const showSuccessAlert = ref(false);
const itemId = ref<number | null>(null);
const message = ref<string | null>(null);
const alertType = ref<FlashAlertType>('success');
const alertTitle = ref<string>('Notificación del Sistema');
let alertTimeout: ReturnType<typeof setTimeout> | null = null;
let watcherInitialized = false;

export function useFlashAlert(timeoutDuration = 0) {
  const page = usePage();
  const flash = computed(() => (page.props as any)?.flash);
  const errors = computed(() => (page.props as any)?.errors);

  const showAlert = (
    idOrMsg: number | string | null,
    msg?: string,
    type: FlashAlertType = 'success',
    title?: string
  ) => {
    if (typeof idOrMsg === 'string' && msg === undefined) {
      itemId.value = null;
      message.value = idOrMsg;
    } else {
      itemId.value = typeof idOrMsg === 'number' ? idOrMsg : null;
      message.value = msg ?? null;
    }

    alertType.value = type;
    alertTitle.value =
      title ??
      (type === 'error'
        ? 'Error en la Operación'
        : type === 'info'
          ? 'Aviso del Sistema'
          : '¡Operación Exitosa!');

    // Disparar notificación Toast flotante no invasiva
    const headerTitle = alertTitle.value;
    const bodyDescription = message.value || undefined;

    if (type === 'error') {
      toast.error(headerTitle, {
        description: bodyDescription,
      });
    } else if (type === 'info') {
      toast.info(headerTitle, {
        description: bodyDescription,
      });
    } else {
      toast.success(headerTitle, {
        description: bodyDescription,
      });
    }

    // Activar el Modal de Notificación institucional (<SuccessAlert />)
    showSuccessAlert.value = true;

    if (alertTimeout) {
      clearTimeout(alertTimeout);
      alertTimeout = null;
    }

    if (timeoutDuration > 0) {
      alertTimeout = setTimeout(() => {
        closeAlert();
      }, timeoutDuration);
    }
  };

  const closeAlert = () => {
    showSuccessAlert.value = false;
    if (alertTimeout) {
      clearTimeout(alertTimeout);
      alertTimeout = null;
    }
    itemId.value = null;
    message.value = null;

    // Limpiar el estado flash actual en Inertia para permitir disparos consecutivos idénticos
    // y evitar que vuelva a abrirse al montar otros componentes en la misma sesión de página
    if (flash.value) {
      flash.value.success = null;
      flash.value.error = null;
      flash.value.warning = null;
      flash.value.message = null;
      flash.value.id = null;
    }
    if (errors.value && errors.value.error) {
      delete errors.value.error;
    }
  };

  if (!watcherInitialized) {
    watcherInitialized = true;

    watch(
      () => [flash.value, errors.value],
      ([newFlash, newErrors]) => {
        if (newFlash?.success) {
          showAlert(newFlash.id ?? null, newFlash.success, 'success');
        } else if (newFlash?.error) {
          showAlert(newFlash.id ?? null, newFlash.error, 'error');
        } else if (newFlash?.warning) {
          showAlert(newFlash.id ?? null, newFlash.warning, 'info', 'Aviso del Sistema');
        } else if (newFlash?.message) {
          showAlert(newFlash.id ?? null, newFlash.message, 'info');
        } else if (newErrors?.error) {
          showAlert(null, newErrors.error, 'error');
        }
      },
      { deep: true, immediate: true }
    );
  } else {
    // Evaluar inmediatamente al montar una nueva vista si existe un mensaje flash activo
    if (flash.value?.success) {
      showAlert(flash.value.id ?? null, flash.value.success, 'success');
    } else if (flash.value?.error) {
      showAlert(flash.value.id ?? null, flash.value.error, 'error');
    } else if (flash.value?.warning) {
      showAlert(flash.value.id ?? null, flash.value.warning, 'info', 'Aviso del Sistema');
    } else if (flash.value?.message) {
      showAlert(flash.value.id ?? null, flash.value.message, 'info');
    } else if (errors.value?.error) {
      showAlert(null, errors.value.error, 'error');
    }
  }

  return {
    showSuccessAlert,
    itemId,
    message,
    alertType,
    alertTitle,
    showAlert,
    closeAlert,
  };
}