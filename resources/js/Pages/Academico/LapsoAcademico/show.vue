<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Lapso Académico (Módulo: Estructura Académica).
 *
 * Propósito: Muestra la ficha técnica con los detalles del Lapso Académico seleccionado,
 * incluyendo el Año Académico padre y Tipo de Lapso al que pertenece.
 *
 * Historial:
 *  - 2026-11-14: Alineado a esquema `sogac_mio.sql` (`id_laa`, `codigo_laa`, `nombre_laa`,
 *    `nombre_ana`, `nombre_tla`, `fecha_inicio_laa`, `fecha_fin_laa`, `status_id` con `@/lib/status`).
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
    Clock,
    ArrowLeft,
    Pencil,
    CalendarDays,
    BookOpen,
    Hash,
} from 'lucide-vue-next';

interface LapsoAcademicoDetalle {
    id_laa: number;
    ana_id?: number | null;
    tla_id?: number | null;
    codigo_laa?: string | null;
    nombre_laa: string;
    fecha_inicio_laa?: string | null;
    fecha_fin_laa?: string | null;
    fase_operativa_laa?: string | null;
    status_id?: number | null;
    nombre_ana?: string | null;
    nombre_tla?: string | null;
    ano_status_id?: number | null;
}

const props = defineProps<{
    lapso: LapsoAcademicoDetalle;
}>();

const isActivo = computed(() => esActivo(props.lapso));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
    { title: props.lapso?.nombre_laa ?? 'Lapso Académico', subtitle: 'Ficha Técnica', href: route('academico.lapso-academico.show', props.lapso?.id_laa) },
];
</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Detalle Lapso Académico: ${props.lapso?.nombre_laa ?? ''} - Estructura Académica`" />

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
                            Detalle del Lapso Académico: {{ props.lapso?.nombre_laa }}
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
                        <Link :href="route('academico.lapso-academico.edit', props.lapso?.id_laa)">
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
                                #{{ props.lapso?.id_laa }}
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
                                    {{ props.lapso?.nombre_ana || 'Sin asignar' }}
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
                                {{ props.lapso?.nombre_laa }}
                            </p>
                            <p v-if="props.lapso?.codigo_laa || props.lapso?.nombre_tla" class="text-xs text-muted-foreground">
                                {{ [props.lapso?.codigo_laa, props.lapso?.nombre_tla].filter(Boolean).join(' · ') }}
                            </p>
                        </div>

                        <!-- FECHA DE INICIO -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Inicio
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.lapso?.fecha_inicio_laa ? String(props.lapso.fecha_inicio_laa).substring(0, 10) : 'No definida (N/A)' }}
                            </p>
                        </div>

                        <!-- FECHA DE FIN -->
                        <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <CalendarDays class="h-3.5 w-3.5" />
                                Fecha de Finalización
                            </div>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ props.lapso?.fecha_fin_laa ? String(props.lapso.fecha_fin_laa).substring(0, 10) : 'No definida (N/A)' }}
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

