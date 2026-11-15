<script setup lang="ts">
/**
 * Componente: AppLogo
 * Propósito: Logotipo principal institucional SOGAC - UPTP J.J. Montilla.
 * 
 * Actualización (Soporte de Colapso Icon Mode):
 * Se agregó la clase `group-data-[collapsible=icon]:hidden` al contenedor de texto
 * para que cuando el menú se colapse en modo icono, solo se muestre el isotipo cuadrado centrado.
 */
// import AppLogoIcon from './AppLogoIcon.vue';
import AppLogoSogac from './AppLogoSogac.vue';

</script>

<template>
    <!-- bg-sidebar-primary -->

    <div
        class="flex aspect-square size-8 p-[3.5px] items-center justify-center rounded-xs bg-sidebar-primary text-sidebar-primary-foreground">
        <AppLogoSogac class="size-5 fill-current" color="text-foreground" />
    </div>
    <div class="grid flex-1 text-left group-data-[collapsible=icon]:hidden">
        <span class="truncate .dark:text-white font-medium text-[0.95rem] leading-5">SOGAC</span>
        <p class="text-foreground/70 text-xs font-light text-pretty">UPTP J.J. Montilla</p>
    </div>
</template>
