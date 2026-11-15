<script setup lang="ts">
/**
 * Vista Standalone: Horario De Aula (Informes)
 *
 * Propósito: Visualización e impresión de la cuadrícula horaria semanal
 * de ocupación de un aula o laboratorio determinado.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    School,
    Printer,
    Layers,
    UserCheck,
    Clock,
} from 'lucide-vue-next';

interface AulaItem {
    id_aul: number;
    nombre_aul: string;
    capacidad_aul?: number;
}

const props = defineProps<{
    aulas: AulaItem[];
    aulaSeleccionada?: AulaItem | null;
    horarios: any[];
}>();

const aulaId = ref(props.aulaSeleccionada?.id_aul ?? '');

const cambiarAula = () => {
    router.get(route('informes.horario_aula'), { aula_id: aulaId.value || undefined }, { preserveState: true });
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
        { title: 'Horario De Aula', href: route('informes.horario_aula') },
    ]">
        <Head :title="`Horario de Aula: ${props.aulaSeleccionada?.nombre_aul || 'Consulta'} - SOGAC`" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:hidden">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-600">
                            <School class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Horario de Ocupación por Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Consulta de disponibilidad física de aulas y laboratorios por día y bloque horario.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="aulaId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary w-64"
                        @change="cambiarAula"
                    >
                        <option value="">-- Seleccionar Aula --</option>
                        <option v-for="aul in props.aulas" :key="aul.id_aul" :value="aul.id_aul">
                            {{ aul.nombre_aul }} (Cap: {{ aul.capacidad_aul || 30 }})
                        </option>
                    </select>

                    <Button variant="outline" @click="imprimir">
                        <Printer class="h-4 w-4 mr-2" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <!-- TÍTULO DE IMPRESIÓN -->
            <div v-if="props.aulaSeleccionada" class="p-4 bg-card border rounded-xl shadow-xs">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <School class="h-5 w-5 text-primary" />
                    {{ props.aulaSeleccionada.nombre_aul }}
                    <Badge variant="outline" class="text-xs">
                        Capacidad: {{ props.aulaSeleccionada.capacidad_aul || 30 }} puestos
                    </Badge>
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
                            class="p-2.5 rounded-lg border bg-background text-xs space-y-1 border-l-4 border-l-indigo-600 shadow-2xs"
                        >
                            <div class="font-bold text-indigo-700 dark:text-indigo-400 font-mono text-[11px]">
                                {{ h.hora_inicio }} - {{ h.hora_fin }}
                            </div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                {{ h.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.nombre_ucu || 'Materia' }}
                            </div>
                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <Layers class="h-3 w-3" />
                                <span>Sec: {{ h.seccion_unidad_docente?.seccion?.nombre_sec || 'N/A' }}</span>
                            </div>
                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <UserCheck class="h-3 w-3" />
                                <span>Prof: {{ h.seccion_unidad_docente?.docente?.name || 'Por Asignar' }}</span>
                            </div>
                        </div>

                        <div v-if="getHorariosPorDia(dia.id).length === 0" class="text-center py-6 text-muted-foreground text-xs italic">
                            Libre / Sin clase
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

