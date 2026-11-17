<script setup lang="ts">
/**
 * Vista Standalone: Corrección de Horario en Cuadrícula Semanal (Módulo 7 - Programación Académica).
 *
 * Propósito: Permite filtrar por Lapso Académico, Sede y Sección para gestionar bloques horarios
 * en una matriz semanal de Lunes a Domingo. Al hacer clic en una celda libre o en un bloque
 * existente despliega el modal 'Asignar Bloque Horario' conservando las selecciones y destacando
 * visualmente las alertas de conflicto retornadas por `MotorAntiChoqueService`.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial con filtros en línea, matriz Horarios | Lunes..Domingo,
 *    modal nativo <dialog>, <DocenteCombobox /> y alertas de colisión sin cierre ni pérdida de datos.
 */
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CalendarClock,
    Layers,
    MapPin,
    Plus,
    School,
    ShieldAlert,
    Trash2,
    UserCheck,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import DocenteCombobox from '@/components/Academico/DocenteCombobox.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFlashAlert } from '@/composables/useFlashAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    coincideDiaHorario,
    formatearHoraCorta,
    obtenerAulaEtiqueta,
    obtenerCodigoMateriaHorario,
    obtenerNombreDocenteCarga,
    obtenerNombreMateriaHorario,
} from '@/lib/academico';
import type { DocenteSelectOption } from '@/lib/academico';
import type { BreadcrumbItem } from '@/types/navigation';

interface LapsoOption {
    id_laa: number;
    codigo_laa?: string | null;
    nombre_laa: string;
}

interface SedeOption {
    id_sed: number;
    nombre_sed: string;
}

interface UnidadOption {
    id_unc: number;
    codigo_unc: string;
    nombre_unc: string;
    tuc_id?: number | null;
}

interface CargaSeccionItem {
    id_caa: number;
    sec_id: number;
    unc_id: number;
    pep_id: number;
    unidad_curricular?: UnidadOption | null;
}

interface SeccionOption {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    laa_id: number;
    sed_id: number;
    semestre_sec?: number | null;
    trayecto_sec?: number | null;
    turno?: { id_tur: number; codigo_tur?: string; nombre_tur: string } | null;
    malla_curricular?: {
        id_mac: number;
        nombre_mac: string;
        detalles?: Array<{
            id_mcd: number;
            unc_id: number;
            unidad_curricular?: UnidadOption | null;
        }>;
    } | null;
    cargas_academicas?: CargaSeccionItem[];
}

interface AulaOption {
    id_aul: number;
    codigo_aula_aul: string;
    capacidad_maxima_aul?: number | null;
    tipo_espacio_aul?: string | null;
    sed_id?: number | null;
}

interface BloqueTurnoItem {
    numero: number;
    hora: string;
    hora_inicio: string;
    hora_fin: string;
    tipo: string;
    id_tur?: number;
    turno_codigo?: string;
    turno_nombre?: string;
}

const props = defineProps<{
    turnos: Array<{ id_tur: number; codigo: string; nombre: string; bloques: BloqueTurnoItem[] }>;
    bloques: BloqueTurnoItem[];
    lapsos: LapsoOption[];
    sedes: SedeOption[];
    secciones: SeccionOption[];
    unidadesCurriculares: UnidadOption[];
    docentes: DocenteSelectOption[];
    aulas: AulaOption[];
    horarios: any[];
    filters: {
        laa_id?: string | number;
        sed_id?: string | number;
        sec_id?: string | number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Espacios y Tiempo', href: route('horarios.index') },
    { title: 'Corrección de Horario', subtitle: 'Cuadrícula Semanal', href: route('horarios.correccion') },
];

useFlashAlert();

const diasSemana = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
    { id: 7, nombre: 'Domingo' },
];

const lapsoFiltro = ref<string | number>(props.filters?.laa_id ?? props.lapsos[0]?.id_laa ?? '');
const sedeFiltro = ref<string | number>(props.filters?.sed_id ?? '');
const seccionFiltro = ref<string | number>(props.filters?.sec_id ?? '');

const seccionesFiltradas = computed(() => {
    return props.secciones.filter((s) => {
        const okLapso = !lapsoFiltro.value || Number(s.laa_id) === Number(lapsoFiltro.value);
        const okSede = !sedeFiltro.value || Number(s.sed_id) === Number(sedeFiltro.value);
        return okLapso && okSede;
    });
});

