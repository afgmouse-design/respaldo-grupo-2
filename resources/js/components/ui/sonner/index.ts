/**
 * Módulo de Notificaciones Toast (Sonner).
 *
 * Propósito: Exportar el componente Toaster y la utilidad reactiva toast de vue-sonner
 * para su uso global en la aplicación.
 *
 * Actualización (Integración Global de Toasts):
 * Se exporta explícitamente `toast` para permitir disparar notificaciones flotantes
 * programáticamente desde composables y vistas sin depender de modales intrusivos.
 */
export { default as Toaster } from "./Sonner.vue";
export { toast } from "vue-sonner";

