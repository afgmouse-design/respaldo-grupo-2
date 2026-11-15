<script setup lang="ts">
/**
 * Layout: AppSidebarLayout
 * Propósito: Estructura base para vistas con navegación lateral (Sidebar).
 * 
 * Actualización (Eliminación de Doble Scroll):
 * Motivo: Se restableció `overflow-x-hidden` en `AppContent` para evitar que el contenedor global
 * genere una segunda barra de desplazamiento horizontal en el viewport. Las tablas de datos
 * (`DataTable`) gestionan su propio scroll interno de columnas de manera limpia y autónoma.
 *
 * Actualización (Soporte de Impresión Limpia y Exportación PDF):
 * Motivo: Se añadieron las clases `print:hidden` a la barra lateral y cabecera institucional
 * para evitar que los íconos de navegación o menús se filtren al exportar o imprimir documentos.
 */
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar class="print:hidden" />
        <AppContent variant="sidebar" class="min-w-0 flex-1 overflow-x-hidden print:overflow-visible print:p-0 print:m-0 print:w-full">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" class="print:hidden" />
            <slot />
        </AppContent>
    </AppShell>
</template>
