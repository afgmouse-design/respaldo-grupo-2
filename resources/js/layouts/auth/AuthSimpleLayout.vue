<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppearanceTabsSwitch from '@/components/AppearanceTabsSwitch.vue';
import AppLogoSogac from '@/components/AppLogoSogac.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

// Pasos estáticos
const steps = [
    { title: 'Acceso Inicial', description: 'Usa tu Cédula como nombre de usuario.' },
    { title: 'Seguridad', description: 'Actualiza tu contraseña al ingresar.' },
    { title: 'Verificación', description: 'Valida que tus datos sean correctos.' },
];
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10 overflow-hidden">
        <!-- Fondo con gradiente y blur (sin cambios) -->
<div class="absolute inset-0 z-0 overflow-hidden">
    <div class="absolute inset-0 bg-white dark:bg-[#050505]"></div>

    <div class="hidden dark:block">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,_rgba(153,27,27,0.15)_0%,_transparent_70%)]"></div>
        
        <div class="absolute inset-0 bg-gradient-to-b from-black via-transparent to-black opacity-80"></div>
        
        <div class="absolute top-0 -left-40 h-80 w-80 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute bottom-0 -right-40 h-80 w-80 rounded-full bg-amber-500/5 blur-3xl"></div>
    </div>

    <div class="dark:hidden absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-200"></div>
        <div class="absolute top-0 -left-40 h-80 w-80 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="absolute bottom-0 -right-40 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-96 w-96 rounded-full bg-primary/5 blur-3xl"></div>
    </div>

    <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.08] pointer-events-none mix-blend-overlay"
        style="background-image: url('https://grainy-gradients.vercel.app/noise.svg')">
    </div>
</div>

        <!-- Conmutador de tema -->
        <div class="absolute top-4 right-4 z-20">
            <AppearanceTabsSwitch />
        </div>

        <!-- Layout de dos columnas -->
        <div class="flex gap-30 items-center w-full justify-center">
            <!-- Columna izquierda: Lista vertical de pasos (solo visible en lg) -->
            <div class="max-w-md hidden lg:block z-20">
                <h2 class="text-2xl font-semibold tracking-tight text-foreground/90 mb-4">
                    Guía de inicio rápido
                </h2>
                <ul class="flex flex-col">
                   <li v-for="(step, index) in steps" :key="index" 
    class="relative flex gap-6 pb-8 last:pb-0 group">
    
    <div v-if="index !== steps.length - 1" 
        class="absolute left-[7px] top-[14px] bottom-[-14px] w-[1px] bg-zinc-200 dark:bg-zinc-800">
    </div>

    <div class="relative z-10 flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-950 mt-1.5">
        <div class="h-1.5 w-1.5 rounded-full bg-zinc-400 dark:bg-zinc-600 group-hover:bg-primary transition-colors"></div>
    </div>

    <div class="flex flex-col gap-1">
        <h3 class="text-sm font-bold leading-none text-zinc-900 dark:text-zinc-100 lg:text-base">
            {{ step.title }}
        </h3>
        <p class="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400 lg:text-sm">
            {{ step.description }}
        </p>
    </div>
</li>
                </ul>
            </div>

            <!-- Columna derecha: Formulario (sin cambios) -->
            <div class="w-full max-w-sm z-20">
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col items-center gap-4">
                        <Link :href="home()"
                            class="flex flex-col items-center gap-2 font-medium transition-transform hover:scale-105">
                            <div
                                class="mb-1 flex h-18 w-18 items-center justify-center rounded-full bg-primary p-3 shadow-md">
                                <AppLogoSogac color="text-white" class="h-10 w-10" />
                            </div>
                            <span class="sr-only">{{ title }}</span>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                            <p class="text-sm text-muted-foreground">{{ description }}</p>
                        </div>
                    </div>
                    <slot />
                    <p class="text-center text-xs text-muted-foreground/70">
                        Al acceder aceptas nuestras
                        <a href="#" class="underline underline-offset-2 hover:text-foreground transition-colors">
                            políticas de uso
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>