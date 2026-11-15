<script setup lang="ts">
/**
 * Vista Standalone: Tipo De Sección (sin Tabla)
 *
 * Propósito: Catálogo normativo y parametrización de tipos de sección en el SOGAC
 * (Regular, Plan Especial de Referencia [PER], Repitiente, Intensivo, Proyecto).
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Layers,
    ArrowLeft,
    Plus,
    CheckCircle2,
    AlertCircle,
    Info,
    Sparkles,
} from 'lucide-vue-next';

interface TipoSeccionItem {
    codigo: string;
    nombre: string;
    descripcion: string;
    cupo_sugerido: number;
    permite_per: boolean;
    color: string;
}

const props = defineProps<{
    tipos: TipoSeccionItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Tipo De Sección (sin Tabla)', href: route('tipo_seccion.index') },
    ]">
        <Head title="Tipos de Sección - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-6xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Layers class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Catálogo de Tipos de Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Definiciones normativas y reglas operativas para la clasificación y oferta de secciones académicas.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('seccion.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver a Secciones
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('seccion.create')">
                            <Plus class="h-4 w-4 mr-2" />
                            Nueva Sección
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- TARJETAS DE TIPOS DE SECCIÓN -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="tipo in props.tipos"
                    :key="tipo.codigo"
                    class="bg-card border rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-primary/50 transition-all"
                >
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <Badge variant="secondary" class="font-mono text-xs font-bold">
                                {{ tipo.codigo }}
                            </Badge>
                            <Badge variant="outline" class="text-xs">
                                Sugerido: {{ tipo.cupo_sugerido }} cupos
                            </Badge>
                        </div>

                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">
                            {{ tipo.nombre }}
                        </h2>

                        <p class="text-sm text-muted-foreground mb-4 leading-relaxed">
                            {{ tipo.descripcion }}
                        </p>
                    </div>

                    <div class="pt-4 border-t flex items-center justify-between text-xs text-muted-foreground">
                        <span v-if="tipo.permite_per" class="inline-flex items-center text-amber-600 font-medium">
                            <AlertCircle class="h-3.5 w-3.5 mr-1" />
                            Acompañamiento PER
                        </span>
                        <span v-else class="inline-flex items-center text-emerald-600 font-medium">
                            <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                            Secuencia Regular
                        </span>

                        <Button size="sm" variant="ghost" as-child class="h-8 text-xs">
                            <Link :href="route('seccion.create')">
                                Crear Sección
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- NOTA NORMATIVA -->
            <div class="p-4 rounded-xl bg-muted/40 border border-muted flex items-start gap-3 text-xs text-muted-foreground">
                <Info class="h-5 w-5 text-primary shrink-0 mt-0.5" />
                <div>
                    <strong class="font-semibold text-foreground">Regulación Curricular Institucional:</strong>
                    Los tipos de sección son aplicables a cualquier sede y programa nacional de formación (PNF). Al aperturar secciones en el lapso académico activo, la selección del tipo determina el algoritmo de priorización en la inscripción del estudiante y la conformación de actas finales de notas.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

