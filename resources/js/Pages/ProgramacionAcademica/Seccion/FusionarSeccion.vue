<script setup lang="ts">
/**
 * Vista Standalone: Fusionar Secciones (pronto)
 *
 * Propósito: Módulo de planificación y consolidación de matrícula para unir
 * grupos con baja densidad estudiantil hacia una sección receptora.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    GitMerge,
    ArrowLeft,
    Clock,
    ShieldAlert,
    CheckCircle2,
    Users,
} from 'lucide-vue-next';
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Fusionar Secciones (pronto)', href: route('seccion.fusionar') },
    ]">
        <Head title="Fusionar Secciones - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-4xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-600">
                            <GitMerge class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            Fusión de Secciones
                            <Badge variant="secondary" class="text-xs">Próximamente</Badge>
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Consolidación de estudiantes y reorganización de carga académica entre secciones afines.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('seccion.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver a Secciones
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- TARJETA INFORMATIVA PRINCIPAL -->
            <div class="bg-card border rounded-2xl p-8 shadow-xs text-center space-y-4">
                <div class="mx-auto w-16 h-16 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-indigo-600">
                    <Clock class="h-8 w-8" />
                </div>

                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                    Fase de Consolidación de Matrícula
                </h2>

                <p class="text-sm text-muted-foreground max-w-lg mx-auto leading-relaxed">
                    Esta función automatizará el traspaso masivo de estudiantes inscritos de una sección origen a una sección destino que comparta la misma cohorte, trayecto, semestre y turno, resguardando la trazabilidad de actas de evaluación en la bitácora institucional.
                </p>

                <div class="pt-6 border-t grid grid-cols-1 sm:grid-cols-3 gap-4 text-left max-w-2xl mx-auto">
                    <div class="p-3 bg-muted/40 rounded-xl border">
                        <div class="text-xs font-semibold text-foreground flex items-center gap-1.5 mb-1">
                            <CheckCircle2 class="h-4 w-4 text-primary" />
                            Validación de Cupos
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            Controla que la suma de alumnos no sobrepase la capacidad máxima del aula destino.
                        </p>
                    </div>

                    <div class="p-3 bg-muted/40 rounded-xl border">
                        <div class="text-xs font-semibold text-foreground flex items-center gap-1.5 mb-1">
                            <CheckCircle2 class="h-4 w-4 text-primary" />
                            Preservación Kárdex
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            Mantiene intactas las calificaciones ya cargadas en las unidades curriculares.
                        </p>
                    </div>

                    <div class="p-3 bg-muted/40 rounded-xl border">
                        <div class="text-xs font-semibold text-foreground flex items-center gap-1.5 mb-1">
                            <CheckCircle2 class="h-4 w-4 text-primary" />
                            Liberación de Aulas
                        </div>
                        <p class="text-[11px] text-muted-foreground">
                            Desocupa automáticamente los bloques y aulas de la sección absorbida.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

