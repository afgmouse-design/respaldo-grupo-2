<script setup lang="ts">
/**
 * Layout Principal de la Aplicación (AppLayout).
 *
 * Propósito: Contenedor global de navegación lateral (Sidebar), migas de pan y temas.
 *
 * Actualización (Modales Globales de Confirmación y Notificaciones):
 * Se integraron los componentes `<ConfirmModal />` y `<SuccessAlert is-global />`
 * para garantizar que todas las notificaciones flash y confirmaciones de usuario
 * se presenten como modales institucionales en cualquier vista del sistema.
 *
 * Actualización (Adopción de Notificaciones Toast Flotantes):
 * Motivo: Reemplazo del modal estático `<SuccessAlert />` por el sistema de Toasts Sonner
 * ubicado en la esquina superior derecha (`top-right`), con duración adecuada (3500ms)
 * y botón de cierre (`close-button`), ofreciendo una experiencia SPA ágil y no bloqueante.
 */
import { ref, onMounted } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import type { BreadcrumbItem } from '@/types';
import 'vue-sonner/style.css';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const currentTheme = ref('light');

onMounted(() => {
  // Verificamos si el HTML tiene la clase dark
  if (document.documentElement.classList.contains('dark')) {
    currentTheme.value = 'dark';
  }

  // Escuchar cambios en el DOM si el usuario cambia el tema
  const observer = new MutationObserver(() => {
    currentTheme.value = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
  });
  
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>

    <!-- Modal Global de Confirmaciones (Reemplazo de window.confirm / window.alert) -->
    <ConfirmModal />

    <!-- Notificaciones Flotantes Toast en la esquina superior derecha -->
    <Toaster
        position="top-right"
        richColors
        :theme="currentTheme"
        :duration="3500"
        close-button
        expand
    />
</template>

