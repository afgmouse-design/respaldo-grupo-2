<script setup lang="ts">
/**
 * Vista Standalone: Sección (SUD - Sección Unidad Curricular Docente)
 *
 * Propósito: Gestión y asignación de la carga académica docente por cada
 * unidad curricular dentro de las secciones activas del sistema SOGAC.
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Badge from '@/components/ui/badge/Badge.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import { useFlashAlert } from '@/composables/useFlashAlert';
import {
    Users,
    BookOpen,
    GraduationCap,
    Clock,
    Search,
    UserCheck,
    Trash2,
    Layers,
    Plus,
    CheckCircle2,
    Calendar,
    ArrowLeft,
    School,
    AlertTriangle,
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';

interface SeccionItem {
    id_sec: number;
    nombre_sec: string;
    codigo_sec?: string | null;
    tipo_seccion?: string | null;
    semestre_sec?: number | null;
    trayecto_sec?: number | null;
    turno_sec?: string | null;
    cupo_maximo_sec?: number | null;
    status: boolean;
    cohorte_sede?: {
        cohorte?: {
            nombre_coh?: string;
        };
    };
    malla_curricular?: {
        nombre_mac: string;
    };
}

interface DocenteItem {
    id: number;
    name: string;
    email: string;
}

interface AsignacionItem {
    id_sud: number;
    sec_id: number;
    mcd_id: number;
    docente_id: number;
    tipo_contratacion: string;
    horas_semanales: number;
    malla_curricular_detalle?: {
        id_mcd: number;
        unidad_curricular?: {
            nombre_ucu: string;
            codigo_ucu: string;
            horas_academicas_totales_ucu?: number;
            unidades_credito_ucu?: number;
        };
    };
    docente?: DocenteItem;
    horarios?: Array<{
        id_sho: number;
        dia_semana: number;
        hora_inicio: string;
        hora_fin: string;
        tipo_sesion: string;
        aula?: {
            nombre_aul: string;
        };
    }>;
}

const props = defineProps<{
    secciones: SeccionItem[];
    seccionSeleccionada?: any;
    asignaciones: AsignacionItem[];
    unidadesDisponibles: any[];
    aulas?: Array<{ id_aul: number; codigo_aula_aul: string; capacidad_maxima_aul?: number }>;
    filters: {
        search?: string;
        seccion_id?: string | number;
    };
}>();

const { showSuccessAlert, message, closeAlert } = useFlashAlert();

// Búsqueda de sección
const searchSeccion = ref(props.filters.search ?? '');
const seleccionarSeccion = (id: number) => {
    router.get(route('seccion_unidad_docente.index'), {
        seccion_id: id,
        search: searchSeccion.value || undefined,
    }, { preserveState: true });
};

// Formulario de asignación docente
const mcdIdSeleccionada = ref<number | ''>('');
const docenteCedulaQuery = ref('');
const buscandoDocente = ref(false);
const docentesEncontrados = ref<DocenteItem[]>([]);
const docenteSeleccionado = ref<DocenteItem | null>(null);
const tipoContratacion = ref('Titular');
const horasSemanales = ref(4);
const guardandoAsignacion = ref(false);

const buscarDocentes = async () => {
    if (docenteCedulaQuery.value.trim().length < 2) {
        docentesEncontrados.value = [];
        return;
    }
    buscandoDocente.value = true;
    try {
        const res = await fetch(route('seccion.buscar_docente', { q: docenteCedulaQuery.value }));
        docentesEncontrados.value = await res.json();
    } catch {
        docentesEncontrados.value = [];
    } finally {
        buscandoDocente.value = false;
    }
};

const elegirDocente = (docente: DocenteItem) => {
    docenteSeleccionado.value = docente;
    docentesEncontrados.value = [];
};

const asignarDocenteAUnidad = () => {
    if (!props.seccionSeleccionada?.id_sec || !mcdIdSeleccionada.value || !docenteSeleccionado.value) {
        return;
    }
    guardandoAsignacion.value = true;
    router.post(route('seccion.asignar_docente', props.seccionSeleccionada.id_sec), {
        mcd_id: mcdIdSeleccionada.value,
        docente_id: docenteSeleccionado.value.id,
        tipo_contratacion: tipoContratacion.value,
        horas_semanales: horasSemanales.value,
    }, {
        onSuccess: () => {
            mcdIdSeleccionada.value = '';
            docenteSeleccionado.value = null;
            docenteCedulaQuery.value = '';
        },
        onFinish: () => {
            guardandoAsignacion.value = false;
        },
    });
};

const desvincularDocente = (idSud: number) => {
    if (confirm('¿Está seguro de desvincular al docente de esta unidad curricular?')) {
        router.delete(route('seccion.eliminar_asignacion_docente', idSud));
    }
};

// ==========================================
// MODAL Y GESTIÓN DE HORARIOS (ANTI-CHOQUE)
// ==========================================
const isHorarioModalOpen = ref(false);
const asignacionParaHorario = ref<AsignacionItem | null>(null);
const formHorario = ref({
    aul_id: '',
    dia_semana: 1,
    hora_inicio: '07:30',
    hora_fin: '09:00',
    tipo_sesion: 'Teorica',
});
const guardandoHorario = ref(false);
const errorHorario = ref('');

const abrirModalHorario = (asig: AsignacionItem) => {
    if (!asig.docente_id) {
        alert('Regla Obligatoria: Asigne primero al docente por cédula antes de fijar el horario.');
        return;
    }
    asignacionParaHorario.value = asig;
    formHorario.value.aul_id = props.aulas && props.aulas.length > 0 ? String(props.aulas[0].id_aul) : '';
    errorHorario.value = '';
    isHorarioModalOpen.value = true;
};

const guardarHorario = () => {
    if (!asignacionParaHorario.value || !props.seccionSeleccionada?.id_sec) return;
    if (!formHorario.value.aul_id) {
        errorHorario.value = 'Debe seleccionar un aula o ambiente físico.';
        return;
    }
    guardandoHorario.value = true;
    errorHorario.value = '';

    router.post(route('seccion.agregar_horario', props.seccionSeleccionada.id_sec), {
        sud_id: asignacionParaHorario.value.id_sud,
        aul_id: Number(formHorario.value.aul_id),
        dia_semana: Number(formHorario.value.dia_semana),
        hora_inicio: formHorario.value.hora_inicio,
        hora_fin: formHorario.value.hora_fin,
        tipo_sesion: formHorario.value.tipo_sesion,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isHorarioModalOpen.value = false;
        },
        onError: (errs) => {
            errorHorario.value = Object.values(errs).join(' | ') || 'Conflicto de horario detectado por el motor anti-choque.';
        },
        onFinish: () => {
            guardandoHorario.value = false;
        },
    });
};

const eliminarBloqueHorario = (idSho: number) => {
    if (confirm('¿Está seguro de eliminar este bloque de clase?')) {
        router.delete(route('seccion.eliminar_horario', idSho), {
            preserveScroll: true,
        });
    }
};

const diasSemana = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Sección (SUD)', href: route('seccion_unidad_docente.index') },
    ]">
        <Head title="Sección (SUD) - Asignación Docente" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <SuccessAlert v-if="showSuccessAlert" :message="message" @close="closeAlert" />

            <!-- CABECERA PRINCIPAL -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <UserCheck class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Sección (SUD): Asignación Docente y Unidades
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Vinculación oficial de profesores por cédula a las asignaturas de cada sección académica.
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

            <!-- LAYOUT EN 2 COLUMNAS: SELECTOR DE SECCIONES + PANEL SUD -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- COLUMNA IZQUIERDA: LISTA DE SECCIONES -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-card border rounded-xl p-4 shadow-xs">
                        <h2 class="text-base font-semibold flex items-center gap-2 mb-3">
                            <Layers class="h-4 w-4 text-primary" />
                            Seleccionar Sección Activa
                        </h2>

                        <div class="relative mb-3">
                            <Search class="h-4 w-4 absolute left-3 top-3 text-muted-foreground" />
                            <Input
                                v-model="searchSeccion"
                                placeholder="Filtrar por código o nombre..."
                                class="pl-9"
                                @input="seleccionarSeccion(props.seccionSeleccionada?.id_sec ?? props.secciones[0]?.id_sec)"
                            />
                        </div>

                        <div class="space-y-2 max-h-[550px] overflow-y-auto pr-1">
                            <div
                                v-for="sec in props.secciones"
                                :key="sec.id_sec"
                                class="p-3 rounded-lg border text-sm cursor-pointer transition-all"
                                :class="props.seccionSeleccionada?.id_sec === sec.id_sec
                                    ? 'bg-primary/10 border-primary shadow-xs'
                                    : 'bg-background hover:bg-muted/50'"
                                @click="seleccionarSeccion(sec.id_sec)"
                            >
                                <div class="flex items-center justify-between font-medium">
                                    <span class="text-foreground">{{ sec.nombre_sec }}</span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ sec.codigo_sec || 'S/C' }}
                                    </Badge>
                                </div>
                                <div class="text-xs text-muted-foreground mt-1 flex items-center gap-2">
                                    <span>T{{ sec.trayecto_sec ?? 1 }} / S{{ sec.semestre_sec ?? 1 }}</span>
                                    <span>•</span>
                                    <span>Turno {{ sec.turno_sec ?? 'Matutino' }}</span>
                                </div>
                            </div>

                            <div v-if="props.secciones.length === 0" class="text-center py-6 text-muted-foreground text-xs">
                                No se encontraron secciones activas.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ASIGNACIÓN Y RESUMEN SUD -->
                <div class="lg:col-span-8 space-y-6">
                    <div v-if="props.seccionSeleccionada" class="space-y-6">
                        <!-- TARJETA INFORMATIVA DE LA SECCIÓN SELECCIONADA -->
                        <div class="bg-card border rounded-xl p-5 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-3 mb-4">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        {{ props.seccionSeleccionada.nombre_sec }}
                                        <Badge variant="secondary" class="font-mono text-xs">
                                            {{ props.seccionSeleccionada.codigo_sec }}
                                        </Badge>
                                    </h2>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        Malla Curricular: {{ props.seccionSeleccionada.malla_curricular?.nombre_mac || 'Sin Malla' }}
                                    </p>
                                </div>
                                <Badge variant="outline" class="self-start sm:self-auto">
                                    Capacidad: {{ props.seccionSeleccionada.cupo_maximo_sec || 35 }} estudiantes
                                </Badge>
                            </div>

                            <!-- FORMULARIO DE ASIGNACIÓN DOCENTE -->
                            <div class="bg-muted/40 p-4 rounded-lg border space-y-4">
                                <h3 class="text-sm font-semibold flex items-center gap-2">
                                    <Plus class="h-4 w-4 text-primary" />
                                    Vincular Docente a Unidad Curricular
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <Label class="text-xs">Unidad Curricular *</Label>
                                        <select
                                            v-model="mcdIdSeleccionada"
                                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="">-- Seleccionar Asignatura --</option>
                                            <option
                                                v-for="det in props.unidadesDisponibles"
                                                :key="det.id_mcd"
                                                :value="det.id_mcd"
                                            >
                                                {{ det.unidad_curricular?.nombre_ucu || 'Materia #' + det.id_mcd }} ({{ det.unidad_curricular?.codigo_ucu || 'S/C' }})
                                            </option>
                                        </select>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label class="text-xs">Buscar Profesor (Cédula / Nombre) *</Label>
                                        <div class="relative">
                                            <Input
                                                v-model="docenteCedulaQuery"
                                                placeholder="Escribe cédula o nombre..."
                                                class="h-9"
                                                @input="buscarDocentes"
                                            />
                                        </div>
                                        <!-- Resultados de búsqueda -->
                                        <div
                                            v-if="docentesEncontrados.length > 0"
                                            class="border rounded-md bg-popover shadow-md divide-y text-xs max-h-36 overflow-y-auto mt-1"
                                        >
                                            <div
                                                v-for="doc in docentesEncontrados"
                                                :key="doc.id"
                                                class="p-2 hover:bg-muted cursor-pointer flex items-center justify-between"
                                                @click="elegirDocente(doc)"
                                            >
                                                <span class="font-medium text-foreground">{{ doc.name }}</span>
                                                <span class="text-muted-foreground">{{ doc.email }}</span>
                                            </div>
                                        </div>

                                        <div v-if="docenteSeleccionado" class="mt-1.5 p-2 bg-emerald-500/10 border border-emerald-500/20 rounded text-xs flex items-center justify-between text-emerald-800 dark:text-emerald-300">
                                            <span><strong>Profesor:</strong> {{ docenteSeleccionado.name }}</span>
                                            <Button size="sm" variant="ghost" class="h-5 px-1 text-xs" @click="docenteSeleccionado = null">Cambiar</Button>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label class="text-xs">Tipo de Contratación</Label>
                                        <select
                                            v-model="tipoContratacion"
                                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                        >
                                            <option value="Titular">Docente Titular</option>
                                            <option value="Contratado">Contratado</option>
                                            <option value="Suplente">Suplente</option>
                                            <option value="Tiempo Completo (40h)">Tiempo Completo (40h)</option>
                                            <option value="Medio Tiempo (20h)">Medio Tiempo (20h)</option>
                                        </select>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label class="text-xs">Horas Semanales</Label>
                                        <Input v-model="horasSemanales" type="number" min="1" max="20" class="h-9" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <Button
                                        size="sm"
                                        :disabled="guardandoAsignacion || !mcdIdSeleccionada || !docenteSeleccionado"
                                        @click="asignarDocenteAUnidad"
                                    >
                                        <CheckCircle2 class="h-4 w-4 mr-1.5" />
                                        Guardar Asignación SUD
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- LISTADO DE ASIGNACIONES ACTIVAS -->
                        <div class="bg-card border rounded-xl p-5 shadow-xs">
                            <h3 class="text-base font-semibold flex items-center gap-2 mb-4">
                                <BookOpen class="h-4 w-4 text-primary" />
                                Carga Asignada en la Sección ({{ props.asignaciones.length }} Materias)
                            </h3>

                            <div v-if="props.asignaciones.length > 0" class="divide-y border rounded-lg overflow-hidden">
                                <div
                                    v-for="asig in props.asignaciones"
                                    :key="asig.id_sud"
                                    class="p-4 bg-background flex flex-col md:flex-row md:items-center justify-between gap-3"
                                >
                                    <div>
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                            {{ asig.malla_curricular_detalle?.unidad_curricular?.nombre_ucu || 'Unidad Curricular' }}
                                            <Badge variant="outline" class="text-xs">
                                                {{ asig.malla_curricular_detalle?.unidad_curricular?.codigo_ucu || 'S/C' }}
                                            </Badge>
                                        </div>
                                        <div class="text-xs text-muted-foreground mt-1 flex flex-wrap items-center gap-3">
                                            <span><strong>Docente:</strong> {{ asig.docente?.name || 'Por Asignar' }}</span>
                                            <span>•</span>
                                            <span><strong>Dedicación:</strong> {{ asig.tipo_contratacion }}</span>
                                            <span>•</span>
                                            <span>{{ asig.horas_semanales }} horas/sem</span>
                                        </div>
                                        <!-- Horarios asociados -->
                                        <div v-if="asig.horarios && asig.horarios.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                                            <span
                                                v-for="h in asig.horarios"
                                                :key="h.id_sho"
                                                class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] bg-primary/10 text-primary font-medium"
                                            >
                                                <span>{{ diasSemana[h.dia_semana] }} {{ h.hora_inicio }} - {{ h.hora_fin }} ({{ h.aula?.nombre_aul || 'Aula' }})</span>
                                                <button
                                                    type="button"
                                                    class="text-primary hover:text-red-600 font-bold ml-1 text-xs"
                                                    title="Eliminar bloque horario"
                                                    @click="eliminarBloqueHorario(h.id_sho)"
                                                >
                                                    &times;
                                                </button>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-end md:self-center">
                                        <Button
                                            v-if="asig.docente_id"
                                            size="sm"
                                            variant="outline"
                                            class="h-8 gap-1.5 text-xs text-primary border-primary/30 hover:bg-primary/10"
                                            @click="abrirModalHorario(asig)"
                                        >
                                            <Clock class="h-3.5 w-3.5" />
                                            + Asignar Horario
                                        </Button>
                                        <Badge v-else variant="destructive" class="text-[10px]">
                                            Requiere Docente
                                        </Badge>

                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0"
                                            title="Desvincular materia y docente"
                                            @click="desvincularDocente(asig.id_sud)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8 text-muted-foreground text-sm border border-dashed rounded-lg">
                                Esta sección aún no tiene docentes ni unidades curriculares vinculadas.
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-12 text-center border border-dashed rounded-xl bg-card text-muted-foreground">
                        Seleccione una sección de la lista izquierda para gestionar su asignación docente (SUD).
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL PARA AGREGAR BLOQUE HORARIO (CON MOTOR ANTI-CHOQUE) -->
        <Dialog :open="isHorarioModalOpen" @update:open="isHorarioModalOpen = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        Asignar Bloque Horario
                    </DialogTitle>
                    <DialogDescription>
                        {{ asignacionParaHorario?.malla_curricular_detalle?.unidad_curricular?.nombre_ucu }}
                        — Docente: <strong>{{ asignacionParaHorario?.docente?.name }}</strong>
                    </DialogDescription>
                </DialogHeader>

                <div v-if="errorHorario" class="p-3 bg-destructive/10 text-destructive text-xs rounded-md border border-destructive/20 font-medium">
                    {{ errorHorario }}
                </div>

                <div class="space-y-4 py-2">
                    <div class="space-y-1.5">
                        <Label class="text-xs">Espacio Físico (Aula / Laboratorio) *</Label>
                        <select
                            v-model="formHorario.aul_id"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="" disabled>-- Seleccionar Aula --</option>
                            <option v-for="a in props.aulas" :key="a.id_aul" :value="a.id_aul">
                                {{ a.codigo_aula_aul }} (Capacidad: {{ a.capacidad_maxima_aul || 'N/A' }})
                            </option>
                        </select>
                        <p v-if="!props.aulas || props.aulas.length === 0" class="text-xs text-amber-600">
                            No hay aulas registradas en el módulo Aulas.
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs">Día de la Semana *</Label>
                        <select
                            v-model="formHorario.dia_semana"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option :value="1">Lunes</option>
                            <option :value="2">Martes</option>
                            <option :value="3">Miércoles</option>
                            <option :value="4">Jueves</option>
                            <option :value="5">Viernes</option>
                            <option :value="6">Sábado</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label class="text-xs">Hora Inicio *</Label>
                            <Input v-model="formHorario.hora_inicio" type="time" class="h-9" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs">Hora Fin *</Label>
                            <Input v-model="formHorario.hora_fin" type="time" class="h-9" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs">Tipo de Sesión</Label>
                        <select
                            v-model="formHorario.tipo_sesion"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="Teorica">Teórica</option>
                            <option value="Practica/Lab">Práctica / Laboratorio</option>
                        </select>
                    </div>
                </div>

                <DialogFooter class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="guardandoHorario" @click="isHorarioModalOpen = false">
                        Cancelar
                    </Button>
                    <Button size="sm" :disabled="guardandoHorario || !formHorario.aul_id" @click="guardarHorario">
                        {{ guardandoHorario ? 'Validando Anti-Choque...' : 'Guardar Bloque' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

