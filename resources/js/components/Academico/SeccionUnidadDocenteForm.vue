<script setup lang="ts">
/**
 * Componente Compartido: Formulario de Creación/Edición de Asignación Sección - Unidad - Docente (SUD).
 *
 * Propósito: Reutilizado por `SeccionUnidadDocente/create.vue` y `SeccionUnidadDocente/edit.vue` para
 * evitar duplicación de lógica. Filtra dinámicamente las unidades curriculares según la malla y
 * semestre de la sección seleccionada (excluyendo las ya asignadas), integra búsqueda en tiempo real
 * de docentes con `DocenteCombobox` y muestra en vivo el consumo respecto al tope de su dedicación laboral.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial del formulario compartido de Sección - Unidad - Docente.
 */
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, BookOpen, CalendarRange, Clock, Info, UserCheck } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import DocenteCombobox from '@/components/Academico/DocenteCombobox.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    obtenerHorasAsignadasDocenteLapso,
    obtenerIdDocente,
    obtenerNombreDocente,
} from '@/lib/academico';
import type { DocenteSelectOption } from '@/lib/academico';

export interface UnidadMallaSemestreOption {
    id_unc: number;
    codigo_unc: string;
    nombre_unc: string;
    horas_sugeridas: number;
    tuc_nombre?: string | null;
    semestre_mcd?: number | null;
    duracion_semestres_mcd?: number | null;
    origen_malla?: boolean;
}

export interface SeccionSudFormOption {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    semestre_sec: number;
    trayecto_sec?: number | null;
    laa_id?: number | null;
    prg_id?: number | null;
    sed_id?: number | null;
    tur_id?: number | null;
    mac_id?: number | null;
    lapso_academico?: { id_laa: number; codigo_laa?: string; nombre_laa: string } | null;
    programa?: { id_prg: number; nombre_prg: string; siglas_prg?: string } | null;
    sede?: { id_sed: number; nombre_sed: string } | null;
    turno?: { id_tur: number; nombre_tur: string } | null;
    malla_curricular?: { id_mac: number; nombre_mac: string } | null;
    unidades_asignadas_ids: number[];
    unidades_malla_semestre: UnidadMallaSemestreOption[];
    tiene_detalles_malla: boolean;
    tiene_detalles_semestre: boolean;
}

export interface AsignacionInicialSud {
    id_caa: number;
    sec_id: number;
    unc_id: number;
    pep_id: number;
    tipo_contratacion_caa?: string | null;
    horas_semanales_caa: number;
    status_id?: number;
}

const props = withDefaults(
    defineProps<{
        secciones: SeccionSudFormOption[];
        unidadesCurriculares: UnidadMallaSemestreOption[];
        docentes: DocenteSelectOption[];
        tiposContratacion: string[];
        asignacion?: AsignacionInicialSud | null;
        secIdInicial?: number | null;
        isEdit?: boolean;
    }>(),
    {
        asignacion: null,
        secIdInicial: null,
        isEdit: false,
    }
);

const initialSecId = props.asignacion?.sec_id ?? props.secIdInicial ?? '';
const initialUncId = props.asignacion?.unc_id ?? '';
const initialPepId = props.asignacion?.pep_id ?? '';

const form = useForm({
    sec_id: initialSecId as number | string,
    unc_id: initialUncId as number | string,
    pep_id: initialPepId as number | string,
    tipo_contratacion_caa: props.asignacion?.tipo_contratacion_caa ?? props.tiposContratacion[0] ?? 'Titular',
    horas_semanales_caa: props.asignacion?.horas_semanales_caa ?? 4,
});

const horasEditadasManual = ref(Boolean(props.isEdit));
const usarCatalogoGeneral = ref(false);

const seccionSeleccionada = computed<SeccionSudFormOption | null>(() => {
    const id = Number(form.sec_id || 0);
    if (!id) return null;
    return props.secciones.find((s) => Number(s.id_sec) === id) ?? null;
});

