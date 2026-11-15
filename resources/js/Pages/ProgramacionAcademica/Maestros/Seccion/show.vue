<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Sección, Asignación Docente y Horarios (Programación Académica).
 *
 * Propósito: Ficha técnica integral de la sección, administración de unidades curriculares,
 * vinculación de profesores por búsqueda, y programación de horarios con validación Anti-Choque.
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import { useFlashAlert } from '@/composables/useFlashAlert';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    ArrowLeft,
    Pencil,
    Users,
    CheckCircle2,
    XCircle,
    Building2,
    BookOpen,
    Calendar,
    Clock,
    Plus,
    Trash2,
    Search,
    AlertTriangle,
    DoorClosed,
    UserCheck,
    GraduationCap,
    Network,
} from 'lucide-vue-next';

interface AulaItem {
    id_aul: number;
    codigo_aula_aul: string;
    capacidad_maxima_aul: number;
    tipo_aula?: { id_tia: number; nombre_tia: string } | null;
}

interface MateriaMallaItem {
    id_mcd: number;
    unidades_credito_mcd: number;
    thte_mcd?: number;
    unidad_curricular?: { id_unc: number; codigo_unc: string; nombre_unc: string } | null;
    trayecto?: { id_tra: number; nombre_tra: string } | null;
}

interface HorarioItem {
    id_sho: number;
    dia_semana: number;
    hora_inicio: string;
    hora_fin: string;
    tipo_sesion: string;
    aula?: AulaItem | null;
}

interface UnidadDocenteItem {
    id_sud: number;
    tipo_contratacion: string;
    horas_semanales: number;
    docente?: { id: number; name: string; email: string } | null;
    malla_curricular_detalle?: MateriaMallaItem | null;
    horarios?: HorarioItem[];
}

interface SeccionItem {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    tipo_seccion?: string;
    trayecto_sec?: number;
    semestre_sec?: number;
    nivel_sec?: number;
    turno_sec: string;
    cupo_maximo_sec: number;
    status?: boolean;
    lapso_academico?: { id_la: number; nombre_la: string };
    pnf?: { id_pnf: number; nombre_pnf: string; codigo_pnf?: string };
    sede?: { id_sed: number; nombre_sed: string };
    cohorte_sede?: { id_cos: number; nombre_cos: string } | null;
    malla_curricular?: { id_mac: number; nombre_mac: string } | null;
    unidades_docentes?: UnidadDocenteItem[];
}

const props = defineProps<{
    seccion: SeccionItem;
    aulas: AulaItem[];
    materiasMalla: MateriaMallaItem[];
}>();

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Secciones', subtitle: 'Listado', href: route('seccion.index') },
    { title: `Ficha: ${props.seccion.nombre_sec}`, subtitle: 'Detalle', href: route('seccion.show', props.seccion.id_sec) },
];

const diasSemana = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

// Estado y formularios
const docenteSearchQuery = ref('');
const docenteResultados = ref<Array<{ id: number; name: string; email: string }>>([]);
const buscandoDocente = ref(false);

const formDocente = useForm({
    mcd_id: '',
    docente_id: '',
    tipo_contratacion: 'Titular',
    horas_semanales: 4,
});

const formHorario = useForm({
    sud_id: '',
    aul_id: '',
    dia_semana: 1,
    hora_inicio: '08:00',
    hora_fin: '09:30',
    tipo_sesion: 'Teorica',
});

// Búsqueda AJAX de docentes
const buscarDocentes = async () => {
    if (docenteSearchQuery.value.length < 2) {
        docenteResultados.value = [];
        return;
    }
    buscandoDocente.value = true;
    try {
        const res = await fetch(route('seccion.buscar_docente', { q: docenteSearchQuery.value }));
        docenteResultados.value = await res.json();
    } catch {
        docenteResultados.value = [];
    } finally {
        buscandoDocente.value = false;
    }
};

const seleccionarDocente = (doc: { id: number; name: string; email: string }) => {
    formDocente.docente_id = String(doc.id);
    docenteSearchQuery.value = `${doc.name} (${doc.email})`;
    docenteResultados.value = [];
};

// Acciones
const submitAsignarDocente = () => {
    formDocente.post(route('seccion.asignar_docente', props.seccion.id_sec), {
        preserveScroll: true,
        onSuccess: () => {
            formDocente.reset('mcd_id', 'docente_id');
            docenteSearchQuery.value = '';
        },
    });
};

