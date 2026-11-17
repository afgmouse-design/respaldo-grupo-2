<script setup lang="ts">
/**
 * Vista Standalone: Consulta de Horario del Estudiante (Programación Académica).
 *
 * Propósito: Permite al estudiante o a Control de Estudios visualizar la cuadrícula
 * personalizada de clases mediante el código de la sección y semestre.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types/navigation';
import { coincideDiaHorario } from '@/lib/academico';
import {
    Calendar,
    Clock,
    Search,
    Printer,
    Download,
    DoorClosed,
    UserCheck,
    Users,
} from 'lucide-vue-next';

interface HorarioBloque {
    id: number;
    dia_semana: string | number;
    hora_inicio: string;
    hora_fin: string;
    tipo_sesion: string;
    materia: string;
    codigo_materia: string;
    docente: string;
    aula: string;
    tipo_aula: string;
}

interface SeccionData {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    tipo_seccion?: string | { nombre_tis?: string } | null;
    turno?: { nombre_tur?: string } | null;
    turno_sec?: string;
    semestre_sec?: number;
    trayecto_sec?: number;
    lapso_academico?: { id_laa?: number; id_la?: number; nombre_laa?: string; nombre_la?: string };
    programa?: { id_prg?: number; nombre_prg?: string; codigo_prg?: string };
    pnf?: { id_pnf?: number; nombre_pnf?: string; codigo_pnf?: string };
    sede?: { id_sed: number; nombre_sed: string };
    malla_curricular?: { id_mac: number; nombre_mac: string };
}

const props = defineProps<{
    seccion?: SeccionData | null;
    horario: HorarioBloque[];
    filtros: {
        codigo_sec: string;
        semestre?: number | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Oferta', href: '#' },
    { title: 'Consulta de Horarios', subtitle: 'Estudiante', href: route('seccion.horario_estudiante') },
];

const diasSemana = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

const searchCodigo = ref(props.filtros.codigo_sec || '');
const searchSemestre = ref(props.filtros.semestre ? String(props.filtros.semestre) : '');

const buscarHorario = () => {
    router.get(
        route('seccion.horario_estudiante'),
        {
            codigo_sec: searchCodigo.value,
            semestre: searchSemestre.value || undefined,
        },
        { preserveState: true }
    );
};

const imprimirHorario = () => {
    window.print();
};

const getBloquesPorDia = (diaId: number, diaNombre?: string) => {
    return props.horario
        .filter((h) => coincideDiaHorario(h, diaId, diaNombre))
        .sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Consulta de Horario Estudiantil - SOGAC" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800 print:hidden">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Consulta de Horario de Clases
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Visualización personalizada de la carga horaria semanal por sección y semestre.
                    </p>
                </div>

                <div v-if="seccion" class="flex items-center gap-2">
                    <a
                        :href="route('informes.horarios_semanal_turnos_pdf', { seccion_id: seccion.id_sec })"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 gap-1.5"
                    >
                        <Download class="h-4 w-4" />
                        <span>PDF Oficial UPTP</span>
                    </a>
                    <Button variant="outline" class="gap-2" @click="imprimirHorario">
                        <Printer class="h-4 w-4" />
                        <span>Imprimir Horario</span>
                    </Button>
                </div>
            </div>

            <!-- BARRA DE BÚSQUEDA Y CONSULTA -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs print:hidden">
                <CardContent class="p-4">
                    <form @submit.prevent="buscarHorario" class="flex flex-col sm:flex-row items-end gap-3">
                        <div class="space-y-1.5 flex-1">
                            <Label for="codigo_sec">Código o Nombre de Sección</Label>
                            <Input
                                id="codigo_sec"
                                v-model="searchCodigo"
                                placeholder="Ej: INFO-T1-S1-A"
                                required
                            />
                        </div>

                        <div class="space-y-1.5 w-full sm:w-44">
                            <Label for="semestre">Semestre</Label>
                            <select
                                id="semestre"
                                v-model="searchSemestre"
                                class="w-full h-10 px-3 py-2 rounded-md border text-sm bg-background"
                            >
                                <option value="">Cualquier semestre</option>
                                <option v-for="n in 8" :key="n" :value="n">Semestre {{ n }}</option>
                            </select>
                        </div>

                        <Button type="submit" class="gap-1.5 shrink-0">
                            <Search class="h-4 w-4" />
                            <span>Consultar Horario</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- FICHA DE LA SECCIÓN ENCONTRADA -->
            <div v-if="seccion" class="space-y-6">
                <!-- RESUMEN DE MATRÍCULA -->
                <div class="bg-card border rounded-xl p-5 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-3 dark:border-slate-800">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                    {{ seccion.nombre_sec }}
                                </h2>
                                <Badge variant="outline">
                                    {{ typeof seccion.tipo_seccion === 'object' && seccion.tipo_seccion !== null ? (seccion.tipo_seccion.nombre_tis ?? 'Regular') : (seccion.tipo_seccion || 'Regular') }}
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground font-mono">
                                Código Oficial: {{ seccion.codigo_sec || `#${seccion.id_sec}` }}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-xs uppercase font-medium text-muted-foreground">Lapso Lectivo</span>
                            <div class="text-sm font-bold text-primary">
                                {{ seccion.lapso_academico?.nombre_laa || seccion.lapso_academico?.nombre_la || '2026-I' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Programa Académico</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ seccion.programa?.nombre_prg || seccion.pnf?.nombre_pnf || 'No asignado' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Sede Física</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ seccion.sede?.nombre_sed }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Turno</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ seccion.turno?.nombre_tur || seccion.turno_sec }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Pénsum / Malla</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ seccion.malla_curricular?.nombre_mac || 'Oficial Vigente' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- CUADRÍCULA DE HORARIO SEMANAL -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <Clock class="h-5 w-5 text-primary" /> Distribución de Encuentros Académicos
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                        <div
                            v-for="dia in diasSemana"
                            :key="dia.id"
                            class="rounded-xl border bg-slate-50/70 dark:bg-slate-900/40 p-3 min-h-[160px] space-y-2.5"
                        >
                            <div class="border-b pb-1.5 text-center font-bold text-xs uppercase text-slate-700 dark:text-slate-300">
                                {{ dia.nombre }}
                            </div>

                            <div v-if="getBloquesPorDia(dia.id, dia.nombre).length" class="space-y-2.5">
                                <div
                                    v-for="b in getBloquesPorDia(dia.id, dia.nombre)"
                                    :key="b.id"
                                    class="bg-card border rounded-lg p-2.5 shadow-xs space-y-1.5 text-xs hover:border-primary/50 transition-colors"
                                >
                                    <!-- Horas -->
                                    <div class="flex items-center justify-between font-mono font-bold text-primary text-[11px] border-b pb-1">
                                        <span>{{ b.hora_inicio }} - {{ b.hora_fin }}</span>
                                        <Badge variant="secondary" class="text-[9px] px-1 py-0">
                                            {{ b.tipo_sesion }}
                                        </Badge>
                                    </div>

                                    <!-- Materia -->
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs leading-snug">
                                        {{ b.materia }}
                                    </div>

                                    <!-- Aula -->
                                    <div class="flex items-center gap-1.5 text-[11px] text-muted-foreground font-medium">
                                        <DoorClosed class="h-3.5 w-3.5 text-amber-500" />
                                        <span>{{ b.aula }} ({{ b.tipo_aula }})</span>
                                    </div>

                                    <!-- Docente -->
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-400">
                                        <UserCheck class="h-3.5 w-3.5 text-emerald-500" />
                                        <span class="truncate">{{ b.docente }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-10 text-xs text-muted-foreground italic">
                                Libre
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ESTADO INICIAL O SIN RESULTADOS -->
            <div
                v-else-if="filtros.codigo_sec"
                class="text-center py-12 px-4 border border-dashed rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50/50"
            >
                <Users class="h-10 w-10 text-muted-foreground mx-auto mb-3 opacity-50" />
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                    Sección no encontrada
                </h3>
                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                    No se encontró ninguna sección con el código "{{ filtros.codigo_sec }}". Verifique los datos o consulte con Control de Estudios.
                </p>
            </div>

            <div
                v-else
                class="text-center py-14 px-4 border border-dashed rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50/50"
            >
                <Search class="h-10 w-10 text-muted-foreground mx-auto mb-3 opacity-50" />
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                    Consulte el Horario de su Sección
                </h3>
                <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">
                    Introduzca el código o nombre de su sección en el formulario superior para ver la cuadrícula de clases programada.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

