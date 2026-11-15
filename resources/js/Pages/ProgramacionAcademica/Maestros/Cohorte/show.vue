<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Cohorte (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta y detalle de una cohorte académica.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, GraduationCap, CheckCircle2, XCircle, Calendar } from 'lucide-vue-next';

const props = defineProps<{
    cohorte: {
        id_coh: number;
        nombre_coh: string;
        fecha_inicio_coh: string;
        status?: boolean;
        lapso_academico?: { id_la: number; nombre_la: string; fecha_inicio_la: string; fecha_fin_la: string };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Cohortes', subtitle: 'Listado', href: route('cohorte.index') },
    { title: 'Detalle de Cohorte', subtitle: 'Consulta', href: route('cohorte.show', props.cohorte.id_coh) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Cohorte: ${cohorte.nombre_coh} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('cohorte.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Cohorte
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle y lapso académico asociado.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('cohorte.edit', cohorte.id_coh)">
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
                            <GraduationCap class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">{{ cohorte.nombre_coh }}</CardTitle>
                        </div>
                        <Badge :variant="cohorte.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="cohorte.status" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ cohorte.status ? 'Activa' : 'Inactiva' }}
                        </Badge>
                    </div>
                    <CardDescription>Identificador interno: #{{ cohorte.id_coh }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Nombre Oficial</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ cohorte.nombre_coh }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Fecha de Inicio</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <Calendar class="h-4 w-4 text-primary" />
                                {{ cohorte.fecha_inicio_coh }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Lapso Académico Vinculado</span>
                            <p class="text-base font-medium text-slate-900 dark:text-slate-100 mt-1">
                                {{ cohorte.lapso_academico?.nombre_la || 'No asignado' }}
                                <span v-if="cohorte.lapso_academico" class="text-xs text-muted-foreground ml-2">
                                    ({{ cohorte.lapso_academico.fecha_inicio_la }} al {{ cohorte.lapso_academico.fecha_fin_la }})
                                </span>
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('cohorte.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

