<script setup lang="ts">
/**
 * Vista Standalone: Creación de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite registrar una nueva Malla Curricular (plan de estudios) mediante useForm de Inertia,
 * cargando de forma reactiva las unidades curriculares del Programa seleccionado, estructuradas por trayectos y semestres,
 * e incorporando los requisitos globales de egreso para TSU e Ingeniería/Licenciatura.
 *
 * Historial:
 *  - 2026-11-08: Rediseño interactivo del constructor dinámico de trayectos, tipo de lapso y unidades por período.
 *  - 2026-11-14: Alineación estricta a `sogac_mio.sql` (`programa.id_prg`, `tipo_lapso.id_tla`, `total_credito_*`,
 *    `total_horas_*`, `total_unidades_*`) reutilizando adaptadores compartidos de `@/lib/academico`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 *  - 2026-11-16: Corrección de diseño del panel inline de inserción de unidades en semestres paralelos
 *    (eliminación de `overflow-hidden`, apilamiento vertical con `w-full min-w-0`, grilla `xl:grid-cols-2`,
 *    `pb-24` inferior y auto-scroll con `scrollIntoView` al abrir "+ Agregar Unidad").
 *  - 2026-11-16: Ampliación del selector inline y tabla por semestre con Eje Curricular (`ecu_id`),
 *    horas HEA/HEI/HTE con cálculo automático, Duración (1 o 2 semestres) y Nota Aprobatoria validada.
 *  - 2026-11-16: Reemplazo del filtro + `<select>` separado por el buscador autocompletado
 *    interactivo `<UnidadCurricularCombobox />` con navegación por teclado y prioridad por código.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    AlertCircle,
    ArrowLeft,
    Award,
    BookOpen,
    Calendar,
    Check,
    CheckCircle2,
    GraduationCap,
    Layers,
    Loader2,
    Network,
    Plus,
    Search,
    Sparkles,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { route } from 'ziggy-js';
import UnidadCurricularCombobox from '@/components/Academico/UnidadCurricularCombobox.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    construirPeriodosParaTrayecto as construirPeriodosLapso,
    esNotaValidaEnEscala,
    inicializarParametrosPeriodo,
    obtenerCodigoPrograma,
    obtenerConfigTipoLapso,
    obtenerDuracionTipoLapso,
    obtenerErrorNotaPeriodo,
    obtenerIdPrograma,
    obtenerIdTipoLapso,
    obtenerNombrePrograma,
    obtenerNombreTipoLapso,
    obtenerNomenclaturaTipoLapso,
    obtenerTipoLapsoSemestralPorDefecto,
    recalcularHtePeriodo,
    recalcularHteUnidad,
    resolverEscalaPrograma,
    ROMANOS_TRAYECTO as ROMANOS,
} from '@/lib/academico';
import type {
    EjeCurricularCatalogoItem,
    EscalaMedicionCatalogoItem,
    PeriodoContainer,
    ProgramaCatalogoItem,
    TipoLapsoCatalogoItem,
    TrayectoBuilderBlock,
    UnidadCatalogoItem,
    UnidadSeleccionadaRow,
} from '@/lib/academico';
import type { BreadcrumbItem } from '@/types/navigation';

interface CohorteItem {
    id_coh: number;
    nombre_coh: string;
    fecha_inicio_coh?: string | null;
}

const props = withDefaults(
    defineProps<{
        programas?: ProgramaCatalogoItem[];
        pnfs?: ProgramaCatalogoItem[];
        tiposLapso?: TipoLapsoCatalogoItem[];
        tiposPeriodo?: TipoLapsoCatalogoItem[];
        cohortes?: CohorteItem[];
        ejes?: EjeCurricularCatalogoItem[];
        escalas?: EscalaMedicionCatalogoItem[];
        unidadesCatalogo?: UnidadCatalogoItem[];
    }>(),
    {
        programas: () => [],
        pnfs: () => [],
        tiposLapso: () => [],
        tiposPeriodo: () => [],
        cohortes: () => [],
        ejes: () => [],
        escalas: () => [],
        unidadesCatalogo: () => [],
    }
);

const listaProgramas = computed<ProgramaCatalogoItem[]>(() =>
    props.programas.length ? props.programas : props.pnfs
);

const catalogoTiposLapso = computed<TipoLapsoCatalogoItem[]>(() =>
    props.tiposLapso.length ? props.tiposLapso : props.tiposPeriodo
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Malla Curricular', subtitle: 'Planes de Estudio', href: route('academico.malla-curricular.index') },
    { title: 'Crear Malla', subtitle: 'Nuevo Registro', href: route('academico.malla-curricular.create') },
];

// Determinar el Tipo de Lapso Semestral por defecto
const tipoLapsoDefaultId = computed<number>(() =>
    obtenerTipoLapsoSemestralPorDefecto(catalogoTiposLapso.value)
);

const tipoLapsoGlobalId = ref<number>(tipoLapsoDefaultId.value);
const trayectos = ref<TrayectoBuilderBlock[]>([]);
const cargandoSugerencia = ref(false);
const mostrarRequisitosEgreso = ref(false);
const notificacionAccion = ref<string | null>(null);
const ultimoTrayectoUid = ref<string | null>(null);
let notifTimer: ReturnType<typeof setTimeout> | null = null;

const emitirNotificacionLocal = (titulo: string, detalle: string, tipo: 'success' | 'info' = 'success') => {
    notificacionAccion.value = `${titulo}: ${detalle}`;
    if (tipo === 'info') {
        toast.info(titulo, { description: detalle });
    } else {
        toast.success(titulo, { description: detalle });
    }
    if (notifTimer) clearTimeout(notifTimer);
    notifTimer = setTimeout(() => {
        notificacionAccion.value = null;
        ultimoTrayectoUid.value = null;
    }, 4000);
};

const form = useForm({
    nombre_mac: '',
    codigo_gaceta_mac: '', // Número de Cohorte / Gaceta
    version_pensum_mac: '',
    fecha_aprobacion_mac: new Date().toISOString().slice(0, 10),
    observacion_mac: '',
    prg_id: '' as string | number,

    // Requisitos globales TSU
    total_credito_tsu_mac: '' as string | number,
    total_horas_tsu_mac: '' as string | number,
    total_unidades_tsu_mac: '' as string | number,

    // Requisitos globales Ingeniería
    total_credito_ing_mac: '' as string | number,
    total_horas_ing_mac: '' as string | number,
    total_unidades_ing_mac: '' as string | number,
    total_credito_prosecucion_mac: '' as string | number,

    unidades_seleccionadas: [] as any[],
});

const obtenerConfigTipoPeriodo = (tlaId: number) =>
    obtenerConfigTipoLapso(catalogoTiposLapso.value, tlaId);

const construirPeriodosParaTrayecto = (
    tlaId: number,
    numeroOrdenTra = 1,
    periodosPrevios: PeriodoContainer[] = []
): PeriodoContainer[] =>
    construirPeriodosLapso(catalogoTiposLapso.value, tlaId, numeroOrdenTra, periodosPrevios);

/**
 * Crea un nuevo bloque de Trayecto (Trayecto I, Trayecto II, etc.).
 */
