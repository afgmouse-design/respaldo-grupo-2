<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Lapso Académico (Módulo: Estructura Académica).
 *
 * Propósito: Muestra la ficha técnica con los detalles del Lapso Académico seleccionado,
 * incluyendo el Año Académico padre al que pertenece.
 *
 * Actualización: Corrección de ruta de importación de '@/Layouts' a '@/layouts'
 * para compatibilidad con sistemas de archivos sensibles a mayúsculas/minúsculas (Linux).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Clock,
    ArrowLeft,
    Pencil,
    CalendarDays,
    BookOpen,
    Hash,
} from 'lucide-vue-next';

interface LapsoAcademicoDetalle {
    id_la: number;
    ano_id?: number | null;
    nombre_la: string;
    fecha_inicio_la?: string | null;
    fecha_fin_la?: string | null;
    estado_la: boolean | number;
    nombre_ano?: string | null;
    ano_status?: boolean | number;
}

const props = defineProps<{
    lapso: LapsoAcademicoDetalle;
}>();

const isActivo = computed(() => {
    const val = props.lapso?.estado_la;
    return val === true || val === 1 || val === '1' || val === 'true';
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
    { title: props.lapso?.nombre_la ?? 'Lapso Académico', subtitle: 'Ficha Técnica', href: route('academico.lapso-academico.show', props.lapso?.id_la) },
];
</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Detalle Lapso Académico: ${props.lapso?.nombre_la ?? ''} - Estructura Académica`" />

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
                            <Link :href="route('academico.lapso-academico.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Detalle del Lapso Académico: {{ props.lapso?.nombre_la }}
                        </h1>
                        <Badge :variant="isActivo ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ isActivo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Ficha informativa del período lectivo asociado al año académico institucional.
                    </p>
                </div>

                <div class="flex items-center gap-2 ml-10 sm:ml-0">
                    <Button as-child variant="outline">
                        <Link :href="route('academico.lapso-academico.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver
                        </Link>
                    </Button>
                    <Button as-child class="shadow-sm">
                        <Link :href="route('academico.lapso-academico.edit', props.lapso?.id_la)">
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
                            <Clock class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Información General del Lapso Académico</CardTitle>
                            <CardDescription>
                                Datos de identificación, adscripción anual, fechas de vigencia y estado operativo.
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
                                #{{ props.lapso?.id_la }}
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

                        <!-- AÑO ACADÉMICO PADRE -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <BookOpen class="h-3.5 w-3.5" />
                                Año Académico al que Pertenece
                            </div>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                <span class="font-mono text-sm font-semibold px-2.5 py-0.5 rounded bg-muted text-foreground">
                                    {{ props.lapso?.nombre_ano || 'Sin asignar' }}
                                </span>
                            </p>
                        </div>

                        <!-- NOMBRE DEL LAPSO -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <Clock class="h-3.5 w-3.5" />
                                Nombre del Lapso Académico
                            </div>
                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                {{ props.lapso?.nombre_la }}
                            </p>
                        </div>

                        <!-- FECHA DE INICIO -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Inicio
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.lapso?.fecha_inicio_la ? String(props.lapso.fecha_inicio_la).substring(0, 10) : 'No definida (N/A)' }}
                            </p>
                        </div>

                        <!-- FECHA DE FIN -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Finalización
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.lapso?.fecha_fin_la ? String(props.lapso.fecha_fin_la).substring(0, 10) : 'No definida (N/A)' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex items-center justify-end gap-2 border-t pt-4 dark:border-slate-800">
                    <Button as-child variant="outline">
                        <Link :href="route('academico.lapso-academico.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver al Listado
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

