<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Ficha técnica y de consulta completa de una Unidad Curricular (materia) registrada,
 * mostrando su semestre, trayecto formativo, tipología y carga horaria/créditos.
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    BookOpen,
    ArrowLeft,
    Pencil,
    CheckCircle2,
    XCircle,
    FileText,
    Tag,
    Calendar,
    Layers,
    Clock,
    Award,
} from 'lucide-vue-next';

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
}

interface TrayectoItem {
    id_tra: number;
    nombre_tra: string;
}

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc: string | null;
    nombre_unc: string;
    tuc_id: number;
    semestre_unc?: number | null;
    tra_id?: number | null;
    unidades_credito_unc?: number | null;
    htea_unc?: number | null;
    htei_unc?: number | null;
    thte_unc?: number | null;
    tipo_unidad_curricular?: TipoUnidadCurricularItem | null;
    trayecto?: TrayectoItem | null;
    status_id?: number;
    status?: boolean;
}

const props = defineProps<{
    unidad: UnidadCurricularItem;
}>();

const esActivo = computed(() => {
    return props.unidad.status ?? (props.unidad.status_id === 1);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Unidades Curriculares', subtitle: 'Materias y Asignaturas', href: route('academico.unidad-curricular.index') },
    { title: `Ficha: ${props.unidad.nombre_unc}`, subtitle: 'Detalle', href: route('academico.unidad-curricular.show', props.unidad.id_unc) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Materia: ${unidad.nombre_unc} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle curricular y ubicación de la asignatura en el plan de estudios.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.unidad-curricular.edit', unidad.id_unc)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Materia</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHA PRINCIPAL -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ unidad.nombre_unc }}
                                </CardTitle>
                                <CardDescription>Código de registro: #{{ unidad.id_unc }}</CardDescription>
                            </div>
                        </div>

                        <Badge :variant="esActivo ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3 w-fit">
                            <CheckCircle2 v-if="esActivo" class="h-3.5 w-3.5 text-emerald-500" />
                            <XCircle v-else class="h-3.5 w-3.5 text-slate-400" />
                            {{ esActivo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- DATOS BÁSICOS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="h-3.5 w-3.5" /> Código de la Materia
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                {{ unidad.codigo_unc || 'No asignado' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <BookOpen class="h-3.5 w-3.5" /> Nombre de la Asignatura
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ unidad.nombre_unc }}
                            </p>
                        </div>

                        <div class="space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-primary" /> Semestre / Período
                            </span>
                            <p class="text-base font-semibold text-foreground">
                                Semestre {{ unidad.semestre_unc || 1 }}
                            </p>
                        </div>

                        <div class="space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Layers class="h-3.5 w-3.5 text-primary" /> Trayecto Formativo
                            </span>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ unidad.trayecto?.nombre_tra || 'Sin trayecto asignado' }}
                            </p>
                        </div>

                        <div class="md:col-span-2 space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Tag class="h-3.5 w-3.5" /> Tipo de Unidad Curricular
                            </span>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ unidad.tipo_unidad_curricular?.nombre_tuc || 'No clasificada' }}
                            </p>
                        </div>
                    </div>

                    <!-- CARGA ACADÉMICA Y CRÉDITOS -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-card border rounded-xl text-center space-y-1">
                            <div class="text-xs text-muted-foreground font-medium flex items-center justify-center gap-1">
                                <Award class="h-3.5 w-3.5 text-amber-500" /> Créditos (UC)
                            </div>
                            <div class="text-xl font-bold text-amber-600 dark:text-amber-400">
                                {{ unidad.unidades_credito_unc ?? 3 }}
                            </div>
                        </div>

                        <div class="p-3 bg-card border rounded-xl text-center space-y-1">
                            <div class="text-xs text-muted-foreground font-medium flex items-center justify-center gap-1">
                                <Clock class="h-3.5 w-3.5 text-blue-500" /> HTEA (Docente)
                            </div>
                            <div class="text-xl font-bold text-foreground">
                                {{ unidad.htea_unc ?? 3 }}h
                            </div>
                        </div>

                        <div class="p-3 bg-card border rounded-xl text-center space-y-1">
                            <div class="text-xs text-muted-foreground font-medium flex items-center justify-center gap-1">
                                <Clock class="h-3.5 w-3.5 text-indigo-500" /> HTEI (Indep.)
                            </div>
                            <div class="text-xl font-bold text-foreground">
                                {{ unidad.htei_unc ?? 3 }}h
                            </div>
                        </div>

                        <div class="p-3 bg-card border rounded-xl text-center space-y-1">
                            <div class="text-xs text-muted-foreground font-medium flex items-center justify-center gap-1">
                                <Layers class="h-3.5 w-3.5 text-primary" /> Total Horas (THTE)
                            </div>
                            <div class="text-xl font-bold text-primary">
                                {{ unidad.thte_unc ?? 6 }}h
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