const eliminarAsignacion = async (sudId: number) => {
    if (await confirmDialog('¿Está seguro de que desea desvincular esta materia de la sección?', 'Desvincular materia')) {
        router.delete(route('seccion.eliminar_asignacion_docente', sudId), {
            preserveScroll: true,
        });
    }
};

const submitAgregarHorario = () => {
    formHorario.post(route('seccion.agregar_horario', props.seccion.id_sec), {
        preserveScroll: true,
        onSuccess: () => {
            formHorario.reset('hora_inicio', 'hora_fin');
        },
    });
};

const eliminarHorarioBloque = async (shoId: number) => {
    if (await confirmDialog('¿Está seguro de eliminar este bloque horario?', 'Eliminar horario')) {
        router.delete(route('seccion.eliminar_horario', shoId), {
            preserveScroll: true,
        });
    }
};

// Matriz de horario agrupada por día
const bloquesPorDia = computed(() => {
    const mapa: Record<number, Array<{
        id: number;
        hora_inicio: string;
        hora_fin: string;
        materia: string;
        docente: string;
        aula: string;
        tipo_sesion: string;
    }>> = { 1: [], 2: [], 3: [], 4: [], 5: [], 6: [] };

    props.seccion.unidades_docentes?.forEach((ud) => {
        const mat = ud.malla_curricular_detalle?.unidad_curricular?.nombre_unc || 'Materia';
        const doc = ud.docente?.name || 'Por asignar';

        ud.horarios?.forEach((h) => {
            if (mapa[h.dia_semana]) {
                mapa[h.dia_semana].push({
                    id: h.id_sho,
                    hora_inicio: h.hora_inicio.substring(0, 5),
                    hora_fin: h.hora_fin.substring(0, 5),
                    materia: mat,
                    docente: doc,
                    aula: h.aula?.codigo_aula_aul || 'Aula N/A',
                    tipo_sesion: h.tipo_sesion,
                });
            }
        });
    });

    Object.keys(mapa).forEach((dia) => {
        mapa[Number(dia)].sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));
    });

    return mapa;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Sección: ${seccion.nombre_sec} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ALERTA FLASH -->
            <SuccessAlert v-if="showSuccessAlert" :message="message" @close="closeAlert" />

            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('seccion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Gestión de Sección y Horarios
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Configuración de la sección, asignación docente y motor de horarios anti-choque.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('seccion.edit', seccion.id_sec)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Sección</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHA TÉCNICA PRINCIPAL -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                                <Users class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ seccion.nombre_sec }}
                                </CardTitle>
                                <CardDescription>Código: {{ seccion.codigo_sec || `#${seccion.id_sec}` }}</CardDescription>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge variant="outline" class="font-medium">
                                {{ seccion.tipo_seccion || 'Regular' }}
                            </Badge>
                            <Badge :variant="seccion.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                                <CheckCircle2 v-if="seccion.status" class="h-3.5 w-3.5 text-emerald-500" />
                                <XCircle v-else class="h-3.5 w-3.5 text-slate-400" />
                                {{ seccion.status ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>

                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-xs">
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Lapso Académico</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm mt-0.5">
                                {{ seccion.lapso_academico?.nombre_la || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">PNF / Carrera</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm mt-0.5">
                                {{ seccion.pnf?.nombre_pnf || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Sede Física</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm mt-0.5">
                                {{ seccion.sede?.nombre_sed || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-muted-foreground uppercase font-medium">Turno y Cupo</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm mt-0.5">
                                {{ seccion.turno_sec }} (Máx. {{ seccion.cupo_maximo_sec }} est.)
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-muted-foreground uppercase font-medium">Cohorte por Sede</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">
                                {{ seccion.cohorte_sede?.nombre_cos || 'Cohorte Abierta / General' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-muted-foreground uppercase font-medium">Malla Curricular</span>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 mt-0.5">
                                {{ seccion.malla_curricular?.nombre_mac || 'Sin malla asignada' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- SECCIÓN 2: ASIGNACIÓN DOCENTE Y UNIDADES CURRICULARES -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Asignación Docente y Unidades Curriculares</CardTitle>
                    </div>
                    <CardDescription>
                        Vincule materias de la malla al grupo y asigne el profesor titular localizándolo por nombre o cédula.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- FORMULARIO DE ASIGNACIÓN (VISTA INTEGRADA) -->
                    <form @submit.prevent="submitAsignarDocente" class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Nueva Asignación</h4>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Unidad Curricular -->
                            <div class="space-y-1.5 md:col-span-2">
                                <Label for="mcd_id">Materia de la Malla *</Label>
                                <select
                                    id="mcd_id"
                                    v-model="formDocente.mcd_id"
                                    class="w-full h-9 px-3 py-1 rounded-md border text-xs bg-background"
                                    required
                                >
                                    <option value="" disabled>Seleccione materia...</option>
                                    <option v-for="mat in materiasMalla" :key="mat.id_mcd" :value="mat.id_mcd">
                                        {{ mat.unidad_curricular?.nombre_unc }} ({{ mat.unidad_curricular?.codigo_unc }}) - {{ mat.trayecto?.nombre_tra || 'T1' }}
                                    </option>
                                </select>
                            </div>

                            <!-- Búsqueda de Docente -->
                            <div class="space-y-1.5 relative md:col-span-2">
                                <Label for="docente_q">Buscar Docente (Cédula o Nombre) *</Label>
                                <div class="relative">
                                    <Input
                                        id="docente_q"
                                        v-model="docenteSearchQuery"
                                        placeholder="Escriba cédula o apellido..."
                                        class="h-9 text-xs pr-8"
                                        @input="buscarDocentes"
                                    />
                                    <Search class="h-4 w-4 absolute right-2.5 top-2.5 text-muted-foreground" />
                                </div>

                                <!-- Dropdown de Resultados -->
                                <div
                                    v-if="docenteResultados.length > 0"
                                    class="absolute z-20 w-full mt-1 bg-popover border rounded-md shadow-lg overflow-hidden text-xs"
                                >
                                    <button
                                        v-for="doc in docenteResultados"
                                        :key="doc.id"
                                        type="button"
                                        class="w-full px-3 py-2 text-left hover:bg-muted flex items-center justify-between"
                                        @click="seleccionarDocente(doc)"
                                    >
                                        <span class="font-medium text-foreground">{{ doc.name }}</span>
                                        <span class="text-muted-foreground">{{ doc.email }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" size="sm" :disabled="formDocente.processing || !formDocente.mcd_id || !formDocente.docente_id" class="gap-1.5">
                                <Plus class="h-3.5 w-3.5" /> Asignar a la Sección
                            </Button>
                        </div>
                    </form>

                    <!-- TABLA DE MATERIAS ASIGNADAS -->
                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-800 uppercase font-semibold text-slate-700 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-2.5">Código / Unidad Curricular</th>
                                    <th class="px-4 py-2.5">Profesor Titular</th>
                                    <th class="px-4 py-2.5 text-center">Horas Sem.</th>
                                    <th class="px-4 py-2.5 text-center">Bloques</th>
                                    <th class="px-4 py-2.5 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="ud in seccion.unidades_docentes || []" :key="ud.id_sud" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">
                                        <div>{{ ud.malla_curricular_detalle?.unidad_curricular?.nombre_unc || 'Materia' }}</div>
                                        <div class="text-[11px] font-mono text-muted-foreground">
                                            {{ ud.malla_curricular_detalle?.unidad_curricular?.codigo_unc }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                            <UserCheck class="h-3.5 w-3.5 text-primary" />
                                            <span>{{ ud.docente?.name || 'Sin docente' }}</span>
                                        </div>
                                        <div class="text-[11px] text-muted-foreground">{{ ud.docente?.email }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-semibold">
                                        {{ ud.horas_semanales }}h
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary font-mono text-[11px]">
                                            {{ ud.horarios?.length || 0 }} bloques
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-7 w-7 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400"
                                            title="Desvincular materia"
                                            @click="eliminarAsignacion(ud.id_sud)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="!seccion.unidades_docentes?.length">
                                    <td colspan="5" class="text-center py-6 text-muted-foreground">
                                        No hay materias ni profesores asignados aún a esta sección.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- SECCIÓN 3: MOTOR DE HORARIOS Y AULAS (ANTI-CHOQUE) -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Planificación de Horarios y Aulas (Motor Anti-Choque)</CardTitle>
                    </div>
                    <CardDescription>
                        Programe los bloques de clase semanales. El motor valida en tiempo real la disponibilidad del docente y del aula física.
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- FORMULARIO DE BLOQUE HORARIO -->
                    <form @submit.prevent="submitAgregarHorario" class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Agregar Bloque Semanal</h4>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                            <!-- Materia / Docente -->
                            <div class="space-y-1.5 md:col-span-2">
                                <Label for="sud_id">Materia Asignada *</Label>
                                <select
                                    id="sud_id"
                                    v-model="formHorario.sud_id"
                                    class="w-full h-9 px-3 py-1 rounded-md border text-xs bg-background"
                                    required
                                >
                                    <option value="" disabled>Seleccione materia...</option>
                                    <option v-for="ud in seccion.unidades_docentes || []" :key="ud.id_sud" :value="ud.id_sud">
                                        {{ ud.malla_curricular_detalle?.unidad_curricular?.nombre_unc }} (Prof. {{ ud.docente?.name }})
                                    </option>
                                </select>
                            </div>

                            <!-- Aula / Espacio Físico -->
                            <div class="space-y-1.5">
                                <Label for="aul_id">Aula o Laboratorio *</Label>
                                <select
                                    id="aul_id"
                                    v-model="formHorario.aul_id"
                                    class="w-full h-9 px-3 py-1 rounded-md border text-xs bg-background"
                                    required
                                >
                                    <option value="" disabled>Seleccione aula...</option>
                                    <option v-for="aula in aulas" :key="aula.id_aul" :value="aula.id_aul">
                                        {{ aula.codigo_aula_aul }} ({{ aula.tipo_aula?.nombre_tia || 'Aula' }})
                                    </option>
                                </select>
                            </div>

                            <!-- Día de la semana -->
                            <div class="space-y-1.5">
                                <Label for="dia_semana">Día *</Label>
                                <select
                                    id="dia_semana"
                                    v-model="formHorario.dia_semana"
                                    class="w-full h-9 px-3 py-1 rounded-md border text-xs bg-background"
                                    required
                                >
                                    <option v-for="dia in diasSemana" :key="dia.id" :value="dia.id">
                                        {{ dia.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Horas -->
                            <div class="space-y-1.5 grid grid-cols-2 gap-1.5">
                                <div>
                                    <Label for="hora_inicio">Inicio</Label>
                                    <Input id="hora_inicio" v-model="formHorario.hora_inicio" type="time" class="h-9 text-xs" required />
                                </div>
                                <div>
                                    <Label for="hora_fin">Fin</Label>
                                    <Input id="hora_fin" v-model="formHorario.hora_fin" type="time" class="h-9 text-xs" required />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" size="sm" :disabled="formHorario.processing || !formHorario.sud_id || !formHorario.aul_id" class="gap-1.5">
                                <Plus class="h-3.5 w-3.5" /> Programar con Anti-Choque
                            </Button>
                        </div>
                    </form>

                    <!-- CUADRÍCULA SEMANAL DE HORARIO DE LA SECCIÓN -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                Cuadrícula Semanal de la Sección
                            </h4>
                            <span class="text-xs text-muted-foreground">Distribución de clases de Lunes a Sábado</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div
                                v-for="dia in diasSemana"
                                :key="dia.id"
                                class="rounded-lg border bg-slate-50/60 dark:bg-slate-900/40 p-3 min-h-[140px] space-y-2"
                            >
                                <div class="border-b pb-1 text-center font-bold text-xs uppercase text-slate-700 dark:text-slate-300">
                                    {{ dia.nombre }}
                                </div>

                                <div v-if="bloquesPorDia[dia.id]?.length" class="space-y-2">
                                    <div
                                        v-for="b in bloquesPorDia[dia.id]"
                                        :key="b.id"
                                        class="bg-card border rounded-md p-2 shadow-xs space-y-1 text-xs relative group"
                                    >
                                        <div class="flex items-center justify-between font-mono font-bold text-primary text-[11px]">
                                            <span>{{ b.hora_inicio }} - {{ b.hora_fin }}</span>
                                            <button
                                                type="button"
                                                class="text-red-500 hover:text-red-700 opacity-60 hover:opacity-100"
                                                title="Eliminar bloque"
                                                @click="eliminarHorarioBloque(b.id)"
                                            >
                                                <XCircle class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                        <div class="font-medium text-slate-900 dark:text-slate-100 leading-tight">
                                            {{ b.materia }}
                                        </div>
                                        <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                            <DoorClosed class="h-3 w-3" /> {{ b.aula }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 dark:text-slate-400 truncate">
                                            {{ b.docente }}
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-6 text-[11px] text-muted-foreground italic">
                                    Sin clases
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
