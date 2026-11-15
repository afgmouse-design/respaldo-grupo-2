<script setup lang="ts">
/**
 * Componente: SidebarTrigger
 * Propósito: Botón disparador para abrir y colapsar la barra lateral (Sidebar).
 * 
 * Actualización (Flechita de Apertura/Cierre y Estilos Visuales):
 * Se reemplazaron los íconos genéricos por flechas claras (ChevronRight / ChevronLeft)
 * y estilos con borde sutil para que el usuario identifique intuitivamente la flechita
 * de apertura cuando el menú esté colapsado y de repliegue cuando esté desplegado.
 */
import type { HTMLAttributes } from "vue"
import { ChevronLeft, ChevronRight } from "lucide-vue-next"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
import { useSidebar } from "./utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const { isMobile, state, toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="outline"
    size="icon"
    :class="cn('h-8 w-8 rounded-lg border-sidebar-border/80 bg-background/80 shadow-2xs hover:bg-accent hover:text-accent-foreground transition-all cursor-pointer', props.class)"
    :title="isMobile || state === 'collapsed' ? 'Abrir menú lateral' : 'Colapsar menú lateral'"
    @click="toggleSidebar"
  >
    <ChevronRight v-if="isMobile || state === 'collapsed'" class="h-4 w-4 text-primary shrink-0 transition-transform hover:scale-110" />
    <ChevronLeft v-else class="h-4 w-4 text-muted-foreground shrink-0 transition-transform hover:scale-110" />
    <span class="sr-only">Alternar menú</span>
  </Button>
</template>