const agregarTrayecto = (ordenPersonalizado?: number, silencioso = false) => {
    const usados = trayectos.value.map((t) => t.numero_orden_tra);
    let siguienteOrden = ordenPersonalizado;

    if (siguienteOrden === undefined) {
        siguienteOrden = 1;
        while (usados.includes(siguienteOrden)) {
            siguienteOrden++;
        }
    }

    if (usados.includes(siguienteOrden)) {
        if (!silencioso) {
            emitirNotificacionLocal('Aviso', 'Ese trayecto ya se encuentra agregado en la malla.', 'info');
        }
        return;
    }

    const tlaId = tipoLapsoGlobalId.value || tipoLapsoDefaultId.value;
    const cfg = obtenerConfigTipoPeriodo(tlaId);
    const etiquetaRomana = ROMANOS[siguienteOrden] ?? String(siguienteOrden);
    const nombreTra = siguienteOrden === 0 ? 'Trayecto Inicial' : `Trayecto ${etiquetaRomana}`;
    const uid = `tra-${siguienteOrden}-${Date.now()}`;
    const periodosCreados = construirPeriodosParaTrayecto(tlaId, siguienteOrden);

    trayectos.value.push({
        uid,
        numero_orden_tra: siguienteOrden,
        nombre_tra: nombreTra,
        tla_id: tlaId,
        tipo_periodo_nombre: cfg.singular,
        periodos: periodosCreados,
    });

    trayectos.value.sort((a, b) => a.numero_orden_tra - b.numero_orden_tra);

    if (!silencioso) {
        ultimoTrayectoUid.value = uid;
        const nombresPeriodos = periodosCreados.map((p) => p.etiqueta).join(' y ');
        emitirNotificacionLocal(
            '¡Trayecto Agregado!',
            `Se añadió ${nombreTra} con ${nombresPeriodos}.`
        );
        nextTick(() => {
            const el = document.getElementById(uid);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
};

/**
 * Elimina un bloque de Trayecto de la configuración.
 */
const eliminarTrayecto = (uid: string) => {
    const target = trayectos.value.find((t) => t.uid === uid);
    trayectos.value = trayectos.value.filter((t) => t.uid !== uid);
    if (target) {
        emitirNotificacionLocal('Trayecto Eliminado', `Se quitó ${target.nombre_tra} del diseño curricular.`, 'info');
    }
};

/**
 * Cambia el Tipo de Lapso (Semestres, Trimestres, Años) de un Trayecto específico.
 */
const cambiarTipoPeriodoTrayecto = (trayecto: TrayectoBuilderBlock, nuevoTlaId: number, notificar = true) => {
    trayecto.tla_id = Number(nuevoTlaId);
    const cfg = obtenerConfigTipoPeriodo(trayecto.tla_id);
    trayecto.tipo_periodo_nombre = cfg.singular;
    trayecto.periodos = construirPeriodosParaTrayecto(trayecto.tla_id, trayecto.numero_orden_tra, trayecto.periodos);
    if (notificar) {
        const nombresPeriodos = trayecto.periodos.map((p) => p.etiqueta).join(', ');
        emitirNotificacionLocal(
            'Tipo de Lapso Actualizado',
            `${trayecto.nombre_tra} ahora contiene: ${nombresPeriodos}.`,
            'info'
        );
    }
};

/**
 * Aplica el Tipo de Lapso global a todos los trayectos creados.
 */
const aplicarTipoPeriodoGlobal = () => {
    const cfg = obtenerConfigTipoPeriodo(tipoLapsoGlobalId.value);
    trayectos.value.forEach((tr) => {
        cambiarTipoPeriodoTrayecto(tr, tipoLapsoGlobalId.value, false);
    });
    emitirNotificacionLocal(
        'Tipo de Lapso Aplicado',
        `Todos los trayectos se actualizaron a modalidad ${cfg.singular} (${cfg.cantidad} por trayecto).`,
        'info'
    );
};

// Inicializar por defecto con Trayecto I (Semestre 1 y Semestre 2) tal como en el croquis
if (trayectos.value.length === 0) {
    agregarTrayecto(1, true);
}

/**
 * Filtra el catálogo de Unidades Curriculares para el buscador de [+ Agregar Unidad].
 * Prioriza las materias del Programa seleccionado y evita duplicar materias ya insertadas en la malla.
 */
const programaSeleccionadoObj = computed(() =>
    listaProgramas.value.find((p) => obtenerIdPrograma(p) === Number(form.prg_id))
);

const escalaProgramaActual = computed(() =>
    resolverEscalaPrograma(programaSeleccionadoObj.value, props.escalas)
);

const esNotaValida = (valor: number | string | null | undefined): boolean =>
    esNotaValidaEnEscala(valor, escalaProgramaActual.value);

const mensajeErrorNotaPeriodo = (periodo: PeriodoContainer): string | null =>
    obtenerErrorNotaPeriodo(periodo, escalaProgramaActual.value);

const idsUnidadesYaEnMalla = computed<Set<number>>(() => {
    const set = new Set<number>();
    trayectos.value.forEach((t) => {
        t.periodos.forEach((p) => {
            p.unidades.forEach((u) => set.add(Number(u.unc_id)));
        });
    });
    return set;
});

const codigoProgramaPrefix = computed(() => {
    const codigoPrograma = obtenerCodigoPrograma(programaSeleccionadoObj.value);
    return codigoPrograma ? codigoPrograma.substring(0, 3).toUpperCase() : null;
});

/**
 * Precarga horas, eje, duración y nota aprobatoria al seleccionar una Unidad Curricular del catálogo.
 */
const onSeleccionarUnidadCatalogo = (periodo: PeriodoContainer, item?: UnidadCatalogoItem | null) => {
    const uCat =
        item ??
        props.unidadesCatalogo.find((u) => Number(u.id_unc) === Number(periodo.uncIdSeleccionada));
    if (uCat) {
        inicializarParametrosPeriodo(periodo, uCat, escalaProgramaActual.value, props.ejes);
    }
};

/**
 * Ajusta las semanas estimadas al cambiar la duración en semestres dentro de una fila.
 */
const onCambiarDuracionFila = (trayecto: TrayectoBuilderBlock, unidad: UnidadSeleccionadaRow) => {
    const cfg = obtenerConfigTipoPeriodo(trayecto.tla_id);
    const semestres = unidad.duracion_semestres === 2 ? 2 : 1;
    unidad.duracion_semestres = semestres;
    unidad.duracion_semanas = (cfg.duracion || 18) * semestres;
};

/**
 * Abre el selector inline con el buscador autocompletado enfocado y visible.
 */
const abrirSelectorUnidad = (trayecto: TrayectoBuilderBlock, periodo: PeriodoContainer) => {
    periodo.busquedaActiva = true;
    periodo.terminoBusqueda = '';
    periodo.uncIdSeleccionada = '';
    inicializarParametrosPeriodo(periodo, undefined, escalaProgramaActual.value, props.ejes);

    nextTick(() => {
        const panel = document.getElementById(`selector-${trayecto.uid}-${periodo.numero}`);
        panel?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const inputEl = document.getElementById(`buscar-uc-${trayecto.uid}-${periodo.numero}`);
        inputEl?.focus({ preventScroll: true });
    });
};

/**
 * Inserta la Unidad Curricular elegida con sus parámetros académicos en la tabla del Semestre/Período.
 */
const confirmarAgregarUnidad = (trayecto: TrayectoBuilderBlock, periodo: PeriodoContainer, itemDirecto?: UnidadCatalogoItem) => {
    const catalogoItem =
        itemDirecto ??
        props.unidadesCatalogo.find((u) => Number(u.id_unc) === Number(periodo.uncIdSeleccionada));

    if (!catalogoItem) return;

    if (periodo.unidades.some((u) => Number(u.unc_id) === Number(catalogoItem.id_unc))) {
        return;
    }

    if (mensajeErrorNotaPeriodo(periodo)) {
        toast.error('Nota aprobatoria fuera de rango', {
            description: mensajeErrorNotaPeriodo(periodo) ?? undefined,
        });
        return;
    }

    const cfg = obtenerConfigTipoPeriodo(trayecto.tla_id);
    const duracionSemestres = periodo.duracionSemestresSeleccionada === 2 ? 2 : 1;
    const htea = Math.max(0, Math.trunc(Number(periodo.hteaSeleccionado) || 0));
    const htei = Math.max(0, Math.trunc(Number(periodo.hteiSeleccionado) || 0));
    const thte = Math.max(0, Math.trunc(Number(periodo.thteSeleccionado) || (htea + htei)));
    const ecuId = periodo.ecuIdSeleccionado ? Number(periodo.ecuIdSeleccionado) : null;
    const notaAprobatoria = Number(periodo.notaAprobatoriaSeleccionada);

    periodo.unidades.push({
        unc_id: catalogoItem.id_unc,
        codigo: catalogoItem.codigo_unc,
        nombre: catalogoItem.nombre_unc,
        tipo: catalogoItem.tipo,
        tuc_id: catalogoItem.tuc_id ?? null,
        ecu_id: ecuId,
        esm_id: escalaProgramaActual.value.esm_id,
        uc: catalogoItem.uc || 3,
        htea,
        htei,
        thte,
        duracion_semestres: duracionSemestres,
        duracion_semanas: (cfg.duracion || 18) * duracionSemestres,
        nota_minima_aprobatoria_mcd: notaAprobatoria,
    });

    emitirNotificacionLocal(
        '¡Unidad Curricular Agregada!',
        `[${catalogoItem.codigo_unc}] ${catalogoItem.nombre_unc} insertada en ${periodo.etiqueta} (${trayecto.nombre_tra}).`
    );

    // Limpiar buscador para agregar otra unidad o cerrar si ya se asignaron todas
    periodo.uncIdSeleccionada = '';
    periodo.terminoBusqueda = '';
    const quedanDisponibles = props.unidadesCatalogo.some((u) => !idsUnidadesYaEnMalla.value.has(Number(u.id_unc)));
    if (quedanDisponibles) {
        inicializarParametrosPeriodo(periodo, undefined, escalaProgramaActual.value, props.ejes);
        nextTick(() => {
            document.getElementById(`buscar-uc-${trayecto.uid}-${periodo.numero}`)?.focus({ preventScroll: true });
        });
    } else {
        periodo.busquedaActiva = false;
    }
};

/**
 * Quita una unidad curricular de la tabla del Semestre/Período.
 */
const eliminarUnidadDePeriodo = (periodo: PeriodoContainer, index: number) => {
    const quitada = periodo.unidades[index];
    periodo.unidades.splice(index, 1);
    if (quitada) {
        emitirNotificacionLocal(
            'Unidad Retirada',
            `Se quitó [${quitada.codigo}] ${quitada.nombre} de ${periodo.etiqueta}.`,
            'info'
        );
    }
};

/**
 * Carga sugerida opcional desde el endpoint del Programa y la distribuye en la estructura de Trayectos y Semestres.
 */
const autocompletarDesdeCatalogoPnf = async () => {
    if (!form.prg_id) return;
    cargandoSugerencia.value = true;
    try {
        let endpoint = `/academico/malla-curricular/pnf/${form.prg_id}/unidades`;
        try {
            if (typeof route === 'function') {
                endpoint = route('academico.malla-curricular.unidades-pnf', { id: form.prg_id });
            }
        } catch {
            endpoint = `/academico/malla-curricular/pnf/${form.prg_id}/unidades`;
        }

        const { data } = await axios.get(endpoint);
        const bloques = data.bloques || [];
        if (!bloques.length) return;

        // Conservar TODOS los trayectos y materias que el usuario ya haya agregado en pantalla
        const mapaTrayectos = new Map<number, TrayectoBuilderBlock>();
        trayectos.value.forEach((trActual) => {
            mapaTrayectos.set(trActual.numero_orden_tra, trActual);
        });

        const tlaId = tipoLapsoGlobalId.value || tipoLapsoDefaultId.value;
        const cfg = obtenerConfigTipoPeriodo(tlaId);
        let agregadasCount = 0;

        const codigoPrograma = obtenerCodigoPrograma(programaSeleccionadoObj.value);
        const prefix = codigoPrograma ? codigoPrograma.substring(0, 3).toUpperCase() : null;

        // Asignar en cada Trayecto y Semestre (Semestre 1, 2, 3, 4, 5, 6, 7, 8) las unidades correspondientes a ese semestre
        Array.from(mapaTrayectos.values()).forEach((trBlock) => {
            trBlock.periodos.forEach((perContainer) => {
                // Buscar unidades en el catálogo que correspondan a este semestreGlobal (1..8)
                const candidatasSemestre = props.unidadesCatalogo.filter((uCat) => {
                    const coincideSemestre = Number(uCat.semestre_unc) === Number(perContainer.semestreGlobal);
                    const coincidePnf = !prefix || uCat.codigo_unc.toUpperCase().startsWith(prefix);
                    return coincideSemestre && coincidePnf;
                });

                candidatasSemestre.forEach((uCat) => {
                    const yaExisteEnMalla = Array.from(mapaTrayectos.values()).some((t) =>
                        t.periodos.some((p) =>
                            p.unidades.some((ex) => Number(ex.unc_id) === Number(uCat.id_unc))
                        )
                    );
                    if (!yaExisteEnMalla) {
                        const durSem = uCat.duracion_semestres === 2 ? 2 : 1;
                        perContainer.unidades.push({
                            unc_id: uCat.id_unc,
                            codigo: uCat.codigo_unc,
                            nombre: uCat.nombre_unc,
                            tipo: uCat.tipo || 'Regular',
                            tuc_id: uCat.tuc_id ?? null,
                            ecu_id: uCat.ecu_id ?? (props.ejes[0]?.id_ecu ?? null),
                            esm_id: escalaProgramaActual.value.esm_id,
                            uc: Number(uCat.uc) || 3,
                            htea: Number(uCat.htea) || 3,
                            htei: Number(uCat.htei) || 3,
                            thte: Number(uCat.thte) || 6,
                            duracion_semestres: durSem,
                            duracion_semanas: (cfg.duracion || 18) * durSem,
                            nota_minima_aprobatoria_mcd:
                                uCat.nota_aprobatoria ?? escalaProgramaActual.value.notaAprobatoria,
                        });
                        agregadasCount++;
                    }
                });
            });
        });

        trayectos.value = Array.from(mapaTrayectos.values()).sort(
            (a, b) => a.numero_orden_tra - b.numero_orden_tra
        );
        emitirNotificacionLocal(
            '¡Materias Sugeridas Seleccionadas!',
            `Se conservaron tus ${trayectos.value.length} trayecto(s) y se asignaron ${agregadasCount} materia(s) según su semestre correspondiente.`
        );
    } finally {
        cargandoSugerencia.value = false;
    }
};

// Autocompletar sugerencia de Nombre de Malla si está vacío al seleccionar Programa
watch(
    () => form.prg_id,
    (val) => {
        if (!val) return;
        const prog = listaProgramas.value.find((p) => obtenerIdPrograma(p) === Number(val));
        if (prog && !form.nombre_mac.trim()) {
            form.nombre_mac = `Malla Curricular ${obtenerNombrePrograma(prog)}`;
        }
    }
);

/**
 * Lista plana de todas las unidades agregadas en todos los Trayectos y Semestres,
 * lista para enviarse al backend (`unidades_seleccionadas`).
 */
const unidadesPlanas = computed(() => {
    const lista: any[] = [];
    trayectos.value.forEach((tr) => {
        const cantPeriodos = tr.periodos.length || 2;
        tr.periodos.forEach((per) => {
            // Calcular el número de semestre/período correlativo y la etapa curricular
            const semestreGlobal =
                tr.numero_orden_tra <= 0
                    ? 0
                    : (tr.numero_orden_tra - 1) * cantPeriodos + per.numero;
            const etapa = tr.numero_orden_tra <= 2 ? 'TSU' : 'Ingeniería';

            per.unidades.forEach((u) => {
                lista.push({
                    unc_id: u.unc_id,
                    codigo: u.codigo,
                    nombre: u.nombre,
                    tipo: u.tipo,
                    tuc_id: u.tuc_id ?? null,
                    ecu_id: u.ecu_id ?? null,
                    esm_id: u.esm_id ?? escalaProgramaActual.value.esm_id,
                    numero_orden_tra: tr.numero_orden_tra,
                    tla_id: tr.tla_id,
                    semestre: semestreGlobal > 0 ? semestreGlobal : per.numero,
                    periodo_local: per.numero,
                    etapa,
                    uc: Number(u.uc) || 3,
                    htea: Math.max(0, Number(u.htea) || 0),
                    htei: Math.max(0, Number(u.htei) || 0),
                    thte: Math.max(0, Number(u.thte) || 0),
                    duracion_semestres: u.duracion_semestres === 2 ? 2 : 1,
                    duracion_semanas: Number(u.duracion_semanas) || 18,
                    nota_minima_aprobatoria_mcd:
                        u.nota_minima_aprobatoria_mcd !== null && u.nota_minima_aprobatoria_mcd !== undefined
                            ? Number(u.nota_minima_aprobatoria_mcd)
                            : escalaProgramaActual.value.notaAprobatoria,
                    incluido: true,
                });
            });
        });
    });
    return lista;
});

// Recalcular automáticamente los totales de TSU e Ingeniería al agregar/quitar unidades
const resumenTotales = computed(() => {
    const tsu = unidadesPlanas.value.filter((u) => u.etapa === 'TSU');
    const todas = unidadesPlanas.value;

    const creditosTsu = tsu.reduce((acc, u) => acc + (Number(u.uc) || 0), 0);
    const horasTsu = tsu.reduce((acc, u) => acc + (Number(u.thte) || 0), 0);
    const creditosIng = todas.reduce((acc, u) => acc + (Number(u.uc) || 0), 0);
    const horasIng = todas.reduce((acc, u) => acc + (Number(u.thte) || 0), 0);

    return {
        materiasTsu: tsu.length,
        creditosTsu,
        horasTsu,
        materiasIng: todas.length,
        creditosIng,
        horasIng,
        creditosProsecucion: Math.max(0, creditosIng - creditosTsu),
    };
});

watch(
    resumenTotales,
    (tot) => {
        form.total_credito_tsu_mac = tot.creditosTsu;
        form.total_horas_tsu_mac = tot.horasTsu;
        form.total_unidades_tsu_mac = tot.materiasTsu;
        form.total_credito_ing_mac = tot.creditosIng;
        form.total_horas_ing_mac = tot.horasIng;
        form.total_unidades_ing_mac = tot.materiasIng;
        form.total_credito_prosecucion_mac = tot.creditosProsecucion;
    },
    { immediate: true }
);

const submit = () => {
    const lista = unidadesPlanas.value;
    const invalida = lista.find((u) => !esNotaValida(u.nota_minima_aprobatoria_mcd));
    if (invalida) {
        toast.error('Nota aprobatoria fuera de rango', {
            description: `[${invalida.codigo}] tiene una nota (${invalida.nota_minima_aprobatoria_mcd}) fuera del rango ${escalaProgramaActual.value.min}–${escalaProgramaActual.value.max}.`,
        });
        return;
    }

    form.unidades_seleccionadas = lista;
    form.post(route('academico.malla-curricular.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Crear Malla Curricular" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 px-4 pt-4 md:px-6 md:pt-6 pb-24 max-w-6xl mx-auto w-full">
            <!-- Encabezado Superior -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                        <Network class="h-6 w-6 text-primary" />
                        Crear Malla Curricular
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Diseñe la estructura curricular del PNF configurando sus datos generales, trayectos, tipo de período y asignaturas por semestre.
                    </p>
                </div>

                <Button variant="outline" as-child class="gap-2 self-start sm:self-auto">
                    <Link :href="route('academico.malla-curricular.index')">
                        <ArrowLeft class="h-4 w-4" />
                        Volver al Listado
                    </Link>
                </Button>
            </div>

            <!-- Alerta de Error General -->
            <div
                v-if="(form.errors as any).error"
                class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-destructive flex items-center gap-3 shadow-sm"
            >
                <AlertCircle class="h-5 w-5 shrink-0" />
                <p class="text-sm font-medium">{{ (form.errors as any).error }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- ========================================================= -->
                <!-- SECCIÓN 1: CABECERA Y DATOS GENERALES ("Crear Malla")     -->
                <!-- ========================================================= -->
                <Card class="shadow-sm border-border/60">
                    <CardHeader class="border-b bg-muted/20 pb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                    <BookOpen class="h-5 w-5 text-primary" />
                                    1. Cabecera y Datos Generales
                                </CardTitle>
                                <CardDescription>
                                    Identificación del Programa (PNF), nombre del plan de estudio, versión del pensum, cohorte y fecha.
                                </CardDescription>
                            </div>
                            <Badge variant="outline" class="self-start sm:self-auto font-mono text-xs">
                                Total Asignaturas: {{ resumenTotales.materiasIng }} | {{ resumenTotales.creditosIng }} U.C.
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent class="pt-6 space-y-5">
                        <!-- Fila 1: Programa / PNF y Nombre de la Malla -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <Label for="prg_id" class="font-semibold">
                                    Programa / PNF <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="prg_id"
                                    v-model="form.prg_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    :class="{ 'border-destructive': form.errors.prg_id || (form.errors as any).pnf_id }"
                                    required
                                >
                                    <option value="" disabled>Seleccione un Programa / PNF...</option>
                                    <option
                                        v-for="prog in listaProgramas"
                                        :key="obtenerIdPrograma(prog)"
                                        :value="obtenerIdPrograma(prog)"
                                    >
                                        {{ obtenerCodigoPrograma(prog) ? `[${obtenerCodigoPrograma(prog)}] ` : '' }}{{ obtenerNombrePrograma(prog) }}
                                    </option>
                                </select>
                                <p v-if="form.errors.prg_id || (form.errors as any).pnf_id" class="text-xs text-destructive font-medium">
                                    {{ form.errors.prg_id || (form.errors as any).pnf_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_mac" class="font-semibold">
                                    Nombre de la Malla <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_mac"
                                    v-model="form.nombre_mac"
                                    type="text"
                                    placeholder="Ej. Malla Curricular PNF en Informática"
                                    :class="{ 'border-destructive': form.errors.nombre_mac }"
                                    required
                                />
                                <p v-if="form.errors.nombre_mac" class="text-xs text-destructive font-medium">
                                    {{ form.errors.nombre_mac }}
                                </p>
                            </div>
                        </div>

                        <!-- Fila 2: Versión del Pensum, Número de Cohorte y Fecha -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-2">
                                <Label for="version_pensum_mac" class="font-semibold">
                                    Versión del Pensum
                                </Label>
                                <Input
                                    id="version_pensum_mac"
                                    v-model="form.version_pensum_mac"
                                    type="text"
                                    placeholder="Ej. v1.0 / Reforma 2026"
                                    :class="{ 'border-destructive': form.errors.version_pensum_mac }"
                                />
                                <p v-if="form.errors.version_pensum_mac" class="text-xs text-destructive font-medium">
                                    {{ form.errors.version_pensum_mac }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="codigo_gaceta_mac" class="font-semibold">
                                    Número de Cohorte / Gaceta
                                </Label>
                                <Input
                                    id="codigo_gaceta_mac"
                                    v-model="form.codigo_gaceta_mac"
                                    list="cohortes-sugeridas"
                                    type="text"
                                    placeholder="Ej. Cohorte 2026-1 / Gaceta N° 40.123"
                                    :class="{ 'border-destructive': form.errors.codigo_gaceta_mac }"
                                />
                                <datalist id="cohortes-sugeridas">
                                    <option
                                        v-for="c in cohortes"
                                        :key="c.id_coh"
                                        :value="`Cohorte ${c.nombre_coh}`"
                                    />
                                </datalist>
                                <p v-if="form.errors.codigo_gaceta_mac" class="text-xs text-destructive font-medium">
                                    {{ form.errors.codigo_gaceta_mac }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="fecha_aprobacion_mac" class="font-semibold flex items-center gap-1.5">
                                    <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                    Fecha
                                </Label>
                                <Input
                                    id="fecha_aprobacion_mac"
                                    v-model="form.fecha_aprobacion_mac"
                                    type="date"
                                    :class="{ 'border-destructive': form.errors.fecha_aprobacion_mac }"
                                />
                                <p v-if="form.errors.fecha_aprobacion_mac" class="text-xs text-destructive font-medium">
                                    {{ form.errors.fecha_aprobacion_mac }}
                                </p>
                            </div>
                        </div>

                        <!-- Fila 3: Observaciones -->
                        <div class="space-y-2">
                            <Label for="observacion_mac" class="font-semibold">
                                Observaciones
                            </Label>
                            <textarea
                                id="observacion_mac"
                                v-model="form.observacion_mac"
                                rows="2"
                                placeholder="Notas adicionales del comité curricular o resolución..."
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            ></textarea>
                        </div>
                    </CardContent>
                </Card>

                <!-- ========================================================= -->
                <!-- SECCIÓN 2: CONFIGURACIÓN DINÁMICA DE TRAYECTOS            -->
                <!-- ========================================================= -->
                <Card class="shadow-sm border-border/60">
                    <CardHeader class="border-b bg-muted/20 pb-4">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-semibold flex items-center gap-2">
                                    <Layers class="h-5 w-5 text-primary" />
                                    2. Configuración Dinámica de Trayectos y Períodos
                                </CardTitle>
                                <CardDescription>
                                    Agregue los trayectos del plan de estudio, defina el tipo de período (Semestres, Trimestres o Años) e inserte las unidades curriculares en cada bloque.
                                </CardDescription>
                            </div>

                            <!-- Controles del Croquis: [Agregar Trayecto] y [Agregar Tipo Periodo] -->
                            <div class="flex flex-wrap items-center gap-2.5">
                                <!-- Selector [Agregar Tipo Periodo] -->
                                <div class="flex items-center gap-2 bg-background border rounded-md px-2.5 py-1 shadow-xs">
                                    <Label for="tipo_periodo_global" class="text-xs font-semibold text-muted-foreground whitespace-nowrap">
                                        Tipo Período:
                                    </Label>
                                    <select
                                        id="tipo_periodo_global"
                                        v-model="tipoLapsoGlobalId"
                                        @change="aplicarTipoPeriodoGlobal"
                                        class="h-8 bg-transparent text-xs font-medium focus:outline-none pr-2"
                                    >
                                        <option
                                            v-for="tp in catalogoTiposLapso"
                                            :key="obtenerIdTipoLapso(tp)"
                                            :value="obtenerIdTipoLapso(tp)"
                                        >
                                            {{ obtenerNomenclaturaTipoLapso(tp) || obtenerNombreTipoLapso(tp) }} ({{ obtenerDuracionTipoLapso(tp) || 18 }} sem.)
                                        </option>
                                    </select>
                                </div>

                                <!-- Botón [+ Agregar Trayecto] -->
                                <Button
                                    type="button"
                                    variant="default"
                                    size="sm"
                                    class="gap-1.5 font-semibold"
                                    @click="agregarTrayecto()"
                                >
                                    <Plus class="h-4 w-4" />
                                    Agregar Trayecto
                                </Button>

                                <!-- Botón opcional para Trayecto Inicial -->
                                <Button
                                    v-if="!trayectos.some((t) => t.numero_orden_tra === 0)"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="gap-1.5 text-xs"
                                    @click="agregarTrayecto(0)"
                                >
                                    <Plus class="h-3.5 w-3.5" />
                                    Trayecto Inicial
                                </Button>

                                <!-- Autocompletar opcional desde el catálogo del Programa -->
                                <Button
                                    v-if="form.prg_id"
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    class="gap-1.5 text-xs"
                                    :disabled="cargandoSugerencia"
                                    @click="autocompletarDesdeCatalogoPnf"
                                >
                                    <Loader2 v-if="cargandoSugerencia" class="h-3.5 w-3.5 animate-spin" />
                                    <Sparkles v-else class="h-3.5 w-3.5 text-amber-500" />
                                    Cargar Materias Sugeridas del Programa
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- ========================================================= -->
                    <!-- SECCIÓN 3: DETALLE DE UNIDADES POR TRAYECTO Y SEMESTRE    -->
                    <!-- ========================================================= -->
                    <CardContent class="pt-6 space-y-6 min-w-0">
                        <!-- Banner de Confirmación Instantánea al Agregar Trayecto / Período / Unidad -->
                        <div
                            v-if="notificacionAccion"
                            class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-emerald-700 dark:text-emerald-300 flex items-center justify-between gap-3 shadow-xs transition-all"
                        >
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-semibold">
                                <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                <span>{{ notificacionAccion }}</span>
                            </div>
                            <button
                                type="button"
                                class="text-emerald-700/70 hover:text-emerald-800 dark:text-emerald-300"
                                @click="notificacionAccion = null"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <div
                            v-if="trayectos.length === 0"
                            class="rounded-lg border border-dashed p-10 text-center text-muted-foreground space-y-3"
                        >
                            <Layers class="h-9 w-9 mx-auto text-muted-foreground/50" />
                            <p class="text-sm font-medium">
                                No hay trayectos agregados todavía. Haga clic en <strong>"+ Agregar Trayecto"</strong> para comenzar.
                            </p>
                        </div>

                        <!-- Bloques de Trayecto (Trayecto I, Trayecto II, etc.) -->
                        <div
                            v-for="trayecto in trayectos"
                            :id="trayecto.uid"
                            :key="trayecto.uid"
                            class="rounded-xl border-2 bg-card shadow-xs transition-all duration-300 min-w-0"
                            :class="
                                ultimoTrayectoUid === trayecto.uid
                                    ? 'border-emerald-500 ring-2 ring-emerald-500/30'
                                    : 'border-border/80'
                            "
                        >
                            <!-- Cabecera del Trayecto -->
                            <div class="flex flex-wrap items-center justify-between gap-3 bg-muted/40 px-4 py-3 border-b rounded-t-[10px]">
                                <div class="flex items-center gap-3">
                                    <Badge variant="default" class="text-xs px-2.5 py-1 uppercase tracking-wider font-bold">
                                        {{ trayecto.nombre_tra }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground font-medium">
                                        Etapa:
                                        <strong class="text-foreground">
                                            {{ trayecto.numero_orden_tra <= 2 ? 'TSU' : 'Ingeniería / Licenciatura' }}
                                        </strong>
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <!-- Selector de Tipo Período individual del Trayecto -->
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="text-muted-foreground font-medium">División:</span>
                                        <select
                                            :value="trayecto.tla_id"
                                            @change="cambiarTipoPeriodoTrayecto(trayecto, Number(($event.target as HTMLSelectElement).value))"
                                            class="h-7 rounded border border-input bg-background px-2 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-ring"
                                        >
                                            <option
                                                v-for="tp in catalogoTiposLapso"
                                                :key="obtenerIdTipoLapso(tp)"
                                                :value="obtenerIdTipoLapso(tp)"
                                            >
                                                {{ obtenerNomenclaturaTipoLapso(tp) || obtenerNombreTipoLapso(tp) }}
                                            </option>
                                        </select>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-7 px-2 text-destructive hover:text-destructive hover:bg-destructive/10 gap-1 text-xs"
                                        @click="eliminarTrayecto(trayecto.uid)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                        Quitar Trayecto
                                    </Button>
                                </div>
                            </div>

                            <!-- Contenedores de Períodos (Semestre 1 / Semestre 2) tal como en el croquis -->
                            <div
                                class="p-4 grid gap-4 min-w-0"
                                :class="{
                                    'grid-cols-1': trayecto.periodos.length === 1,
                                    'grid-cols-1 xl:grid-cols-2': trayecto.periodos.length === 2,
                                    'grid-cols-1 xl:grid-cols-3': trayecto.periodos.length >= 3,
                                }"
                            >
                                <div
                                    v-for="periodo in trayecto.periodos"
                                    :key="periodo.numero"
                                    class="rounded-lg border border-border bg-background flex flex-col justify-between min-w-0 shadow-2xs"
                                >
                                    <div class="min-w-0">
                                        <!-- Encabezado del Semestre / Período + Botón [Agregar Unidad] -->
                                        <div class="flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 bg-muted/25 border-b rounded-t-lg min-w-0">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="font-semibold text-sm text-foreground">
                                                    {{ periodo.etiqueta }}
                                                </span>
                                                <Badge variant="secondary" class="text-[11px]">
                                                    {{ periodo.unidades.length }} {{ periodo.unidades.length === 1 ? 'unidad' : 'unidades' }}
                                                </Badge>
                                            </div>

                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-7 text-xs gap-1 font-semibold border-primary/40 text-primary hover:bg-primary/10 shrink-0"
                                                @click="abrirSelectorUnidad(trayecto, periodo)"
                                            >
                                                <Plus class="h-3.5 w-3.5" />
                                                Agregar Unidad
                                            </Button>
                                        </div>

                                        <!-- Buscador / Selector Inline al pulsar [+ Agregar Unidad] -->
                                        <div
                                            v-if="periodo.busquedaActiva"
                                            :id="`selector-${trayecto.uid}-${periodo.numero}`"
                                            class="p-3 bg-primary/5 border-b border-primary/20 space-y-2.5 min-w-0 scroll-mb-24"
                                        >
                                            <div class="flex items-start justify-between gap-2 min-w-0">
                                                <span class="text-xs font-semibold text-primary flex items-center gap-1.5 min-w-0 leading-snug">
                                                    <Search class="h-3.5 w-3.5 shrink-0" />
                                                    <span>Seleccionar Unidad Curricular para {{ periodo.etiqueta }}</span>
                                                </span>
                                            </div>

                                            <div class="flex flex-col gap-3 min-w-0">
                                                <div class="space-y-1 min-w-0">
                                                    <label
                                                        :for="`buscar-uc-${trayecto.uid}-${periodo.numero}`"
                                                        class="block text-[11px] font-medium text-muted-foreground"
                                                    >
                                                        Unidad Curricular
                                                    </label>
                                                    <UnidadCurricularCombobox
                                                        :input-id="`buscar-uc-${trayecto.uid}-${periodo.numero}`"
                                                        v-model="periodo.uncIdSeleccionada"
                                                        v-model:query="periodo.terminoBusqueda"
                                                        :catalogo="props.unidadesCatalogo"
                                                        :excluded-ids="idsUnidadesYaEnMalla"
                                                        :semestre-global="periodo.semestreGlobal"
                                                        :codigo-prefix="codigoProgramaPrefix"
                                                        @select="(item) => onSeleccionarUnidadCatalogo(periodo, item)"
                                                    />
                                                </div>

                                                <!-- Parámetros Académicos de la Unidad en esta Malla -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 min-w-0">
                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`eje-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            Eje
                                                        </label>
                                                        <select
                                                            :id="`eje-${trayecto.uid}-${periodo.numero}`"
                                                            v-model="periodo.ecuIdSeleccionado"
                                                            class="h-8 w-full max-w-full min-w-0 rounded-md border border-input bg-background px-2 text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                                        >
                                                            <option value="">Sin eje</option>
                                                            <option
                                                                v-for="eje in props.ejes"
                                                                :key="eje.id_ecu"
                                                                :value="eje.id_ecu"
                                                            >
                                                                {{ eje.nombre_ecu }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`hea-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            HEA
                                                        </label>
                                                        <Input
                                                            :id="`hea-${trayecto.uid}-${periodo.numero}`"
                                                            v-model.number="periodo.hteaSeleccionado"
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            class="h-8 w-full max-w-full min-w-0 text-xs bg-background"
                                                            @input="recalcularHtePeriodo(periodo)"
                                                        />
                                                    </div>

                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`hei-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            HEI
                                                        </label>
                                                        <Input
                                                            :id="`hei-${trayecto.uid}-${periodo.numero}`"
                                                            v-model.number="periodo.hteiSeleccionado"
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            class="h-8 w-full max-w-full min-w-0 text-xs bg-background"
                                                            @input="recalcularHtePeriodo(periodo)"
                                                        />
                                                    </div>

                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`hte-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            HTE (HEA + HEI)
                                                        </label>
                                                        <Input
                                                            :id="`hte-${trayecto.uid}-${periodo.numero}`"
                                                            v-model.number="periodo.thteSeleccionado"
                                                            type="number"
                                                            min="0"
                                                            step="1"
                                                            class="h-8 w-full max-w-full min-w-0 text-xs font-semibold bg-background"
                                                        />
                                                    </div>

                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`duracion-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            Duración
                                                        </label>
                                                        <select
                                                            :id="`duracion-${trayecto.uid}-${periodo.numero}`"
                                                            v-model.number="periodo.duracionSemestresSeleccionada"
                                                            class="h-8 w-full max-w-full min-w-0 rounded-md border border-input bg-background px-2 text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                                        >
                                                            <option :value="1">1 Semestre</option>
                                                            <option :value="2">2 Semestres</option>
                                                        </select>
                                                    </div>

                                                    <div class="space-y-1 min-w-0">
                                                        <label
                                                            :for="`na-${trayecto.uid}-${periodo.numero}`"
                                                            class="block text-[11px] font-medium text-muted-foreground"
                                                        >
                                                            N.A. (Nota Aprob.)
                                                        </label>
                                                        <Input
                                                            :id="`na-${trayecto.uid}-${periodo.numero}`"
                                                            v-model="periodo.notaAprobatoriaSeleccionada"
                                                            type="number"
                                                            :min="escalaProgramaActual.min"
                                                            :max="escalaProgramaActual.max"
                                                            step="0.5"
                                                            :aria-invalid="Boolean(mensajeErrorNotaPeriodo(periodo))"
                                                            :class="[
                                                                'h-8 w-full max-w-full min-w-0 text-xs bg-background',
                                                                mensajeErrorNotaPeriodo(periodo) ? 'border-destructive focus-visible:ring-destructive/30' : '',
                                                            ]"
                                                        />
                                                        <p
                                                            v-if="mensajeErrorNotaPeriodo(periodo)"
                                                            class="text-[10px] text-destructive leading-tight"
                                                        >
                                                            {{ mensajeErrorNotaPeriodo(periodo) }}
                                                        </p>
                                                        <p
                                                            v-else
                                                            class="text-[10px] text-muted-foreground leading-tight"
                                                        >
                                                            Escala {{ escalaProgramaActual.min }}–{{ escalaProgramaActual.max }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center justify-end gap-2 pt-0.5">
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        class="h-8 px-2.5 text-xs gap-1"
                                                        @click="periodo.busquedaActiva = false"
                                                    >
                                                        <X class="h-3.5 w-3.5" />
                                                        Cancelar
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        size="sm"
                                                        class="h-8 px-3 text-xs gap-1 shrink-0"
                                                        :disabled="!periodo.uncIdSeleccionada || Boolean(mensajeErrorNotaPeriodo(periodo))"
                                                        @click="confirmarAgregarUnidad(trayecto, periodo)"
                                                    >
                                                        <Check class="h-3.5 w-3.5" />
                                                        Insertar
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tabla de Materias del Semestre (N°, Asignatura, Eje, UC, HTE (HEA/HEI), Duración, N.A.) -->
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="border-b bg-muted/15 text-muted-foreground">
                                                        <th class="py-2 px-2.5 text-left font-semibold w-8">#</th>
                                                        <th class="py-2 px-2.5 text-left font-semibold min-w-[140px]">Asignatura</th>
                                                        <th class="py-2 px-2 text-left font-semibold min-w-[115px]">Eje</th>
                                                        <th class="py-2 px-1.5 text-center font-semibold w-14">U.C.</th>
                                                        <th class="py-2 px-2 text-center font-semibold min-w-[138px]">HTE (HEA/HEI)</th>
                                                        <th class="py-2 px-1.5 text-center font-semibold w-20">Duración</th>
                                                        <th class="py-2 px-1.5 text-center font-semibold w-16">N.A.</th>
                                                        <th class="py-2 px-2 text-right font-semibold w-9"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y">
                                                    <tr
                                                        v-if="periodo.unidades.length === 0"
                                                        class="text-muted-foreground"
                                                    >
                                                        <td colspan="8" class="py-6 text-center italic">
                                                            Sin unidades asignadas en {{ periodo.etiqueta }}. Pulse
                                                            <strong>"+ Agregar Unidad"</strong>.
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(unidad, idx) in periodo.unidades"
                                                        :key="unidad.unc_id"
                                                        class="hover:bg-muted/20 transition-colors"
                                                    >
                                                        <td class="py-2 px-2.5 font-mono font-semibold text-muted-foreground">
                                                            {{ idx + 1 }}
                                                        </td>
                                                        <td class="py-2 px-2.5">
                                                            <div class="font-mono text-[11px] font-semibold text-primary">
                                                                {{ unidad.codigo }}
                                                            </div>
                                                            <div class="font-medium text-foreground leading-snug">
                                                                {{ unidad.nombre }}
                                                            </div>
                                                        </td>
                                                        <td class="py-1.5 px-2">
                                                            <select
                                                                v-model="unidad.ecu_id"
                                                                aria-label="Eje curricular de la unidad"
                                                                class="h-7 w-full min-w-[110px] rounded border border-input bg-background px-1.5 text-[11px] focus:outline-none focus:ring-1 focus:ring-ring"
                                                            >
                                                                <option :value="null">Sin eje</option>
                                                                <option
                                                                    v-for="eje in props.ejes"
                                                                    :key="eje.id_ecu"
                                                                    :value="eje.id_ecu"
                                                                >
                                                                    {{ eje.nombre_ecu }}
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td class="py-1.5 px-1.5 text-center">
                                                            <input
                                                                v-model.number="unidad.uc"
                                                                type="number"
                                                                min="0"
                                                                max="20"
                                                                aria-label="Unidades de crédito"
                                                                class="w-11 h-7 text-center rounded border border-input bg-background text-xs font-medium"
                                                            />
                                                        </td>
                                                        <td class="py-1.5 px-2">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <input
                                                                    v-model.number="unidad.htea"
                                                                    type="number"
                                                                    min="0"
                                                                    title="HEA"
                                                                    aria-label="Horas de estudio acompañado"
                                                                    class="w-10 h-7 text-center rounded border border-input bg-background text-[11px]"
                                                                    @input="recalcularHteUnidad(unidad)"
                                                                />
                                                                <span class="text-muted-foreground text-[10px]">+</span>
                                                                <input
                                                                    v-model.number="unidad.htei"
                                                                    type="number"
                                                                    min="0"
                                                                    title="HEI"
                                                                    aria-label="Horas de estudio independiente"
                                                                    class="w-10 h-7 text-center rounded border border-input bg-background text-[11px]"
                                                                    @input="recalcularHteUnidad(unidad)"
                                                                />
                                                                <span class="text-muted-foreground text-[10px]">=</span>
                                                                <input
                                                                    v-model.number="unidad.thte"
                                                                    type="number"
                                                                    min="0"
                                                                    title="HTE total"
                                                                    aria-label="Horas totales del estudiante"
                                                                    class="w-10 h-7 text-center rounded border border-primary/40 bg-primary/5 text-[11px] font-bold text-primary"
                                                                />
                                                            </div>
                                                        </td>
                                                        <td class="py-1.5 px-1.5 text-center">
                                                            <select
                                                                v-model.number="unidad.duracion_semestres"
                                                                aria-label="Duración en semestres"
                                                                class="h-7 rounded border border-input bg-background px-1.5 text-[11px] font-medium focus:outline-none focus:ring-1 focus:ring-ring"
                                                                @change="onCambiarDuracionFila(trayecto, unidad)"
                                                            >
                                                                <option :value="1">1 Sem</option>
                                                                <option :value="2">2 Sem</option>
                                                            </select>
                                                        </td>
                                                        <td class="py-1.5 px-1.5 text-center">
                                                            <input
                                                                v-model.number="unidad.nota_minima_aprobatoria_mcd"
                                                                type="number"
                                                                :min="escalaProgramaActual.min"
                                                                :max="escalaProgramaActual.max"
                                                                step="0.5"
                                                                aria-label="Nota aprobatoria"
                                                                :class="[
                                                                    'w-13 h-7 text-center rounded border bg-background text-xs font-medium',
                                                                    !esNotaValida(unidad.nota_minima_aprobatoria_mcd)
                                                                        ? 'border-destructive text-destructive'
                                                                        : 'border-input',
                                                                ]"
                                                            />
                                                        </td>
                                                        <td class="py-1.5 px-2 text-right">
                                                            <button
                                                                type="button"
                                                                class="text-muted-foreground hover:text-destructive p-1 rounded"
                                                                title="Quitar asignatura"
                                                                @click="eliminarUnidadDePeriodo(periodo, idx)"
                                                            >
                                                                <Trash2 class="h-3.5 w-3.5" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- ========================================================= -->
                <!-- RESUMEN DE REQUISITOS DE EGRESO (AUTOCALCULADOS)          -->
                <!-- ========================================================= -->
                <Card class="shadow-sm border-border/60">
                    <CardHeader class="py-3 px-5 bg-muted/15 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm font-semibold">
                                <GraduationCap class="h-4 w-4 text-primary" />
                                Requisitos Globales de Egreso (Autocalculados según las unidades agregadas)
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="text-xs h-7"
                                @click="mostrarRequisitosEgreso = !mostrarRequisitosEgreso"
                            >
                                {{ mostrarRequisitosEgreso ? 'Ocultar Detalles' : 'Editar / Ver Requisitos' }}
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent v-if="mostrarRequisitosEgreso" class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 rounded-lg border p-3.5 bg-blue-500/5 border-blue-500/20">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 flex items-center gap-1.5">
                                <GraduationCap class="h-4 w-4" /> Salida Intermedia TSU
                            </h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <Label class="text-xs">Créditos TSU</Label>
                                    <Input v-model="form.total_credito_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Horas TSU</Label>
                                    <Input v-model="form.total_horas_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Materias TSU</Label>
                                    <Input v-model="form.total_unidades_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 rounded-lg border p-3.5 bg-amber-500/5 border-amber-500/20">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                                <Award class="h-4 w-4" /> Ingeniería / Licenciatura
                            </h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <Label class="text-xs">Créditos Ing.</Label>
                                    <Input v-model="form.total_credito_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Horas Ing.</Label>
                                    <Input v-model="form.total_horas_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Materias Ing.</Label>
                                    <Input v-model="form.total_unidades_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('academico.malla-curricular.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
