<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Condición de Sede (Organización Institucional).
 *
 * Propósito: Ficha técnica de consulta y detalle de una condición en `condicion_sede`.
 *
 * Historial:
 *  - 2026-11-14: Alineado con `condicion_sede` (`id_cse`, `nombre_cse`, `descripcion_cse`, `status_id`).
 *  - 2026-11-16: Ordenado de importaciones según estándar ESLint del proyecto.
 */
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Building2, CheckCircle2, Pencil, XCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    condicion: {
        id_cse: number;
        nombre_cse: string;
        descripcion_cse?: string | null;
        status_id?: number;
        status?: boolean | number;
        created_at?: string;
        updated_at?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Maestros', href: '#' },
    { title: 'Condición', subtitle: 'Listado', href: route('condicion.index') },
    { title: 'Detalle de Condición', subtitle: 'Consulta', href: route('condicion.show', props.condicion.id_cse) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Condición: ${condicion.nombre_cse} - Organización Institucional`" />

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
                        <Link :href="route('condicion.edit', condicion.id_cse)">
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
                            <CardTitle class="text-lg font-semibold">{{ condicion.nombre_cse }}</CardTitle>
                        </div>
                        <Badge :variant="esActivo(condicion) ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="esActivo(condicion)" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ esActivo(condicion) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <CardDescription>Código de registro: #{{ condicion.id_cse }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Nombre Oficial</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ condicion.nombre_cse }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Estatus del Registro</span>
                            <p class="text-base font-medium mt-1">
                                {{ esActivo(condicion) ? 'Habilitado para asignación' : 'Deshabilitado' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripción / Observaciones</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ condicion.descripcion_cse || 'Sin descripción registrada.' }}
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

