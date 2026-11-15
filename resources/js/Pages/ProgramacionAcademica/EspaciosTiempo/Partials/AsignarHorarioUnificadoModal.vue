<script setup lang="ts">
/**
 * Componente: Modal Unificado de Asignación Directa de Horario.
 * Propósito: Registro conjunto de Sección + Materia + Docente (C.I.) + Aula + Bloque Temporal,
 * evitando estados huérfanos ("N/A") y validando con el Motor Anti-Choque en tiempo real.
 */
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Calendar, UserCheck, School, Clock, Layers, Search, AlertCircle, ShieldAlert } from 'lucide-vue-next';

interface SeccionItem {
    id_sec: number;
    nombre_sec: string;
    codigo_sec?: string;
    malla_curricular?: {
        detalles?: Array<{
            id_mcd: number;
            trayecto_mcd?: number;
            unidad_curricular: {
                id_unc: number;
                nombre_unc: string;
                codigo_unc: string;
            };
        }>;
    };
}

interface DocenteItem {
    id: number;
    name: string;
    email: string;
    cedula?: string;
}

interface AulaItem {
    id_aul: number;
    nombre_aul: string;
    codigo_aula_aul: string;
}

const props = defineProps<{
    isOpen: boolean;
    secciones: SeccionItem[];
    docentes: DocenteItem[];
    aulas: AulaItem[];
}>();

const emit = defineEmits<{
    (e: 'update:isOpen', value: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    sec_id: '',
    mcd_id: '',
    docente_id: '',
    aul_id: '',
    dia_semana: 1,
    hora_inicio: '07:30',
    hora_fin: '09:00',
    tipo_sesion: 'Teoría',
});

// Búsqueda interactiva de Docente por Cédula, Nombre o Correo
const searchDocente = ref('');
const docentesFiltrados = computed(() => {
    if (!searchDocente.value.trim()) return props.docentes.slice(0, 10);
    const q = searchDocente.value.toLowerCase();
    return props.docentes.filter(d =>
        d.name.toLowerCase().includes(q) ||
        (d.cedula && d.cedula.toLowerCase().includes(q)) ||
        d.email.toLowerCase().includes(q)
    );
});

// Unidades curriculares asociadas a la malla de la sección seleccionada
const materiasDisponibles = computed(() => {
    const sec = props.secciones.find(s => String(s.id_sec) === String(form.sec_id));
    return sec?.malla_curricular?.detalles || [];
});

watch(() => form.sec_id, () => {
    form.mcd_id = '';
});

const closeModal = () => {
    emit('update:isOpen', false);
    form.reset();
    form.clearErrors();
};

const guardarHorario = () => {
    form.post(route('horarios.asignar_directo'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            emit('success');
        },
    });
};

const dias = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

const bloquesPreestablecidos = [
    { label: '07:30 - 09:00 (Matutino B1-B2)', inicio: '07:30', fin: '09:00' },
    { label: '09:20 - 10:50 (Matutino B3-B4)', inicio: '09:20', fin: '10:50' },
    { label: '10:50 - 12:20 (Matutino B5-B6)', inicio: '10:50', fin: '12:20' },
    { label: '01:30 - 03:00 (Vespertino B1-B2)', inicio: '13:30', fin: '15:00' },
    { label: '03:00 - 04:30 (Vespertino B3-B4)', inicio: '15:00', fin: '16:30' },
    { label: '04:30 - 06:00 (Vespertino B5-B6)', inicio: '16:30', fin: '18:00' },
    { label: '06:00 - 07:20 (Nocturno B1-B2)', inicio: '18:00', fin: '19:20' },
    { label: '07:20 - 08:40 (Nocturno B3-B4)', inicio: '19:20', fin: '20:40' },
    { label: '08:40 - 10:00 (Nocturno B5-B6)', inicio: '20:40', fin: '22:00' },
];

const aplicarBloquePreset = (preset: { inicio: string; fin: string }) => {
    form.hora_inicio = preset.inicio;
    form.hora_fin = preset.fin;
};
</script>