watch([lapsoFiltro, sedeFiltro], () => {
    if (!seccionFiltro.value) return;
    const sigueVisible = seccionesFiltradas.value.some((s) => Number(s.id_sec) === Number(seccionFiltro.value));
    if (!sigueVisible) {
        seccionFiltro.value = '';
    }
});

const seccionActiva = computed<SeccionOption | null>(() => {
    if (!seccionFiltro.value) return null;
    return props.secciones.find((s) => Number(s.id_sec) === Number(seccionFiltro.value)) ?? null;
});

const recargarGrilla = () => {
    router.get(
        route('horarios.correccion'),
        {
            laa_id: lapsoFiltro.value || undefined,
            sed_id: sedeFiltro.value || undefined,
            sec_id: seccionFiltro.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Construye filas de la grilla combinando los bloques oficiales de turnos y cualquier franja personalizada presente en horarios
const filasHorarias = computed<BloqueTurnoItem[]>(() => {
    const mapa = new Map<string, BloqueTurnoItem>();

    for (const b of props.bloques || []) {
        const key = `${b.hora_inicio}-${b.hora_fin}`;
        if (!mapa.has(key)) {
            mapa.set(key, b);
        }
    }

    for (const h of props.horarios || []) {
        const ini = formatearHoraCorta(h.hora_inicio_hor ?? h.hora_inicio);
        const fin = formatearHoraCorta(h.hora_fin_hor ?? h.hora_fin);
        if (!ini || !fin) continue;
        const key = `${ini}-${fin}`;
        if (!mapa.has(key)) {
            mapa.set(key, {
                numero: 0,
                hora: `${ini} - ${fin}`,
                hora_inicio: ini,
                hora_fin: fin,
                tipo: 'Bloque Registrado',
                turno_nombre: 'Horario Personalizado',
            });
        }
    }

    return Array.from(mapa.values()).sort((a, b) => a.hora_inicio.localeCompare(b.hora_inicio));
});

const obtenerBloquesEnCelda = (bloque: BloqueTurnoItem, dia: { id: number; nombre: string }) => {
    return (props.horarios || []).filter((h) => {
        if (!coincideDiaHorario(h, dia.id, dia.nombre)) return false;
        const hIni = formatearHoraCorta(h.hora_inicio_hor ?? h.hora_inicio);
        const hFin = formatearHoraCorta(h.hora_fin_hor ?? h.hora_fin);
        return hIni < bloque.hora_fin && hFin > bloque.hora_inicio;
    });
};

// --- Modal Nativo <dialog>: Asignar / Corregir Bloque Horario ---
const modalRef = ref<HTMLDialogElement | null>(null);
const modalAbierto = ref(false);
const editingHorarioId = ref<number | null>(null);
const editingCaaId = ref<number | null>(null);
const celdaSeleccionadaTexto = ref('');

const form = useForm({
    sec_id: '' as number | string,
    unc_id: '' as number | string,
    pep_id: '' as number | string,
    aul_id: '' as number | string,
    dia_semana: 'Lunes' as string | number,
    hora_inicio: '07:00',
    hora_fin: '07:45',
    tipo_sesion: 'Teoría',
});

const seccionModal = computed<SeccionOption | null>(() => {
    const id = Number(form.sec_id || 0);
    if (!id) return null;
    return props.secciones.find((s) => Number(s.id_sec) === id) ?? null;
});

const unidadesDisponiblesModal = computed<UnidadOption[]>(() => {
    const sec = seccionModal.value;
    if (!sec) return props.unidadesCurriculares;

    const mapa = new Map<number, UnidadOption>();
    for (const carga of sec.cargas_academicas || []) {
        if (carga.unidad_curricular) {
            mapa.set(Number(carga.unidad_curricular.id_unc), carga.unidad_curricular);
        }
    }
    for (const det of sec.malla_curricular?.detalles || []) {
        if (det.unidad_curricular) {
            mapa.set(Number(det.unidad_curricular.id_unc), det.unidad_curricular);
        }
    }
    return mapa.size > 0 ? Array.from(mapa.values()) : props.unidadesCurriculares;
});

const aulasDisponiblesModal = computed<AulaOption[]>(() => {
    const sedObjetivo = Number(seccionModal.value?.sed_id || sedeFiltro.value || 0);
    if (!sedObjetivo) return props.aulas;
    const filtradas = props.aulas.filter((a) => !a.sed_id || Number(a.sed_id) === sedObjetivo);
    return filtradas.length > 0 ? filtradas : props.aulas;
});

// Lista consolidada de conflictos retornados por MotorAntiChoqueService o validaciones del backend
const listaConflictos = computed<Array<{ campo: string; mensaje: string }>>(() => {
    const errs = form.errors as Record<string, string>;
    return Object.entries(errs)
        .filter(([, mensaje]) => Boolean(mensaje))
        .map(([campo, mensaje]) => ({ campo, mensaje }));
});

const hayConflictoAntichoque = computed(() => {
    const errs = form.errors as Record<string, string>;
    return Boolean(errs.pep_id || errs.aul_id || errs.sec_id || errs.hora_fin_hor || errs.horario);
});

const abrirModalCeldaLibre = (bloque: BloqueTurnoItem, dia: { id: number; nombre: string }) => {
    editingHorarioId.value = null;
    editingCaaId.value = null;
    form.clearErrors();

    form.sec_id = seccionFiltro.value || seccionActiva.value?.id_sec || seccionesFiltradas.value[0]?.id_sec || '';
    form.unc_id = '';
    form.pep_id = '';
    form.aul_id = '';
    form.dia_semana = dia.nombre;
    form.hora_inicio = bloque.hora_inicio;
    form.hora_fin = bloque.hora_fin;
    form.tipo_sesion = 'Teoría';

    celdaSeleccionadaTexto.value = `${dia.nombre} · ${bloque.hora_inicio} - ${bloque.hora_fin}`;
    modalAbierto.value = true;
    modalRef.value?.showModal();
};

const abrirModalEditarBloque = (item: any, dia: { id: number; nombre: string }) => {
    editingHorarioId.value = Number(item.id_hor ?? item.id_sho ?? 0) || null;
    editingCaaId.value = Number(item.caa_id ?? item.carga_academica?.id_caa ?? 0) || null;
    form.clearErrors();

    const carga = item.carga_academica ?? {};
    form.sec_id = Number(carga.sec_id ?? carga.seccion?.id_sec ?? seccionFiltro.value ?? '');
    form.unc_id = Number(carga.unc_id ?? carga.unidad_curricular?.id_unc ?? '');
    form.pep_id = Number(carga.pep_id ?? carga.docente_perfil?.id_pep ?? '');
    form.aul_id = Number(item.aul_id ?? item.aula?.id_aul ?? '');
    form.dia_semana = dia.nombre;
    form.hora_inicio = formatearHoraCorta(item.hora_inicio_hor ?? item.hora_inicio);
    form.hora_fin = formatearHoraCorta(item.hora_fin_hor ?? item.hora_fin);
    form.tipo_sesion = item.tipo_sesion_hor || item.tipo_sesion || 'Teoría';

    celdaSeleccionadaTexto.value = `${dia.nombre} · ${form.hora_inicio} - ${form.hora_fin} (Corrección de bloque #${editingHorarioId.value})`;
    modalAbierto.value = true;
    modalRef.value?.showModal();
};

const cerrarModal = () => {
    if (modalRef.value?.open) {
        modalRef.value.close();
        modalRef.value.returnValue = '';
    }
    modalAbierto.value = false;
};

const onNativeDialogClose = () => {
    if (modalRef.value) {
        modalRef.value.returnValue = '';
    }
    modalAbierto.value = false;
};

const intentarCerrarModal = () => {
    if (form.processing) return;
    cerrarModal();
};

const guardarBloque = () => {
    // Si editamos un bloque existente y la terna (sec_id, unc_id, pep_id) coincide con una carga existente,
    // actualizamos directo vía horario.update; si cambia a nueva materia/docente, usamos guardarHorarioDirecto.
    const cargaCoincidente = seccionModal.value?.cargas_academicas?.find(
        (c) => Number(c.unc_id) === Number(form.unc_id) && Number(c.pep_id) === Number(form.pep_id)
    );

    if (editingHorarioId.value && cargaCoincidente) {
        router.put(
            route('horario.update', editingHorarioId.value),
            {
                caa_id: cargaCoincidente.id_caa,
                aul_id: form.aul_id,
                dia_semana_hor: form.dia_semana,
                hora_inicio_hor: form.hora_inicio,
                hora_fin_hor: form.hora_fin,
                tipo_sesion_hor: form.tipo_sesion,
            },
            {
                preserveScroll: true,
                onStart: () => {
                    form.processing = true;
                    form.clearErrors();
                },
                onFinish: () => {
                    form.processing = false;
                },
                onError: (errors) => {
                    Object.entries(errors).forEach(([k, v]) => form.setError(k as any, String(v)));
                    // Conserva el modal abierto y la selección intacta
                },
                onSuccess: () => {
                    cerrarModal();
                    recargarGrilla();
                },
            }
        );
        return;
    }

    form.post(route('horarios.guardar_directo'), {
        preserveScroll: true,
        onError: () => {
            // Conserva el modal abierto y la selección intacta para visualizar alertas de MotorAntiChoqueService
        },
        onSuccess: () => {
            cerrarModal();
            recargarGrilla();
        },
    });
};

const eliminarBloqueDesdeModal = () => {
    if (!editingHorarioId.value || form.processing) return;
    router.delete(route('horarios.eliminar_bloque', editingHorarioId.value), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModal();
            recargarGrilla();
        },
    });
};
</script>

<template>
    <Head title="Corrección de Horario | Programación Académica" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <!-- ENCABEZADO -->
            <div class="flex flex-col justify-between gap-4 border-b pb-5 sm:flex-row sm:items-center dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <CalendarClock class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Corrección Interactiva de Horarios
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Seleccione Lapso, Sede y Sección; luego haga clic sobre cualquier celda libre o bloque asignado para programar o corregir horarios con verificación Anti-Choque.
                    </p>
                </div>

                <Button as-child variant="outline" size="default">
                    <Link :href="route('horarios.index')">
                        <Layers class="mr-1.5 h-4 w-4" aria-hidden="true" />
                        Vista por Tarjetas
                    </Link>
                </Button>
            </div>

            <!-- 1. CABECERA CON 3 SELECTORES EN LÍNEA: LAPSO ACADÉMICO, SEDE Y SECCIÓN -->
            <div class="grid grid-cols-1 gap-4 rounded-xl border bg-card p-4 shadow-xs md:grid-cols-3 dark:border-slate-800">
                <div class="space-y-1.5">
                    <Label for="filtro-lapso" class="flex items-center gap-1.5 text-xs font-semibold">
                        <CalendarClock class="h-3.5 w-3.5 text-primary" aria-hidden="true" />
                        Lapso Académico
                    </Label>
                    <select
                        id="filtro-lapso"
                        v-model="lapsoFiltro"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        @change="recargarGrilla"
                    >
                        <option value="">Todos los lapsos activos</option>
                        <option v-for="l in lapsos" :key="l.id_laa" :value="l.id_laa">
                            {{ l.codigo_laa ? `[${l.codigo_laa}] ` : '' }}{{ l.nombre_laa }}
                        </option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <Label for="filtro-sede" class="flex items-center gap-1.5 text-xs font-semibold">
                        <MapPin class="h-3.5 w-3.5 text-primary" aria-hidden="true" />
                        Sede
                    </Label>
                    <select
                        id="filtro-sede"
                        v-model="sedeFiltro"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        @change="recargarGrilla"
                    >
                        <option value="">Todas las sedes</option>
                        <option v-for="s in sedes" :key="s.id_sed" :value="s.id_sed">
                            {{ s.nombre_sed }}
                        </option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <Label for="filtro-seccion" class="flex items-center gap-1.5 text-xs font-semibold">
                        <Layers class="h-3.5 w-3.5 text-primary" aria-hidden="true" />
                        Sección
                    </Label>
                    <select
                        id="filtro-seccion"
                        v-model="seccionFiltro"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        @change="recargarGrilla"
                    >
                        <option value="">Todas las secciones ({{ seccionesFiltradas.length }})</option>
                        <option v-for="sec in seccionesFiltradas" :key="sec.id_sec" :value="sec.id_sec">
                            {{ sec.codigo_sec ? `[${sec.codigo_sec}] ` : '' }}{{ sec.nombre_sec }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- 2. CUADRÍCULA SEMANAL: HORARIOS | LUNES .. DOMINGO -->
            <div
                tabindex="0"
                role="region"
                aria-labelledby="correccion-horario-caption"
                class="overflow-x-auto rounded-xl border bg-card shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring dark:border-slate-800"
            >
                <table class="w-full min-w-[1040px] border-collapse text-xs">
                    <caption id="correccion-horario-caption" class="sr-only">
                        Cuadrícula semanal de bloques horarios de Lunes a Domingo
                    </caption>
                    <thead class="sticky top-0 z-10 bg-muted/80 backdrop-blur-xs">
                        <tr class="border-b dark:border-slate-800">
                            <th scope="col" class="w-36 border-r px-3 py-3 text-left font-bold text-slate-900 dark:border-slate-800 dark:text-slate-100">
                                Horarios
                            </th>
                            <th
                                v-for="dia in diasSemana"
                                :key="dia.id"
                                scope="col"
                                class="min-w-[130px] border-r px-2.5 py-3 text-center font-bold text-slate-900 last:border-r-0 dark:border-slate-800 dark:text-slate-100"
                            >
                                {{ dia.nombre }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y dark:divide-slate-800">
                        <tr
                            v-for="bloque in filasHorarias"
                            :key="`${bloque.hora_inicio}-${bloque.hora_fin}`"
                            class="align-top transition-colors hover:bg-muted/20"
                        >
                            <th
                                scope="row"
                                class="border-r bg-muted/30 px-3 py-2.5 text-left font-medium dark:border-slate-800"
                            >
                                <div class="font-mono text-xs font-bold text-foreground">
                                    {{ bloque.hora_inicio }} - {{ bloque.hora_fin }}
                                </div>
                                <div class="mt-0.5 text-[10px] text-muted-foreground">
                                    {{ bloque.turno_nombre || 'Jornada' }}
                                    <template v-if="bloque.numero > 0">· B{{ bloque.numero }}</template>
                                </div>
                            </th>

                            <td
                                v-for="dia in diasSemana"
                                :key="`${bloque.hora_inicio}-${dia.id}`"
                                class="border-r p-1.5 align-top last:border-r-0 dark:border-slate-800"
                            >
                                <div class="space-y-1.5">
                                    <button
                                        v-for="item in obtenerBloquesEnCelda(bloque, dia)"
                                        :key="item.id_hor ?? item.id_sho"
                                        type="button"
                                        class="w-full rounded-lg border border-l-4 border-l-primary bg-background p-2 text-left shadow-2xs transition hover:border-primary hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        @click="abrirModalEditarBloque(item, dia)"
                                    >
                                        <div class="flex items-center justify-between gap-1 font-mono text-[10px] text-muted-foreground">
                                            <span class="font-semibold text-foreground">
                                                {{ formatearHoraCorta(item.hora_inicio_hor ?? item.hora_inicio) }}-{{ formatearHoraCorta(item.hora_fin_hor ?? item.hora_fin) }}
                                            </span>
                                            <Badge variant="outline" class="px-1 py-0 text-[9px]">
                                                {{ item.tipo_sesion_hor || item.tipo_sesion || 'Teoría' }}
                                            </Badge>
                                        </div>
                                        <div class="mt-1 line-clamp-2 font-semibold leading-tight text-slate-900 dark:text-slate-100">
                                            {{ obtenerNombreMateriaHorario(item) }}
                                        </div>
                                        <div class="mt-0.5 font-mono text-[10px] text-muted-foreground">
                                            {{ obtenerCodigoMateriaHorario(item) }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1 truncate text-[10px] text-slate-700 dark:text-slate-300">
                                            <UserCheck class="h-3 w-3 shrink-0 text-primary" aria-hidden="true" />
                                            <span class="truncate">{{ obtenerNombreDocenteCarga(item.carga_academica) }}</span>
                                        </div>
                                        <div class="mt-0.5 flex items-center gap-1 truncate text-[10px] font-semibold text-primary">
                                            <School class="h-3 w-3 shrink-0" aria-hidden="true" />
                                            <span class="truncate">{{ obtenerAulaEtiqueta(item.aula) }}</span>
                                        </div>
                                    </button>

                                    <button
                                        v-if="obtenerBloquesEnCelda(bloque, dia).length === 0"
                                        type="button"
                                        :aria-label="`Asignar bloque horario ${dia.nombre} de ${bloque.hora_inicio} a ${bloque.hora_fin}`"
                                        class="group flex min-h-16 w-full items-center justify-center rounded-md border border-dashed border-transparent text-muted-foreground/40 transition hover:border-primary/40 hover:bg-primary/5 hover:text-primary focus-visible:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        @click="abrirModalCeldaLibre(bloque, dia)"
                                    >
                                        <Plus class="h-4 w-4 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. MODAL NATIVO <dialog>: ASIGNAR BLOQUE HORARIO CON ALERTAS ANTI-CHOQUE -->
        <dialog
            ref="modalRef"
            aria-labelledby="modal-bloque-titulo"
            class="m-auto w-[min(94vw,44rem)] max-h-[92vh] overflow-y-auto rounded-xl border border-border bg-background p-0 text-foreground shadow-2xl backdrop:bg-black/55"
            @cancel.prevent="intentarCerrarModal"
            @close="onNativeDialogClose"
        >
            <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b bg-background/95 px-6 py-4 backdrop-blur-xs dark:border-slate-800">
                <div class="space-y-1">
                    <h2 id="modal-bloque-titulo" class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-slate-100">
                        <CalendarClock class="h-5 w-5 text-primary" aria-hidden="true" />
                        <span>Asignar Bloque Horario</span>
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        {{ celdaSeleccionadaTexto }}
                    </p>
                </div>

                <button
                    type="button"
                    aria-label="Cerrar modal"
                    :disabled="form.processing"
                    class="inline-flex min-h-8 min-w-8 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
                    @click="intentarCerrarModal"
                >
                    <X class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>

            <form @submit.prevent="guardarBloque" class="space-y-4 p-6" novalidate>
                <!-- ALERTA DE CONFLICTO DEL MOTOR ANTI-CHOQUE (NO CIERRA MODAL NI BORRA SELECCIÓN) -->
                <div
                    v-if="listaConflictos.length > 0"
                    role="alert"
                    class="space-y-1.5 rounded-lg border border-red-300 bg-red-50 p-3.5 text-xs text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
                >
                    <div class="flex items-center gap-2 font-bold">
                        <ShieldAlert class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400" aria-hidden="true" />
                        <span>
                            {{ hayConflictoAntichoque ? 'Conflicto detectado por el Motor Anti-Choque' : 'Revise los errores de validación antes de continuar' }}
                        </span>
                    </div>
                    <ul class="ml-6 list-disc space-y-1">
                        <li v-for="(err, idx) in listaConflictos" :key="idx">
                            {{ err.mensaje }}
                        </li>
                    </ul>
                </div>

                <!-- SECCIÓN Y FRANJA HORARIA -->
                <div class="grid grid-cols-1 gap-4 rounded-lg border bg-muted/25 p-3.5 sm:grid-cols-2 dark:border-slate-800">
                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="modal-sec-id">Sección *</Label>
                        <select
                            id="modal-sec-id"
                            v-model="form.sec_id"
                            required
                            autofocus
                            :aria-invalid="form.errors.sec_id ? 'true' : 'false'"
                            :aria-describedby="form.errors.sec_id ? 'modal-sec-error' : undefined"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.sec_id }"
                        >
                            <option value="" disabled>Seleccione una sección...</option>
                            <option v-for="sec in secciones" :key="sec.id_sec" :value="sec.id_sec">
                                {{ sec.codigo_sec ? `[${sec.codigo_sec}] ` : '' }}{{ sec.nombre_sec }}
                            </option>
                        </select>
                        <p v-if="form.errors.sec_id" id="modal-sec-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                            <AlertTriangle class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                            {{ form.errors.sec_id }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="modal-dia">Día de Semana *</Label>
                        <select
                            id="modal-dia"
                            v-model="form.dia_semana"
                            required
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option v-for="d in diasSemana" :key="d.id" :value="d.nombre">
                                {{ d.nombre }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="modal-tipo-sesion">Tipo de Sesión *</Label>
                        <select
                            id="modal-tipo-sesion"
                            v-model="form.tipo_sesion"
                            required
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="Teoría">Teoría</option>
                            <option value="Práctica">Práctica</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Taller">Taller</option>
                            <option value="Tutoría">Tutoría</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="modal-hora-inicio">Hora Inicio *</Label>
                        <Input id="modal-hora-inicio" v-model="form.hora_inicio" type="time" required class="font-mono" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="modal-hora-fin">Hora Fin *</Label>
                        <Input
                            id="modal-hora-fin"
                            v-model="form.hora_fin"
                            type="time"
                            required
                            :aria-invalid="(form.errors as any).hora_fin_hor || form.errors.hora_fin ? 'true' : 'false'"
                            class="font-mono"
                            :class="{ 'border-red-500 ring-1 ring-red-500': (form.errors as any).hora_fin_hor || form.errors.hora_fin }"
                        />
                    </div>
                </div>

                <!-- UNIDAD CURRICULAR -->
                <div class="space-y-1.5">
                    <Label for="modal-unc-id">Unidad Curricular *</Label>
                    <select
                        id="modal-unc-id"
                        v-model="form.unc_id"
                        required
                        :aria-invalid="form.errors.unc_id ? 'true' : 'false'"
                        :aria-describedby="form.errors.unc_id ? 'modal-unc-error' : undefined"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.unc_id }"
                    >
                        <option value="" disabled>Seleccione la unidad curricular...</option>
                        <option v-for="uc in unidadesDisponiblesModal" :key="uc.id_unc" :value="uc.id_unc">
                            {{ uc.codigo_unc }} — {{ uc.nombre_unc }}
                        </option>
                    </select>
                    <p v-if="form.errors.unc_id" id="modal-unc-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                        <AlertTriangle class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.unc_id }}
                    </p>
                </div>

                <!-- DOCENTE -->
                <div class="space-y-1.5">
                    <Label for="modal-pep-id">Docente *</Label>
                    <DocenteCombobox
                        v-model="form.pep_id"
                        input-id="modal-pep-id"
                        :docentes="docentes"
                        :laa-id="seccionModal?.laa_id ?? null"
                        :invalid="Boolean(form.errors.pep_id || (form.errors as any).horas_semanales_caa)"
                        :describedby-id="form.errors.pep_id ? 'modal-pep-error' : undefined"
                    />
                    <p v-if="form.errors.pep_id" id="modal-pep-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                        <ShieldAlert class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.pep_id }}
                    </p>
                </div>

                <!-- AMBIENTE / AULA -->
                <div class="space-y-1.5">
                    <Label for="modal-aul-id">Ambiente / Aula *</Label>
                    <select
                        id="modal-aul-id"
                        v-model="form.aul_id"
                        required
                        :aria-invalid="form.errors.aul_id ? 'true' : 'false'"
                        :aria-describedby="form.errors.aul_id ? 'modal-aul-error' : undefined"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :class="{ 'border-red-500 ring-1 ring-red-500': form.errors.aul_id }"
                    >
                        <option value="" disabled>Seleccione el ambiente o aula...</option>
                        <option v-for="a in aulasDisponiblesModal" :key="a.id_aul" :value="a.id_aul">
                            {{ obtenerAulaEtiqueta(a) }}
                            {{ a.tipo_espacio_aul ? `· ${a.tipo_espacio_aul}` : '' }}
                            {{ a.capacidad_maxima_aul ? `(Cap: ${a.capacidad_maxima_aul})` : '' }}
                        </option>
                    </select>
                    <p v-if="form.errors.aul_id" id="modal-aul-error" class="flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400">
                        <ShieldAlert class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.aul_id }}
                    </p>
                </div>

                <!-- PIE DE ACCIONES DEL MODAL -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-4 dark:border-slate-800">
                    <div>
                        <Button
                            v-if="editingHorarioId"
                            type="button"
                            variant="destructive"
                            size="sm"
                            :disabled="form.processing"
                            @click="eliminarBloqueDesdeModal"
                        >
                            <Trash2 class="mr-1.5 h-3.5 w-3.5" aria-hidden="true" />
                            Eliminar Bloque
                        </Button>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="form.processing"
                            @click="intentarCerrarModal"
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" size="sm" :disabled="form.processing">
                            {{ form.processing ? 'Validando Anti-Choque...' : editingHorarioId ? 'Actualizar Bloque' : 'Guardar Bloque' }}
                        </Button>
                    </div>
                </div>
            </form>
        </dialog>
    </AppLayout>
</template>

<style scoped>
dialog:modal {
    overscroll-behavior: contain;
}

dialog::backdrop {
    overflow: hidden;
    overscroll-behavior: contain;
}
</style>
