<script setup lang="ts">
/**
 * Vista Standalone: Horario De Docente (Informes)
 *
 * Propósito: Visualización e impresión de la carga horaria semanal de un profesor (`persona_perfil.id_pep`).
 *
 * Historial:
 *  - 2026-11-15: Alineado a `pep_id` (`persona_perfil.id_pep`), `horario(id_hor)` y `carga_academica`.
 */
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    UserCheck,
    Printer,
    Layers,
    School,
} from 'lucide-vue-next';
import {
    coincideDiaHorario,
    formatearHoraCorta,
    obtenerAulaEtiqueta,
    obtenerCorreoDocente,
    obtenerIdDocente,
    obtenerNombreDocente,
    obtenerNombreMateriaHorario,
    obtenerSeccionHorario,
    type DocenteSelectOption,
} from '@/lib/academico';

const props = defineProps<{
    docentes: DocenteSelectOption[];
    docenteSeleccionado?: DocenteSelectOption | null;
    horarios: any[];
}>();

const docenteId = ref(obtenerIdDocente(props.docenteSeleccionado) || '');

const cambiarDocente = () => {
    router.get(route('informes.horario_docente'), { pep_id: docenteId.value || undefined }, { preserveState: true });
};

const dias = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

const getHorariosPorDia = (diaId: number, diaNombre: string) => {
    return props.horarios.filter((h) => coincideDiaHorario(h, diaId, diaNombre));
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', subtitle: 'Secciones', href: route('seccion.index') },
        { title: 'Informes', subtitle: 'Programación', href: route('informes.secciones') },
        { title: 'Horario De Docente', subtitle: 'Cuadrícula', href: route('informes.horario_docente') },
    ]">
        <Head :title="`Horario Docente: ${obtenerNombreDocente(props.docenteSeleccionado, 'Consulta')} - SOGAC`" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:hidden">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-500/10 rounded-lg text-blue-600">
                            <UserCheck class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Horario Semanal por Docente
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Carga horaria individual, asignación de materias y espacios para el profesorado universitario.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="docenteId"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary w-64"
                        @change="cambiarDocente"
                    >
                        <option value="">-- Seleccionar Docente --</option>
                        <option v-for="doc in props.docentes" :key="obtenerIdDocente(doc)" :value="obtenerIdDocente(doc)">
                            {{ obtenerNombreDocente(doc) }}{{ doc.cedula_per ? ` (${doc.cedula_per})` : '' }}
                        </option>
                    </select>

                    <Button variant="outline" @click="imprimir">
                        <Printer class="h-4 w-4 mr-2" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <div v-if="props.docenteSeleccionado" class="p-4 bg-card border rounded-xl shadow-xs">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <UserCheck class="h-5 w-5 text-primary" />
                    Prof. {{ obtenerNombreDocente(props.docenteSeleccionado) }}
                    <span v-if="props.docenteSeleccionado.cedula_per || obtenerCorreoDocente(props.docenteSeleccionado)" class="text-xs text-muted-foreground font-normal">
                        ({{ props.docenteSeleccionado.cedula_per ? `C.I. ${props.docenteSeleccionado.cedula_per}` : obtenerCorreoDocente(props.docenteSeleccionado) }})
                    </span>
                </h2>
            </div>

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
                            v-for="h in getHorariosPorDia(dia.id, dia.nombre)"
                            :key="h.id_hor ?? h.id_sho"
                            class="p-2.5 rounded-lg border bg-background text-xs space-y-1 border-l-4 border-l-blue-600 shadow-2xs"
                        >
                            <div class="font-bold text-blue-700 dark:text-blue-400 font-mono text-[11px]">
                                {{ formatearHoraCorta(h.hora_inicio_hor ?? h.hora_inicio) }} - {{ formatearHoraCorta(h.hora_fin_hor ?? h.hora_fin) }}
                            </div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                {{ obtenerNombreMateriaHorario(h) }}
                            </div>
                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <Layers class="h-3 w-3" />
                                <span>Sec: {{ obtenerSeccionHorario(h) }}</span>
                            </div>
                            <div class="text-[11px] font-semibold text-primary flex items-center gap-1">
                                <School class="h-3 w-3" />
                                <span>{{ obtenerAulaEtiqueta(h.aula) }}</span>
                            </div>
                        </div>

                        <div v-if="getHorariosPorDia(dia.id, dia.nombre).length === 0" class="text-center py-6 text-muted-foreground text-xs italic">
                            Sin horas asignadas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
