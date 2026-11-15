<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Horario de Clase (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, CalendarClock, Clock, School, BookOpen, Layers, Edit } from 'lucide-vue-next';

const props = defineProps<{
    horario: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Horarios', subtitle: 'Listado', href: route('horario.index') },
    { title: 'Detalle de Horario', subtitle: 'Consulta', href: route('horario.show', props.horario.id_hor) },
];

const formatTime = (timeStr?: string) => {
    if (!timeStr) return '--:--';
    return timeStr.substring(0, 5);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Detalle de Horario - Programación Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('horario.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Detalle de Horario de Clase
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Información completa de asignación de aula, sección y horas de dictado.
                    </p>
                </div>

                <Button as-child size="sm" class="gap-2">
                    <Link :href="route('horario.edit', horario.id_hor)">
                        <Edit class="h-4 w-4" />
                        <span>Editar</span>
                    </Link>
                </Button>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <CalendarClock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Bloque: {{ horario.dia_semana_hor }} ({{ formatTime(horario.hora_inicio_hor) }} - {{ formatTime(horario.hora_fin_hor) }})</CardTitle>
                    </div>
                    <CardDescription>
                        Identificador de registro: #{{ horario.id_hor }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1">
                            <span class="text-xs font-medium text-muted-foreground flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5 text-primary" /> Franja Horaria
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ formatTime(horario.hora_inicio_hor) }} - {{ formatTime(horario.hora_fin_hor) }}
                            </p>
                            <p class="text-xs text-muted-foreground">Día: {{ horario.dia_semana_hor }}</p>
                        </div>

                        <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1">
                            <span class="text-xs font-medium text-muted-foreground flex items-center gap-1.5">
                                <School class="h-3.5 w-3.5 text-primary" /> Aula Asignada
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ horario.aula?.codigo_aula_aul || 'No asignada' }}
                            </p>
                            <p class="text-xs text-muted-foreground">Capacidad: {{ horario.aula?.capacidad_maxima_aul || 'N/A' }} estudiantes</p>
                        </div>

                        <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1 md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground flex items-center gap-1.5">
                                <BookOpen class="h-3.5 w-3.5 text-primary" /> Unidad Curricular y Sección
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ horario.carga_academica?.unidad_curricular?.nombre_unc || 'Unidad Curricular' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Sección: {{ horario.carga_academica?.seccion?.nombre_sec || horario.carga_academica?.seccion?.codigo_sec || 'N/A' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('horario.index')">Regresar al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