// En edición, si la unidad original no está en `unidades_malla_semestre`, habilitamos automáticamente catálogo general.
watch(
    [seccionSeleccionada, () => props.asignacion?.unc_id],
    ([sec]) => {
        if (!sec) return;
        const origUnc = Number(props.asignacion?.unc_id || 0);
        if (props.isEdit && origUnc > 0 && Number(sec.id_sec) === Number(props.asignacion?.sec_id)) {
            const existeEnMalla = sec.unidades_malla_semestre.some((u) => Number(u.id_unc) === origUnc);
            if (!existeEnMalla) {
                usarCatalogoGeneral.value = true;
            }
        }
    },
    { immediate: true }
);

const unidadesDisponibles = computed<UnidadMallaSemestreOption[]>(() => {
    const sec = seccionSeleccionada.value;
    if (!sec) return [];

    const fuente =
        sec.tiene_detalles_semestre && !usarCatalogoGeneral.value
            ? sec.unidades_malla_semestre
            : usarCatalogoGeneral.value
              ? props.unidadesCurriculares
              : sec.unidades_malla_semestre;

    const asignadasSet = new Set((sec.unidades_asignadas_ids || []).map((id) => Number(id)));
    const uncActualEdicion =
        props.isEdit && Number(sec.id_sec) === Number(props.asignacion?.sec_id)
            ? Number(props.asignacion?.unc_id || 0)
            : 0;

    return fuente.filter((u) => {
        const uId = Number(u.id_unc);
        if (uncActualEdicion > 0 && uId === uncActualEdicion) {
            return true;
        }
        return !asignadasSet.has(uId);
    });
});

watch(
    () => form.sec_id,
    (newSecId, oldSecId) => {
        if (oldSecId !== undefined && Number(newSecId) !== Number(oldSecId)) {
            usarCatalogoGeneral.value = false;
            const sigueDisponible = unidadesDisponibles.value.some(
                (u) => Number(u.id_unc) === Number(form.unc_id)
            );
            if (!sigueDisponible) {
                form.unc_id = '';
            }
        }
    }
);

watch(
    () => form.unc_id,
    (newUncId) => {
        if (horasEditadasManual.value || !newUncId) return;
        const encontrada = unidadesDisponibles.value.find((u) => Number(u.id_unc) === Number(newUncId));
        if (encontrada && encontrada.horas_sugeridas > 0) {
            form.horas_semanales_caa = Math.min(40, Math.max(1, encontrada.horas_sugeridas));
        }
    }
);

const docenteSeleccionado = computed<DocenteSelectOption | null>(() => {
    const id = Number(form.pep_id || 0);
    if (!id) return null;
    return props.docentes.find((d) => obtenerIdDocente(d) === id) ?? null;
});

const resumenCargaDocente = computed(() => {
    const doc = docenteSeleccionado.value;
    if (!doc) return null;

    const laaId = seccionSeleccionada.value?.laa_id ?? null;
    const mismaAsignacionMismoDocente =
        props.isEdit &&
        Number(form.pep_id) === Number(props.asignacion?.pep_id || 0) &&
        (!laaId || Number(form.sec_id) === Number(props.asignacion?.sec_id || 0));

    const descontarHoras = mismaAsignacionMismoDocente ? Number(props.asignacion?.horas_semanales_caa || 0) : 0;
    const actuales = obtenerHorasAsignadasDocenteLapso(doc, laaId, descontarHoras);
    const nuevas = Math.max(0, Number(form.horas_semanales_caa || 0));
    const maximas = Number(doc.horas_maximas_ded ?? 0);
    const proyectadas = actuales + nuevas;
    const excede = maximas > 0 ? proyectadas > maximas : false;
    const porcentaje = maximas > 0 ? Math.min(100, Math.round((proyectadas / maximas) * 100)) : 0;

    return {
        nombre: obtenerNombreDocente(doc),
        dedicacion: doc.dedicacion_nombre || 'Sin dedicación asignada',
        actuales,
        nuevas,
        proyectadas,
        maximas,
        disponibles: Math.max(0, maximas - actuales),
        excede,
        porcentaje,
    };
});

