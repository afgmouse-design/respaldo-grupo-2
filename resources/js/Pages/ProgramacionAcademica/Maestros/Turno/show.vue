<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Turno Académico (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta y detalle de un turno registrado en PostgreSQL (`turno`).
 *
 * Historial:
 * - 2026-11-16: Ordenado de importaciones según estándar ESLint del proyecto.
 */
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Clock, Pencil, XCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    turno: {
        id_tur: number;
        codigo_tur: string;
        nombre_tur: string;
        hora_inicio_tur?: string | null;
        hora_fin_tur?: string | null;
        descripcion_tur?: string | null;
        status_id?: number;
        status?: boolean;
        created_at?: string;
        updated_at?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: route('seccion.index') },
    { title: 'Turnos Académicos', subtitle: 'Listado', href: route('turno.index') },
    { title: 'Detalle de Turno', subtitle: 'Consulta', href: route('turno.show', props.turno.id_tur) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Turno: ${turno.nombre_tur} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('turno.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Turno Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle y horarios del turno institucional.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('turno.edit', turno.id_tur)">
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
                            <CardTitle class="text-lg font-semibold">{{ turno.nombre_tur }}</CardTitle>
                        </div>
                        <Badge :variant="esActivo(turno) ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="esActivo(turno)" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ esActivo(turno) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <CardDescription>Código de registro: #{{ turno.id_tur }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Código</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ turno.codigo_tur }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Denominación</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ turno.nombre_tur }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Franja Horaria</span>
                            <p class="text-base font-mono font-semibold text-primary mt-1">
                                {{ turno.hora_inicio_tur || '07:00' }} – {{ turno.hora_fin_tur || '12:30' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Estatus Operativo</span>
                            <p class="text-base font-medium mt-1">
                                {{ esActivo(turno) ? 'Disponible para programación de secciones' : 'Inactivo' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripción</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ turno.descripcion_tur || 'Sin descripción adicional.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('turno.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

