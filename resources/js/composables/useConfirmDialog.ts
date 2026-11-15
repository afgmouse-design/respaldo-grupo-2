/**
 * Composable de Confirmación Modal (useConfirmDialog).
 *
 * Propósito: Reemplazar los cuadros de diálogo nativos del navegador (`window.confirm` y `window.alert`)
 * por una ventana modal reactiva e institucional (<AlertDialog>) dentro del sistema SOGAC.
 *
 * Historial de Modificaciones:
 * - [Actualización]: Diferenciación semántica automática entre "Confirmar Desactivación",
 *   "Confirmar Activación" y "Confirmar Eliminación", además de soporte para segundo argumento `customTitle`.
 */
import { ref } from 'vue';

export interface ConfirmDialogOptions {
  title?: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'danger' | 'warning' | 'info' | 'success';
  iconType?: 'trash' | 'power' | 'warning' | 'info' | 'success';
  isAlertOnly?: boolean;
}

const isOpen = ref(false);
const dialogTitle = ref('Confirmar Acción');
const dialogMessage = ref('');
const confirmLabel = ref('Aceptar');
const cancelLabel = ref('Cancelar');
const dialogVariant = ref<'danger' | 'warning' | 'info' | 'success'>('warning');
const dialogIconType = ref<'trash' | 'power' | 'warning' | 'info' | 'success'>('warning');
const isAlertOnlyMode = ref(false);

let resolvePromise: ((value: boolean) => void) | null = null;

export function useConfirmDialog() {
  /**
   * Abre un modal de confirmación y retorna una promesa booleana (true = Aceptar, false = Cancelar).
   */
  const confirmDialog = (
    optionsOrMessage: string | ConfirmDialogOptions,
    customTitle?: string
  ): Promise<boolean> => {
    const opts: ConfirmDialogOptions =
      typeof optionsOrMessage === 'string'
        ? { message: optionsOrMessage, title: customTitle }
        : optionsOrMessage;

    const rawMsg = opts.message.trim();
    const isDelete = /eliminar/i.test(rawMsg);
    const isDeactivate = !isDelete && /desactivar|inactivar/i.test(rawMsg);
    const isActivate = !isDelete && !isDeactivate && /\bactivar\b/i.test(rawMsg);
    const isWarning = /advertencia/i.test(rawMsg);

    // Limpiar prefijo repetitivo "⚠️ ADVERTENCIA:" si está presente para mostrarlo limpio en el título/cuerpo
    const cleanMessage = rawMsg.replace(/^(⚠️\s*)?ADVERTENCIA:\s*/i, '');

    if (isDelete) {
      dialogTitle.value = opts.title ?? 'Confirmar Eliminación';
      dialogVariant.value = opts.variant ?? 'danger';
      dialogIconType.value = opts.iconType ?? 'trash';
    } else if (isDeactivate) {
      dialogTitle.value = 'Confirmar Desactivación';
      dialogVariant.value = opts.variant ?? 'danger';
      dialogIconType.value = opts.iconType ?? 'power';
    } else if (isActivate) {
      dialogTitle.value = 'Confirmar Activación';
      dialogVariant.value = opts.variant ?? 'success';
      dialogIconType.value = opts.iconType ?? 'power';
    } else if (isWarning) {
      dialogTitle.value = opts.title ?? 'Advertencia de Seguridad';
      dialogVariant.value = opts.variant ?? 'warning';
      dialogIconType.value = opts.iconType ?? 'warning';
    } else {
      dialogTitle.value = opts.title ?? 'Confirmar Acción';
      dialogVariant.value = opts.variant ?? 'warning';
      dialogIconType.value = opts.iconType ?? 'warning';
    }

    dialogMessage.value = cleanMessage;
    confirmLabel.value = opts.confirmText ?? 'Aceptar';
    cancelLabel.value = opts.cancelText ?? 'Cancelar';
    isAlertOnlyMode.value = Boolean(opts.isAlertOnly);
    isOpen.value = true;

    return new Promise<boolean>((resolve) => {
      resolvePromise = resolve;
    });
  };

  /**
   * Abre un modal informativo (equivalente a window.alert) con un único botón "Aceptar".
   */
  const alertDialog = (
    message: string,
    title = 'Aviso del Sistema',
    variant: 'danger' | 'warning' | 'info' | 'success' = 'info'
  ): Promise<boolean> => {
    return confirmDialog({
      title,
      message,
      confirmText: 'Aceptar',
      variant,
      iconType: variant === 'danger' ? 'warning' : variant,
      isAlertOnly: true,
    });
  };

  const handleConfirm = () => {
    const resolver = resolvePromise;
    resolvePromise = null;
    isOpen.value = false;
    if (resolver) {
      resolver(true);
    }
  };

  const handleCancel = () => {
    const resolver = resolvePromise;
    resolvePromise = null;
    isOpen.value = false;
    if (resolver) {
      resolver(false);
    }
  };

  return {
    isOpen,
    dialogTitle,
    dialogMessage,
    confirmLabel,
    cancelLabel,
    dialogVariant,
    dialogIconType,
    isAlertOnlyMode,
    confirmDialog,
    alertDialog,
    handleConfirm,
    handleCancel,
  };
}