const submit = () => {
    if (props.isEdit && props.asignacion) {
        form.put(route('seccion_unidad_docente.update', props.asignacion.id_caa), {
            preserveScroll: true,
        });
        return;
    }

    form.post(route('seccion_unidad_docente.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Card class="border-slate-200/80 shadow-sm dark:border-slate-800">
        <CardHeader class="border-b bg-muted/20 pb-4 dark:border-slate-800">
            <CardTitle class="flex items-center gap-2 text-lg">
                <BookOpen class="h-5 w-5 text-primary" aria-hidden="true" />
                <span>{{ isEdit ? 'Editar Asignación Sección - Unidad - Docente' : 'Nueva Asignación Sección - Unidad - Docente' }}</span>
            </CardTitle>
            <CardDescription>
                Vincula una unidad curricular perteneciente a la malla de la sección con un docente activo, validando unicidad por materia y disponibilidad horaria en el lapso.
            </CardDescription>
        </CardHeader>

        <form @submit.prevent="submit" novalidate>
            <CardContent class="space-y-6 pt-6">
                <!-- Alerta global de error de dominio / backend -->
                <div
                    v-if="(form.errors as any).error"
                    role="alert"
                    class="flex items-start gap-2.5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                    <span>{{ (form.errors as any).error }}</span>
                </div>

                <!-- 1. Selección de Sección -->
                <div class="space-y-2">
                    <Label for="sec_id">Sección Académica *</Label>
                    <select
                        id="sec_id"
                        v-model="form.sec_id"
                        required
                        :aria-invalid="form.errors.sec_id ? 'true' : 'false'"
                        :aria-describedby="form.errors.sec_id ? 'sec_id-error' : undefined"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.sec_id }"
                    >
                        <option value="" disabled>Seleccione una sección académica...</option>
                        <option v-for="sec in secciones" :key="sec.id_sec" :value="sec.id_sec">
                            {{ sec.codigo_sec ? `[${sec.codigo_sec}] ` : '' }}{{ sec.nombre_sec }} — T{{ sec.trayecto_sec ?? 1 }}/S{{ sec.semestre_sec ?? 1 }} ({{ sec.lapso_academico?.nombre_laa || 'Sin lapso' }})
                        </option>
                    </select>
                    <p v-if="form.errors.sec_id" id="sec_id-error" class="text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.sec_id }}
                    </p>

                    <!-- Contexto informativo de la sección elegida -->
                    <div
                        v-if="seccionSeleccionada"
                        class="grid grid-cols-1 gap-2 rounded-lg border bg-muted/30 p-3 text-xs sm:grid-cols-4 dark:border-slate-800"
                    >
                        <div>
                            <span class="block text-muted-foreground">Programa Académico</span>
                            <span class="font-medium text-foreground">{{ seccionSeleccionada.programa?.nombre_prg || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-muted-foreground">Malla Curricular</span>
                            <span class="font-medium text-foreground">{{ seccionSeleccionada.malla_curricular?.nombre_mac || 'Sin malla vinculada' }}</span>
                        </div>
                        <div>
                            <span class="block text-muted-foreground">Nivel / Turno</span>
                            <span class="font-medium text-foreground">
                                Trayecto {{ seccionSeleccionada.trayecto_sec ?? 1 }} · Semestre {{ seccionSeleccionada.semestre_sec ?? 1 }}
                                <template v-if="seccionSeleccionada.turno?.nombre_tur">({{ seccionSeleccionada.turno.nombre_tur }})</template>
                            </span>
                        </div>
                        <div>
                            <span class="block text-muted-foreground">Lapso Académico</span>
                            <span class="inline-flex items-center gap-1 font-medium text-primary">
                                <CalendarRange class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ seccionSeleccionada.lapso_academico?.nombre_laa || 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Selección Dinámica de Unidad Curricular (filtrada por Malla / Semestre y sin duplicados en la sección) -->
                <div class="space-y-2">
                    <Label for="unc_id">Unidad Curricular (Malla / Semestre) *</Label>
                    <p id="unc_id-hint" class="text-xs text-muted-foreground">
                        Se muestran únicamente las unidades curriculares de la malla y semestre de la sección seleccionada que aún no han sido asignadas.
                    </p>

                    <!-- Aviso si la malla/semestre aún no tiene unidades cargadas en malla_curricular_detalle -->
                    <div
                        v-if="seccionSeleccionada && !seccionSeleccionada.tiene_detalles_semestre"
                        class="space-y-2 rounded-lg border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300"
                    >
                        <div class="flex items-start gap-2">
                            <Info class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
                            <span>
                                La malla asociada ({{ seccionSeleccionada.malla_curricular?.nombre_mac || 'Sin malla' }}) aún no posee unidades curriculares registradas para el
                                <strong>Trayecto {{ seccionSeleccionada.trayecto_sec ?? 1 }} / Semestre {{ seccionSeleccionada.semestre_sec }}</strong>.
                            </span>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 font-medium text-amber-900 dark:text-amber-200">
                            <input
                                id="usar_catalogo_general"
                                v-model="usarCatalogoGeneral"
                                type="checkbox"
                                class="h-4 w-4 rounded border-amber-400 text-primary focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <span>Cargar catálogo general de unidades curriculares como alternativa</span>
                        </label>
                    </div>

                    <select
                        id="unc_id"
                        v-model="form.unc_id"
                        required
                        :disabled="!seccionSeleccionada"
                        :aria-invalid="form.errors.unc_id ? 'true' : 'false'"
                        :aria-describedby="form.errors.unc_id ? 'unc_id-hint unc_id-error' : 'unc_id-hint'"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.unc_id }"
                    >
                        <option value="" disabled>
                            {{
                                !seccionSeleccionada
                                    ? 'Seleccione primero una sección...'
                                    : unidadesDisponibles.length === 0
                                      ? 'Sin unidades disponibles sin asignar para esta sección'
                                      : 'Seleccione unidad curricular...'
                            }}
                        </option>
                        <option v-for="uc in unidadesDisponibles" :key="uc.id_unc" :value="uc.id_unc">
                            {{ uc.codigo_unc }} — {{ uc.nombre_unc }}
                            {{ uc.tuc_nombre ? `(${uc.tuc_nombre})` : '' }}
                            [{{ uc.horas_sugeridas }}h]
                        </option>
                    </select>
                    <p v-if="form.errors.unc_id" id="unc_id-error" class="text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.unc_id }}
                    </p>
                </div>

                <!-- 3. Selección de Docente con Autocompletado en Tiempo Real y Dedicación Laboral -->
                <div class="space-y-2">
                    <Label for="pep_id">Docente Activo *</Label>
                    <p id="pep_id-hint" class="text-xs text-muted-foreground">
                        Escriba cédula o nombre del docente. Se visualiza su dedicación laboral y carga acumulada en el lapso seleccionado.
                    </p>
                    <DocenteCombobox
                        v-model="form.pep_id"
                        input-id="pep_id"
                        :docentes="docentes"
                        :laa-id="seccionSeleccionada?.laa_id ?? null"
                        :horas-solicitadas="Number(form.horas_semanales_caa || 0)"
                        :excluir-docente-pep-id="isEdit && Number(form.sec_id) === Number(asignacion?.sec_id) ? Number(asignacion?.pep_id || 0) : null"
                        :excluir-horas="isEdit && Number(form.sec_id) === Number(asignacion?.sec_id) ? Number(asignacion?.horas_semanales_caa || 0) : 0"
                        :invalid="Boolean(form.errors.pep_id || resumenCargaDocente?.excede)"
                        :describedby-id="form.errors.pep_id ? 'pep_id-hint pep_id-error' : 'pep_id-hint'"
                    />
                    <p v-if="form.errors.pep_id" id="pep_id-error" class="text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.pep_id }}
                    </p>

                    <!-- Medidor en tiempo real de carga horaria del docente seleccionado -->
                    <div
                        v-if="resumenCargaDocente"
                        :role="resumenCargaDocente.excede ? 'alert' : undefined"
                        class="space-y-2 rounded-lg border p-3 text-xs transition-colors"
                        :class="
                            resumenCargaDocente.excede
                                ? 'border-red-300 bg-red-50/70 text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300'
                                : 'border-emerald-200 bg-emerald-50/60 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300'
                        "
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 font-semibold">
                                <UserCheck class="h-4 w-4" aria-hidden="true" />
                                {{ resumenCargaDocente.nombre }} — {{ resumenCargaDocente.dedicacion }}
                            </span>
                            <span class="font-mono font-semibold">
                                {{ resumenCargaDocente.actuales }}h actuales + {{ resumenCargaDocente.nuevas }}h = {{ resumenCargaDocente.proyectadas }}h / {{ resumenCargaDocente.maximas }}h máx.
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-black/10 dark:bg-white/10">
                            <div
                                class="h-full transition-all duration-200"
                                :class="resumenCargaDocente.excede ? 'bg-red-600' : 'bg-emerald-600'"
                                :style="{ width: `${resumenCargaDocente.porcentaje}%` }"
                            />
                        </div>
                        <p v-if="resumenCargaDocente.excede" class="flex items-center gap-1.5 font-medium">
                            <AlertTriangle class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                            Excede el máximo semanal permitido para su dedicación en este lapso. Ajuste las horas o elija otro docente.
                        </p>
                    </div>
                </div>

                <!-- 4. Parámetros de Carga Horaria y Tipo de Contratación -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="horas_semanales_caa">Horas Semanales Asignadas *</Label>
                        <div class="relative">
                            <Clock
                                aria-hidden="true"
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="horas_semanales_caa"
                                v-model.number="form.horas_semanales_caa"
                                type="number"
                                min="1"
                                max="40"
                                required
                                :aria-invalid="form.errors.horas_semanales_caa ? 'true' : 'false'"
                                :aria-describedby="form.errors.horas_semanales_caa ? 'horas_semanales_caa-error' : undefined"
                                class="pl-9"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.horas_semanales_caa }"
                                @input="horasEditadasManual = true"
                            />
                        </div>
                        <p
                            v-if="form.errors.horas_semanales_caa"
                            id="horas_semanales_caa-error"
                            class="text-xs text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.horas_semanales_caa }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="tipo_contratacion_caa">Tipo de Contratación *</Label>
                        <select
                            id="tipo_contratacion_caa"
                            v-model="form.tipo_contratacion_caa"
                            required
                            :aria-invalid="form.errors.tipo_contratacion_caa ? 'true' : 'false'"
                            :aria-describedby="form.errors.tipo_contratacion_caa ? 'tipo_contratacion_caa-error' : undefined"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.tipo_contratacion_caa }"
                        >
                            <option v-for="tipo in tiposContratacion" :key="tipo" :value="tipo">
                                {{ tipo }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.tipo_contratacion_caa"
                            id="tipo_contratacion_caa-error"
                            class="text-xs text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.tipo_contratacion_caa }}
                        </p>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                <CrudFormFooter
                    :cancel-href="route('seccion_unidad_docente.index')"
                    :is-edit="isEdit"
                    :processing="form.processing"
                />
            </CardFooter>
        </form>
    </Card>
</template>
