<script setup lang="ts">
/**
 * Componente: NavMain
 * Propósito: Menú principal del sistema de navegación con soporte responsive para módulos agrupados.
 * 
 * Actualización (Colapso Automático al Seleccionar Opción y Responsive):
 * Se agregó la función `onNavigate` que colapsa el sidebar automáticamente al hacer clic en cualquier
 * opción del menú para que la pantalla de destino disponga de todo el ancho horizontal. Además, se
 * agregaron clases para ocultar etiquetas de texto y centrar íconos cuando el menú está en modo icono (collapsed).
 */
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import type { LucideIcon } from "lucide-vue-next";
import { useMediaQuery } from "@vueuse/core";
import {
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from "@/components/ui/sidebar";
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from "@/components/ui/hover-card";
import {
    Sheet,
    SheetContent,
    SheetTrigger,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";

interface NavGroup {
    title: string;
    icon: LucideIcon;
    items: {
        title: string;
        url: string;
        onClick?: () => void;
    }[];
}

interface NavModule {
    title: string;
    icon: LucideIcon;
    isActive?: boolean;
    urlDirect?: string; // para módulos sin grupos
    groups?: NavGroup[]; // para módulos con grupos
}

defineProps<{
    navMain: NavModule[];
}>();

const isDesktop = useMediaQuery("(min-width: 768px)");
const activeModule = ref<string | null>(null);
const { isMobile, setOpen, setOpenMobile } = useSidebar();

// Cierra modales/popovers y colapsa el sidebar para dar espacio completo a la vista
const onNavigate = () => {
    activeCardTitle.value = '';
    activeModule.value = null;
    if (isMobile.value) {
        setOpenMobile(false);
    } else {
        setOpen(false);
    }
};

// Clase base para todos los botones de módulo (enlace directo, hovercard trigger, sheet trigger)
const moduleButtonClass =
    "w-full justify-start gap-3 px-3 py-2 text-sm font-medium transition-all duration-200 rounded-md select-none cursor-pointer text-sidebar-foreground hover:text-foreground data-[active=true]:bg-accent data-[active=true]:text-foreground group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0";

// Guardamos el ID del elemento que está actualmente abierto
const activeCardTitle = ref('') 

const handleOpenChange = (isOpen: boolean, title: string) => {
  if (isOpen) {
    activeCardTitle.value = title
  } else if (activeCardTitle.value === title) {
    activeCardTitle.value = ''
  }
}
</script>

<template>
    <ScrollArea class="h-full w-full p-1">
        <SidebarMenu class="p-1">
            <SidebarGroupLabel class="group-data-[collapsible=icon]:hidden">Gestión Académica</SidebarGroupLabel>

            <template v-for="module in navMain" :key="module.title">
                <!-- Módulo sin grupos: enlace directo con mismo estilo que los triggers -->
                <SidebarMenuItem v-if="!module.groups">
                    <SidebarMenuButton
                        as-child
                        :is-active="module.isActive"
                        :class="moduleButtonClass"
                    >
                        <Link :href="module.urlDirect || '#'" class="gap-2 my-[2px]" @click="onNavigate">
                            <component :is="module.icon" class="h-4 w-4 shrink-0 text-foreground/70" />
                            <span class="truncate group-data-[collapsible=icon]:hidden">{{ module.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Módulo con grupos: versión responsive -->
                <SidebarMenuItem v-else>
                    <!-- ESCRITORIO: HoverCard -->
                    <HoverCard v-if="isDesktop" :open-delay="200" :open="activeCardTitle === module.title" 
      @update:open="(val) => handleOpenChange(val, module.title)" class="!shadow-xl shadow-black/30">
                        <HoverCardTrigger as-child class="my-[2px]">
                            <SidebarMenuButton
                                :is-active="module.isActive"
                                :class="moduleButtonClass"
                            >
                                <component
                                    :is="module.icon"
                                    class="h-4 w-4 shrink-0 transition-transform duration-200 group-hover:scale-105 text-foreground/70"
                                />
                                <span class="truncate group-data-[collapsible=icon]:hidden">{{ module.title }}</span>
                            </SidebarMenuButton>
                        </HoverCardTrigger>

                        <HoverCardContent
                            class="relative z-50 w-auto p-5 max-h-[85vh]
                                   bg-sidebar   border border-border/80 shadow-xl shadow-black/30 rounded-xl
                                   transition-all duration-300 data-[state=open]:animate-in data-[state=closed]:animate-out
                                   data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95
                                   before:absolute before:top-4 before:-left-[5px]"
                            side="right"
                            align="start"
                            :collision-padding="16"
                        >
                            <!-- ScrollArea interno para igualar el del menú principal -->
                            <ScrollArea class="max-h-[75vh] pr-2">
                                <div class="flex items-start divide-x divide-border divide-black-100 dark:divide-white/5 select-none">
                                    <div
                                        v-for="(group, idx) in module.groups"
                                        :key="idx"
                                        class="flex flex-col min-w-[165px] space-y-3 px-5 first:pl-0 last:pr-0"
                                    >
                                        <div class="flex items-center gap-2 px-2">
                                            <component :is="group.icon" class="h-3.5 w-3.5 text-primary/80" />
                                            <h3 class="text-[11px] font-bold tracking-wider uppercase text-sidebar-foreground">
                                                {{ group.title }}
                                            </h3>
                                        </div>

                                        <div class="flex flex-col gap-0.5">
                                            <Button
                                                v-for="item in group.items"
                                                :key="item.title"
                                                variant="ghost"
                                                size="sm"
                                                class="justify-start text-[13px] h-8 px-2.5 font-normal rounded-md text-foreground/85 hover:text-foreground  transition-all duration-150 cursor-pointer w-full"
                                                as-child
                                            >
                                                <Link v-if="item.url !== '#'" :href="item.url" @click="onNavigate">
                                                    {{ item.title }}
                                                </Link>
                                                <span v-else @click="() => { onNavigate(); item.onClick?.(); }">
                                                    {{ item.title }}
                                                </span>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                        </HoverCardContent>
                    </HoverCard>

                    <!-- MÓVIL: Sheet deslizante -->
                    <Sheet
                        v-else
                        :open="activeModule === module.title"
                        @update:open="(val) => (activeModule = val ? module.title : null)"
                    >
                        <SheetTrigger as-child>
                            <SidebarMenuButton
                                :is-active="module.isActive"
                                :class="moduleButtonClass"
                            >
                                <component :is="module.icon" class="h-4 w-4 shrink-0" />
                                <span class="truncate group-data-[collapsible=icon]:hidden">{{ module.title }}</span>
                            </SidebarMenuButton>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[75vw] max-w-sm p-0">
                            <!-- ScrollArea para el contenido del sheet, consistente con el menú principal -->
                            <ScrollArea class="h-full p-4">
                                <div class="flex flex-col space-y-4">
                                    <div
                                        v-for="(group, idx) in module.groups"
                                        :key="idx"
                                        class="pb-4 border-b border-border/40 last:border-0"
                                    >
                                        <div class="flex items-center gap-2 mb-2.5">
                                            <component :is="group.icon" class="h-4 w-4 text-primary" />
                                            <h3 class="text-xs font-semibold tracking-wide uppercase text-muted-foreground">
                                                {{ group.title }}
                                            </h3>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <Button
                                                v-for="item in group.items"
                                                :key="item.title"
                                                variant="ghost"
                                                size="sm"
                                                class="justify-start text-xs h-8 px-2.5 font-normal hover:bg-primary/10  transition-colors cursor-pointer"
                                                as-child
                                            >
                                                <Link v-if="item.url !== '#'" :href="item.url" @click="onNavigate">
                                                    {{ item.title }}
                                                </Link>
                                                <span v-else @click="() => { onNavigate(); item.onClick?.(); }">{{ item.title }}</span>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                        </SheetContent>
                    </Sheet>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </ScrollArea>
</template>