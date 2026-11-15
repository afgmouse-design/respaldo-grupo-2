<script setup lang="ts">
/**
 * Vista Standalone: Turno (sin Tabla)
 *
 * Propósito: Parametrización y visualización de las jornadas horarias oficiales
 * (Matutino, Vespertino, Nocturno) y la estructura de bloques académicos en el SOGAC.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Clock,
    Sun,
    Sunset,
    Moon,
    ArrowLeft,
    Calendar,
    Layers,
    Info,
} from 'lucide-vue-next';

interface BloqueItem {
    numero: number;
    hora: string;
    tipo: string;
}

interface TurnoItem {
    codigo: string;
    nombre: string;
    hora_inicio: string;
    hora_fin: string;
    bloques: BloqueItem[];
    descripcion: string;
    color: string;
}

const props = defineProps<{
    turnos: TurnoItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Espacios y Tiempo', href: route('aula.index') },
        { title: 'Turno (sin Tabla)', href: route('turno.index') },
    ]">
        <Head title="Turnos y Bloques Horarios - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-6xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600">
                            <Clock class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Estructura de Turnos y Franjas Horarias
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Regulación horaria universitaria para la programación de secciones, docentes y aulas.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('horarios.index')">
                            <Calendar class="h-4 w-4 mr-2" />
                            Programación de Horarios
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- TARJETAS DE TURNOS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="turno in props.turnos"
                    :key="turno.codigo"
                    class="bg-card border rounded-2xl p-6 shadow-xs flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 rounded-xl" :class="{
                                'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400': turno.codigo === 'MAT',
                                'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400': turno.codigo === 'VES',
                                'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400': turno.codigo === 'NOC',
                            }">
                                <Sun v-if="turno.codigo === 'MAT'" class="h-6 w-6" />
                                <Sunset v-else-if="turno.codigo === 'VES'" class="h-6 w-6" />
                                <Moon v-else class="h-6 w-6" />
                            </div>

                            <Badge variant="outline" class="font-mono text-xs">
                                {{ turno.hora_inicio }} - {{ turno.hora_fin }}
                            </Badge>
                        </div>

                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
                            Turno {{ turno.nombre }}
                        </h2>
                        <p class="text-xs text-muted-foreground mb-4 leading-relaxed">
                            {{ turno.descripcion }}
                        </p>

                        <!-- BLOQUES HORARIOS INTERNOS -->
                        <div class="space-y-1.5 border-t pt-3">
                            <div class="text-xs font-semibold text-foreground mb-2">Bloques de Clase:</div>
                            <div
                                v-for="b in turno.bloques"
                                :key="b.numero"
                                class="flex items-center justify-between p-2 rounded bg-muted/40 text-xs font-mono"
                            >
                                <span class="font-semibold text-primary">B{{ b.numero }}</span>
                                <span class="text-slate-700 dark:text-slate-300">{{ b.hora }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN REGULATORIA -->
            <div class="p-4 rounded-xl bg-muted/40 border border-muted flex items-start gap-3 text-xs text-muted-foreground">
                <Info class="h-5 w-5 text-primary shrink-0 mt-0.5" />
                <div>
                    <strong class="font-semibold text-foreground">Regla Operativa Anti-Choque:</strong>
                    Toda sección creada adopta estrictamente la franja horaria de su turno asignado. Las horas teóricas y de laboratorio deben ser programadas respetando los límites de inicio y fin del turno para asegurar el descanso del docente y la sincronización con el transporte estudiantil.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

