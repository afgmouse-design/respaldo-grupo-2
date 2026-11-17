<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Tipo de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Consulta técnica de un registro de `tipo_lapso(id_tla)` incluyendo código,
 * duración en semanas, nomenclatura, descripción y estatus lógico (`status_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a columnas reales de `tipo_lapso` y `esActivo` (`status_id`).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, Clock, CheckCircle2, XCircle } from 'lucide-vue-next';
import { esActivo } from '@/lib/status';

const props = defineProps<{
    tipoLapso: {
        id_tla?: number;
        id?: number;
        codigo_tla: string;
        nombre_tla: string;
        duracion_semanas_tla: number;
        nomenclatura_tla?: string | null;
        descripcion_tla?: string | null;
        status_id?: number;
    };
}>();

const tipoLapsoId = computed(() => props.tipoLapso.id_tla ?? props.tipoLapso.id ?? 0);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Lapso', subtitle: 'Listado', href: route('tipo_lapso.index') },
    { title: 'Detalle de Tipo de Lapso', subtitle: 'Consulta', href: route('tipo_lapso.show', tipoLapsoId.value) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Tipo de Lapso: ${tipoLapso.nombre_tla} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_lapso.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Tipo de Lapso Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Información técnica del régimen temporal para lapsos y trayectos.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('tipo_lapso.edit', tipoLapsoId)">
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
                            <Clock class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">{{ tipoLapso.nombre_tla }}</CardTitle>
                        </div>
                        <Badge :variant="esActivo(tipoLapso) ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="esActivo(tipoLapso)" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ esActivo(tipoLapso) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <CardDescription>Identificador único #{{ tipoLapsoId }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Código</span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ tipoLapso.codigo_tla }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Duración Estándar</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ tipoLapso.duracion_semanas_tla }} semanas
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Nomenclatura</span>
                            <p class="text-base font-medium text-slate-900 dark:text-slate-100 mt-1">
                                {{ tipoLapso.nomenclatura_tla || 'No especificada' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Estado Operativo</span>
                            <p class="text-base font-medium mt-1">
                                {{ esActivo(tipoLapso) ? 'Habilitado para planificación académica' : 'Inactivo' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripción</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1">
                                {{ tipoLapso.descripcion_tla || 'Sin descripción adicional.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('tipo_lapso.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
