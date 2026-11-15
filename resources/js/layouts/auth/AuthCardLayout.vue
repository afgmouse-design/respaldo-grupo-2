<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppearanceTabsSwitch from '@/components/AppearanceTabsSwitch.vue';
import AppLogoSogac from '@/components/AppLogoSogac.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10 overflow-hidden"
    >
        <!-- Fondo con textura y acentos rojos sutiles -->
        <div class="absolute inset-0 -z-10">
            <!-- Gradiente base neutro -->
            <div class="absolute inset-0 bg-gradient-to-br from-background via-background to-muted/50 dark:from-background dark:via-background dark:to-muted/30"></div>
            
            <!-- Degradado rojo tenue en esquina superior izquierda -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-red-500/5 via-transparent to-transparent dark:from-red-500/10"></div>
            
            <!-- Patrón de puntos muy tenue -->
            <div class="absolute inset-0 opacity-30 dark:opacity-20" style="background-image: radial-gradient(circle at 1px 1px, hsl(var(--foreground)) 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
            
            <!-- Círculos borrosos (rojo y neutro) -->
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-primary/5 blur-3xl dark:bg-primary/10"></div>
            <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-red-500/5 blur-3xl dark:bg-red-500/10"></div>
        </div>

        <!-- Conmutador de tema -->
        <div class="absolute top-4 right-4 z-20">
            <AppearanceTabsSwitch />
        </div>

        <div class="flex w-full max-w-md flex-col gap-6">
        

            <!-- Tarjeta de acceso -->
            <div class="flex flex-col gap-6">
                <Card class="rounded-2xl border-border/50 bg-card/80 backdrop-blur-sm shadow-xl transition-all duration-300 dark:bg-card/60">
                        <!-- Logo con enlace a inicio -->
            <Link
                :href="home()"
                class="flex items-center justify-center self-center transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50 rounded-full"
            >
                <div class="flex size-18 p-3 items-center justify-center rounded-full bg-primary shadow-md">
                    <AppLogoSogac color="text-white" class="h-8 w-8" />
                </div>
            </Link>
                    <CardHeader class="px-8 pt-2 pb-4 text-center space-y-2">
                        <CardTitle class="text-2xl font-semibold tracking-tight text-foreground">
                            {{ title }}
                        </CardTitle>
                        <CardDescription class="text-sm text-muted-foreground">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-8 pb-8">
                        <slot />
                    </CardContent>
                </Card>

                <!-- Texto legal -->
                <p class="text-center text-xs text-muted-foreground/70">
                    Al acceder aceptas nuestras
                    <a href="#" class="underline underline-offset-2 hover:text-foreground transition-colors">
                        políticas de uso
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>