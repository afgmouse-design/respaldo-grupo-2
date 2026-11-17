<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Año Académico (Módulo: Estructura Académica).
 *
 * Propósito: Muestra la ficha técnica con los detalles del Año Académico seleccionado.
 *
 * Actualización: Corrección de ruta de importación de '@/Layouts' a '@/layouts'
 * para compatibilidad con sistemas de archivos sensibles a mayúsculas/minúsculas (Linux).
 * Historial:
 *  - 2026-11-14: Alineado a esquema `sogac_mio.sql` (`id_ana`, `nombre_ana`, `fecha_inicio_ana`,
 *    `fecha_fin_ana`, `status_id` mediante `@/lib/status`).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Calendar,
    ArrowLeft,
    Pencil,
    CalendarDays,
    Hash,
} from 'lucide-vue-next';

interface AnoAcademicoDetalle {
    id_ana: number;
    nombre_ana: string;
    fecha_inicio_ana?: string | null;
    fecha_fin_ana?: string | null;
    status_id?: number | null;
}

const props = defineProps<{
    ano: AnoAcademicoDetalle;
}>();

const isActivo = computed(() => esActivo(props.ano));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Años Académicos', subtitle: 'Gestión de Años Académicos', href: route('academico.ano-academico.index') },
    { title: props.ano?.nombre_ana ?? 'Año Académico', subtitle: 'Ficha Técnica', href: route('academico.ano-academico.show', props.ano?.id_ana) },
];
</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Detalle Año Académico: ${props.ano?.nombre_ana ?? ''} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                            title="Volver al listado"
                        >
                            <Link :href="route('academico.ano-academico.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Detalle del Año Académico: {{ props.ano?.nombre_ana }}
                        </h1>
                        <Badge :variant="isActivo ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ isActivo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Ficha informativa del período anual regular registrado en el sistema.
                    </p>
                </div>

                <div class="flex items-center gap-2 ml-10 sm:ml-0">
                    <Button as-child variant="outline">
                        <Link :href="route('academico.ano-academico.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver
                        </Link>
                    </Button>
                    <Button as-child class="shadow-sm">
                        <Link :href="route('academico.ano-academico.edit', props.ano?.id_ana)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- TARJETA DE DETALLES -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Información General del Año Académico</CardTitle>
                            <CardDescription>
                                Datos identificadores, vigencia temporal y estado operativo actual.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- ID -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <Hash class="h-3.5 w-3.5" />
                                ID del Registro
                            </div>
                            <p class="text-lg font-mono font-bold text-slate-900 dark:text-slate-100">
                                #{{ props.ano?.id_ana }}
                            </p>
                        </div>

                        <!-- ESTATUS -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Estatus Operativo
                            </div>
                            <div class="pt-0.5">
                                <Badge :variant="isActivo ? 'successCustom' : 'destructiveCustom'">
                                    {{ isActivo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1 sm:col-span-2">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <Calendar class="h-3.5 w-3.5" />
                                Nombre del Año Académico
                            </div>
                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                {{ props.ano?.nombre_ana }}
                            </p>
                        </div>

                        <!-- FECHA DE INICIO -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Inicio
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.ano?.fecha_inicio_ana ? String(props.ano.fecha_inicio_ana).substring(0, 10) : 'No definida (N/A)' }}
                            </p>
                        </div>

                        <!-- FECHA DE FIN -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Finalización
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.ano?.fecha_fin_ana ? String(props.ano.fecha_fin_ana).substring(0, 10) : 'No definida (N/A)' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex items-center justify-end gap-2 border-t pt-4 dark:border-slate-800">
                    <Button as-child variant="outline">
                        <Link :href="route('academico.ano-academico.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver al Listado
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