<template>
    <Dialog :open="props.isOpen" @update:open="emit('update:isOpen', $event)">
        <DialogContent class="sm:max-w-[720px] max-h-[92vh] overflow-y-auto">
            <DialogHeader>
                <div class="flex items-center gap-2 text-primary font-bold">
                    <Calendar class="h-5 w-5" />
                    <DialogTitle>Asignación Directa de Horario por Bloque</DialogTitle>
                </div>
                <DialogDescription>
                    Formulario unificado SOGAC: Registra en un solo paso la Sección, Materia, Docente titular, Aula y Horario para prevenir registros huérfanos.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="guardarHorario" class="space-y-4 py-1">
                <!-- 1. SELECCIÓN DE SECCIÓN Y UNIDAD CURRICULAR -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 bg-muted/40 rounded-lg border">
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold flex items-center gap-1.5">
                            <Layers class="h-3.5 w-3.5 text-primary" />
                            Sección Académica *
                        </Label>
                        <select
                            v-model="form.sec_id"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-xs focus:ring-2 focus:ring-primary"
                            required
                        >
                            <option value="" disabled>Seleccione sección...</option>
                            <option v-for="sec in props.secciones" :key="sec.id_sec" :value="sec.id_sec">
                                {{ sec.nombre_sec }} ({{ sec.codigo_sec || 'S/C' }})
                            </option>
                        </select>
                        <span v-if="form.errors.sec_id" class="text-[11px] text-destructive">
                            {{ form.errors.sec_id }}
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold flex items-center gap-1.5">
                            <School class="h-3.5 w-3.5 text-primary" />
                            Unidad Curricular (Malla) *
                        </Label>
                        <select
                            v-model="form.mcd_id"
                            :disabled="!form.sec_id"
                            class="w-full h-9 rounded-md border border-input bg-background px-3 text-xs focus:ring-2 focus:ring-primary disabled:opacity-50"
                            required
                        >
                            <option value="" disabled>
                                {{ form.sec_id ? 'Seleccione unidad curricular...' : 'Primero seleccione sección' }}
                            </option>
                            <option v-for="m in materiasDisponibles" :key="m.id_mcd" :value="m.id_mcd">
                                {{ m.unidad_curricular.codigo_unc }} - {{ m.unidad_curricular.nombre_unc }}
                            </option>
                        </select>
                        <span v-if="form.errors.mcd_id" class="text-[11px] text-destructive">
                            {{ form.errors.mcd_id }}
                        </span>
                    </div>
                </div>

                <!-- 2. DOCENTE RESPONSABLE (BÚSQUEDA POR CÉDULA/NOMBRE) -->
                <div class="p-3 bg-muted/40 rounded-lg border space-y-2">
                    <Label class="text-xs font-semibold flex items-center gap-1.5">
                        <UserCheck class="h-3.5 w-3.5 text-primary" />
                        Profesor Responsable * <span class="text-muted-foreground text-[10px]">(Filtro por C.I. o Nombre)</span>
                    </Label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <Search class="h-3.5 w-3.5 absolute left-2.5 top-2.5 text-muted-foreground" />
                            <input
                                v-model="searchDocente"
                                type="text"
                                placeholder="Escriba Cédula o Nombre para filtrar..."
                                class="w-full h-8 pl-8 pr-3 rounded-md border border-input bg-background text-xs"
                            />
                        </div>
                        <select
                            v-model="form.docente_id"
                            class="sm:w-3/5 h-8 rounded-md border border-input bg-background px-3 text-xs focus:ring-2 focus:ring-primary font-medium"
                            required
                        >
                            <option value="" disabled>Seleccione profesor asignado...</option>
                            <option v-for="doc in docentesFiltrados" :key="doc.id" :value="doc.id">
                                {{ doc.name }} {{ doc.cedula ? ' - C.I. ' + doc.cedula : ' - ' + doc.email }}
                            </option>
                        </select>
                    </div>
                    <span v-if="form.errors.docente_id" class="text-[11px] text-destructive flex items-center gap-1 mt-1">
                        <ShieldAlert class="h-3.5 w-3.5" />
                        {{ form.errors.docente_id }}
                    </span>
                </div>

                <!-- 3. AULA, DÍA, HORAS Y TIPO DE SESIÓN -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-3 bg-muted/40 rounded-lg border">
                    <div class="space-y-1.5 col-span-2 md:col-span-2">
                        <Label class="text-xs font-semibold flex items-center gap-1">
                            <School class="h-3.5 w-3.5 text-primary" />
                            Espacio Físico (Aula / Laboratorio) *
                        </Label>
                        <select
                            v-model="form.aul_id"
                            class="w-full h-8 rounded-md border border-input bg-background px-2 text-xs"
                            required
                        >
                            <option value="" disabled>Seleccione aula o taller...</option>
                            <option v-for="a in props.aulas" :key="a.id_aul" :value="a.id_aul">
                                {{ a.codigo_aula_aul }} - {{ a.nombre_aul }}
                            </option>
                        </select>
                        <span v-if="form.errors.aul_id" class="text-[11px] text-destructive flex items-center gap-1 mt-1">
                            <ShieldAlert class="h-3.5 w-3.5" />
                            {{ form.errors.aul_id }}
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold">Tipo Sesión *</Label>
                        <select v-model="form.tipo_sesion" class="w-full h-8 rounded-md border border-input bg-background px-2 text-xs">
                            <option value="Teoría">Teoría</option>
                            <option value="Práctica">Práctica</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Taller">Taller</option>
                            <option value="Tutoría">Tutoría</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold">Día de Semana *</Label>
                        <select v-model="form.dia_semana" class="w-full h-8 rounded-md border border-input bg-background px-2 text-xs" required>
                            <option v-for="d in dias" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5 col-span-1 md:col-span-2">
                        <Label class="text-xs font-semibold flex items-center gap-1">
                            <Clock class="h-3.5 w-3.5 text-primary" />
                            Hora Inicio *
                        </Label>
                        <input
                            v-model="form.hora_inicio"
                            type="time"
                            class="w-full h-8 rounded-md border border-input bg-background px-2 text-xs text-center font-mono"
                            required
                        />
                    </div>

                    <div class="space-y-1.5 col-span-1 md:col-span-2">
                        <Label class="text-xs font-semibold flex items-center gap-1">
                            <Clock class="h-3.5 w-3.5 text-primary" />
                            Hora Fin *
                        </Label>
                        <input
                            v-model="form.hora_fin"
                            type="time"
                            class="w-full h-8 rounded-md border border-input bg-background px-2 text-xs text-center font-mono"
                            required
                        />
                    </div>
                </div>

                <!-- PRESETS DE FRANJAS HORARIAS UPTP -->
                <div class="space-y-1.5">
                    <Label class="text-[11px] font-medium text-muted-foreground">Franjas académicas sugeridas (clic para aplicar):</Label>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="(p, idx) in bloquesPreestablecidos"
                            :key="idx"
                            type="button"
                            class="text-[10px] px-2 py-0.5 rounded border border-border bg-card hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer"
                            @click="aplicarBloquePreset(p)"
                        >
                            {{ p.label }}
                        </button>
                    </div>
                </div>

                <!-- ALERTA DE ERROR GENERAL -->
                <div v-if="form.hasErrors" class="p-2.5 bg-destructive/10 border border-destructive/20 rounded-md text-destructive text-xs flex items-center gap-2">
                    <AlertCircle class="h-4 w-4 shrink-0" />
                    <span>Por favor revise las alertas del motor Anti-Choque o los campos requeridos en rojo.</span>
                </div>

                <DialogFooter class="pt-2 gap-2">
                    <Button type="button" variant="outline" size="sm" @click="closeModal" :disabled="form.processing">
                        Cancelar
                    </Button>
                    <Button type="submit" size="sm" :disabled="form.processing">
                        {{ form.processing ? 'Validando Anti-Choque...' : 'Guardar Horario Completo' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

