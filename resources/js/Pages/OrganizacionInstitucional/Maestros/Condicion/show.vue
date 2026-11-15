<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Condición (Organización Institucional).
 *
 * Propósito: Ficha técnica de consulta y detalle de una condición institucional registrada.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, Building2, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    condicion: {
        id_cdn: number;
        nombre_cdn: string;
        descripcion_cdn?: string | null;
        status?: boolean;
        created_at?: string;
        updated_at?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Maestros', href: '#' },
    { title: 'Condición', subtitle: 'Listado', href: route('condicion.index') },
    { title: 'Detalle de Condición', subtitle: 'Consulta', href: route('condicion.show', props.condicion.id_cdn) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Condición: ${condicion.nombre_cdn} - Organización Institucional`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('condicion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Condición
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Información detallada y estatus institucional.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('condicion.edit', condicion.id_cdn)">
                            <Pencil class="h-4 w-4" />
                            <span>Editar</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">{{ condicion.nombre_cdn }}</CardTitle>
                        </div>
                        <Badge :variant="condicion.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="condicion.status" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ condicion.status ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <CardDescription>Código de registro: #{{ condicion.id_cdn }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Nombre Oficial</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ condicion.nombre_cdn }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Estatus del Registro</span>
                            <p class="text-base font-medium mt-1">
                                {{ condicion.status ? 'Habilitado para asignación' : 'Deshabilitado' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripción / Observaciones</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ condicion.descripcion_cdn || 'Sin descripción registrada.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('condicion.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

