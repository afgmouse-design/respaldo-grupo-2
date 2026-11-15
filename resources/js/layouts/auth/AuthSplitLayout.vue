<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppearanceTabsSwitch from '@/components/AppearanceTabsSwitch.vue';
import AppLogoSogac from '@/components/AppLogoSogac.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid min-h-svh lg:grid-cols-2 bg-white dark:bg-[#09090b]">

        <!-- Panel Izquierdo -->
        <div class="relative hidden h-full flex-col p-12 lg:flex border-border/50 overflow-hidden">
            
            <!-- Fondo adaptable -->
            <div class="absolute inset-4 rounded-2xl z-0 
                        bg-zinc-100 dark:bg-zinc-950 
                        border border-zinc-200 dark:border-zinc-800">

                <!-- Efecto de brillo sutil en claro -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,#ffffff_0%,transparent_60%)] dark:bg-[radial-gradient(circle_at_top_left,#ffffff_0%,transparent_70%)] opacity-60 dark:opacity-10"></div>
                
                <!-- Patrón de puntos -->
                <div class="absolute inset-0 opacity-40 dark:opacity-20"
                     style="background-image: radial-gradient(#71717a 1px, transparent 1px); background-size: 28px 28px;">
                </div>
            </div>

            <div class="relative z-20">
                <Link :href="home()" class="flex items-center gap-4 group">
                    <div class="flex size-11 p-1.5 items-center justify-center rounded-xl 
                                bg-white dark:bg-zinc-900 shadow-sm 
                                transition-transform duration-300 group-hover:scale-105">
                        <AppLogoSogac 
                            :color="$colorMode === 'dark' ? 'text-zinc-200' : 'text-zinc-800'" 
                        />
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white">
                            UPTP J.J. MONTILLA
                        </span>
                        <span class="text-[9px] font-semibold uppercase tracking-[0.25em] 
                                     text-zinc-500 dark:text-white/60">
                            Gestión Académica Universitaria
                        </span>
                    </div>
                </Link>
            </div>

            <div class="relative z-20 mt-20 max-w-md">
                <h2 class="text-4xl font-bold tracking-tight text-zinc-900 dark:text-white leading-[1.1]">
                    Excelencia tecnológica para la formación académica.
                </h2>
                <p class="mt-6 text-base text-zinc-600 dark:text-white/70 font-normal leading-relaxed">
                    Bienvenido al portal SOGAC. Una plataforma diseñada para optimizar los procesos de inscripción, 
                    consulta de notas y gestión administrativa.
                </p>
            </div>

            <!-- Guía de inicio rápido -->
            <div class="relative z-20 mt-auto">
                <p class="mb-4 ml-1 text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-500 dark:text-white/70">
                    Guía de inicio rápido
                </p>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="group relative flex flex-col gap-2 rounded-xl border border-zinc-200 dark:border-white/10 
                                bg-white/70 dark:bg-white/5 p-4 backdrop-blur-md 
                                transition-all duration-300 hover:bg-white dark:hover:bg-white/10 
                                hover:border-zinc-300 dark:hover:border-white/20">
                        <div class="flex items-center gap-3">
                            <Badge class="font-semibold bg-zinc-800 dark:bg-white/80 text-white dark:text-black">
                                1
                            </Badge>
                            <h4 class="text-[13px] font-bold tracking-tight text-zinc-800 dark:text-white/90">Acceso Inicial</h4>
                        </div>
                        <p class="text-[11px] leading-relaxed text-zinc-600 dark:text-white/60">
                            Usa tu <span class="font-semibold text-zinc-900 dark:text-white">Cédula</span> como nombre de usuario.
                        </p>
                    </div>

                    <div class="group relative flex flex-col gap-2 rounded-xl border border-zinc-200 dark:border-white/10 
                                bg-white/70 dark:bg-white/5 p-4 backdrop-blur-md 
                                transition-all duration-300 hover:bg-white dark:hover:bg-white/10 
                                hover:border-zinc-300 dark:hover:border-white/20">
                        <div class="flex items-center gap-3">
                            <Badge class="font-semibold bg-zinc-800 dark:bg-white/80 text-white dark:text-black">
                                2
                            </Badge>
                            <h4 class="text-[13px] font-bold tracking-tight text-zinc-800 dark:text-white/90">Seguridad</h4>
                        </div>
                        <p class="text-[11px] leading-relaxed text-zinc-600 dark:text-white/60">
                            Actualiza tu <span class="font-semibold text-zinc-900 dark:text-white">contraseña</span> al ingresar.
                        </p>
                    </div>

                    <div class="group relative flex flex-col gap-2 rounded-xl border border-zinc-200 dark:border-white/10 
                                bg-white/70 dark:bg-white/5 p-4 backdrop-blur-md 
                                transition-all duration-300 hover:bg-white dark:hover:bg-white/10 
                                hover:border-zinc-300 dark:hover:border-white/20">
                        <div class="flex items-center gap-3">
                            <Badge class="font-semibold bg-zinc-800 dark:bg-white/80 text-white dark:text-black">
                                3
                            </Badge>
                            <h4 class="text-[13px] font-bold tracking-tight text-zinc-800 dark:text-white/90">Verificación</h4>
                        </div>
                        <p class="text-[11px] leading-relaxed text-zinc-600 dark:text-white/60">
                            Valida que tus <span class="font-semibold text-zinc-900 dark:text-white">datos</span> sean correctos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho (sin cambios importantes) -->
        <div class="relative flex flex-col items-center justify-center p-4 lg:p-8">
            <div class="absolute top-8 right-8">
                <AppearanceTabsSwitch />
            </div>

            <div class="w-full max-w-[400px] space-y-6 m-auto my-auto">

                <div class="space-y-4 lg:text-left">
                    <div class="lg:hidden flex flex-col items-center mb-2">
                        <div class="size-18 p-3 bg-zinc-800 dark:bg-zinc-900 rounded-full shadow-xl mb-4">
                            <AppLogoSogac color="text-white" />
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-black dark:text-white text-center" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-center text-black/60 dark:text-white/80 font-medium" v-if="description">
                        {{ description }}
                    </p>
                </div>

                <div class="space-y-6">
                    <slot />
                </div>

                <footer class="mt-6 border-t border-slate-100 dark:border-zinc-800/50">
                    <p class="text-center text-[11px] leading-relaxed text-slate-400 dark:text-zinc-500">
                        Este sistema es de uso exclusivo para la comunidad universitaria. Al ingresar, usted acepta
                        nuestras
                        <a href="#" 
                           class="text-slate-600 dark:text-zinc-300 underline underline-offset-4 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            Políticas de Seguridad
                        </a>.
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>