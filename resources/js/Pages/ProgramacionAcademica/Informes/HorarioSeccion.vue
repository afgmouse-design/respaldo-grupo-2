<script setup lang="ts">
/**
 * Vista Standalone: Horario De Sección (Informes)
 *
 * Propósito: Visualización e impresión de la cuadrícula horaria semanal
 * integral de una sección académica particular.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Layers,
    Printer,
    Download,
    School,
    UserCheck,
    Clock,
} from 'lucide-vue-next';

interface SeccionItem {
    id_sec: number;
    nombre_sec: string;
    codigo_sec?: string;
    semestre_sec?: number;
    trayecto_sec?: number;
}

const props = defineProps<{
    secciones: SeccionItem[];
    seccionSeleccionada?: SeccionItem | null;
    horarios: any[];
}>();

const seccionId = ref(props.seccionSeleccionada?.id_sec ?? '');

const cambiarSeccion = () => {
    router.get(route('informes.horario_seccion'), { seccion_id: seccionId.value || undefined }, { preserveState: true });
};

const dias = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

const getHorariosPorDia = (diaId: number) => {
    return props.horarios.filter(h => h.dia_semana === diaId);
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Informes', href: route('informes.secciones') },
        { title: 'Horario De Sección', href: route('informes.horario_seccion') },
    ]">
        <Head :title="`Horario de Sección: ${props.seccionSeleccionada?.nombre_sec || 'Consulta'} - SOGAC`" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:hidden">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-600">
                            <Layers class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Horario Semanal por Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Cuadrícula completa de materias, aulas y profesores asignados a una sección.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="seccionId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary w-64"
                        @change="cambiarSeccion"
                    >
                        <option value="">-- Seleccionar Sección --</option>
                        <option v-for="sec in props.secciones" :key="sec.id_sec" :value="sec.id_sec">
                            {{ sec.nombre_sec }} ({{ sec.codigo_sec || 'S/C' }})
                        </option>
                    </select>

                    <a
                        :href="route('informes.horarios_semanal_turnos_pdf', seccionId ? { seccion_id: seccionId } : {})"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
                    >
                        <Download class="h-4 w-4 mr-2" />
                        PDF Vertical
                    </a>

                    <Button variant="outline" @click="imprimir">
                        <Printer class="h-4 w-4 mr-2" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <!-- FICHA DE LA SECCIÓN -->
            <div v-if="props.seccionSeleccionada" class="p-4 bg-card border rounded-xl shadow-xs">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <Layers class="h-5 w-5 text-primary" />
                    Sección: {{ props.seccionSeleccionada.nombre_sec }}
                    <Badge variant="outline" class="font-mono text-xs">
                        {{ props.seccionSeleccionada.codigo_sec }}
                    </Badge>
                    <span class="text-xs text-muted-foreground font-normal">
                        (Trayecto {{ props.seccionSeleccionada.trayecto_sec ?? 1 }} / Semestre {{ props.seccionSeleccionada.semestre_sec ?? 1 }})
                    </span>
                </h2>
            </div>

            <!-- MATRIZ SEMANAL -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div
                    v-for="dia in dias"
                    :key="dia.id"
                    class="bg-card border rounded-xl overflow-hidden shadow-xs flex flex-col"
                >
                    <div class="bg-muted/50 p-3 border-b text-center font-bold text-sm">
                        {{ dia.nombre }}
                    </div>

                    <div class="p-2 space-y-2 flex-1 min-h-[160px]">
                        <div
                            v-for="h in getHorariosPorDia(dia.id)"
                            :key="h.id_sho"
                            class="p-2.5 rounded-lg border bg-background text-xs space-y-1 border-l-4 border-l-emerald-600 shadow-2xs"
                        >
                            <div class="font-bold text-emerald-700 dark:text-emerald-400 font-mono text-[11px]">
                                {{ h.hora_inicio }} - {{ h.hora_fin }}
                            </div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                {{ h.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.nombre_ucu || 'Materia' }}
                            </div>
                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <UserCheck class="h-3 w-3" />
                                <span>Prof: {{ h.seccion_unidad_docente?.docente?.name || 'Por Asignar' }}</span>
                            </div>
                            <div class="text-[11px] font-semibold text-primary flex items-center gap-1">
                                <School class="h-3 w-3" />
                                <span>{{ h.aula?.nombre_aul || 'Aula' }}</span>
                            </div>
                        </div>

                        <div v-if="getHorariosPorDia(dia.id).length === 0" class="text-center py-6 text-muted-foreground text-xs italic">
                            Sin horas asignadas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

