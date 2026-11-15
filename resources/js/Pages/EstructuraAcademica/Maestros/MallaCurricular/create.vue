<script setup lang="ts">
/**
 * Vista Standalone: Creación de Malla Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite registrar una nueva Malla Curricular (plan de estudios) mediante useForm de Inertia,
 * cargando de forma reactiva las unidades curriculares del PNF seleccionado, estructuradas por trayectos y semestres,
 * e incorporando los requisitos globales de egreso para TSU e Ingeniería/Licenciatura.
 *
 * Actualización 2026-11-08 (Rediseño Interactivo según Croquis de "Crear Malla"):
 * Se implementó el constructor dinámico de Malla Curricular dividido en:
 * 1. Cabecera y Datos Generales (Programa/PNF, Nombre de la Malla, Versión del Pensum, Número de Cohorte, Fecha y Observaciones).
 * 2. Configuración Dinámica de Trayectos con botón [+ Agregar Trayecto] y selector [Agregar Tipo Periodo] (Semestres, Trimestres o Años).
 * 3. Detalle de Unidades Curriculares por Semestre (Semestre 1 / Semestre 2) con botón [+ Agregar Unidad] y tabla ordenada (N°, Código, Nombre).
 */
import { ref, computed, watch, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Network,
    ArrowLeft,
    Save,
    AlertCircle,
    CheckCircle2,
    GraduationCap,
    Award,
    BookOpen,
    Loader2,
    Layers,
    Sparkles,
    Plus,
    Trash2,
    Calendar,
    Search,
    Check,
    X,
} from 'lucide-vue-next';

interface PnfItem {
    id_pnf: number;
    codigo_pnf?: string | null;
    nombre_pnf: string;
}

interface TipoPeriodoItem {
    id_tpd: number;
    nombre_tpd: string;
    codigo_tpd?: string | null;
    nomenclatura?: string | null;
    duracion_semanas_tpd?: number | null;
}

interface CohorteItem {
    id_coh: number;
    nombre_coh: string;
    fecha_inicio_coh?: string | null;
}

interface UnidadCatalogoItem {
    id_unc: number;
    codigo_unc: string;
    nombre_unc: string;
    tipo: string;
    tuc_id?: number | null;
    tra_id: number;
    semestre_unc: number;
    uc: number;
    htea: number;
    htei: number;
    thte: number;
}

interface UnidadSeleccionadaRow {
    unc_id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    tuc_id?: number | null;
    uc: number;
    htea: number;
    htei: number;
    thte: number;
    duracion_semanas: number;
}

interface PeriodoContainer {
    numero: number; // 1, 2, 3 (índice dentro del trayecto)
    semestreGlobal: number; // 1..8 (Semestre 1 y 2 en Trayecto I, Semestre 3 y 4 en Trayecto II, etc.)
    etiqueta: string; // "Semestre 1", "Semestre 2", "Semestre 3", "Semestre 4", etc.
    busquedaActiva: boolean;
    terminoBusqueda: string;
    uncIdSeleccionada: number | string;
    unidades: UnidadSeleccionadaRow[];
}

interface TrayectoBuilderBlock {
    uid: string;
    numero_orden_tra: number; // 1 = Trayecto I, 2 = Trayecto II, etc.
    nombre_tra: string; // "Trayecto I", "Trayecto II", etc.
    tpd_id: number;
    tipo_periodo_nombre: string; // "Semestre", "Trimestre", "Año"
    periodos: PeriodoContainer[];
}

const props = withDefaults(
    defineProps<{
        pnfs: PnfItem[];
        tiposPeriodo?: TipoPeriodoItem[];
        cohortes?: CohorteItem[];
        unidadesCatalogo?: UnidadCatalogoItem[];
    }>(),
    {
        tiposPeriodo: () => [],
        cohortes: () => [],
        unidadesCatalogo: () => [],
    }
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Malla Curricular', subtitle: 'Planes de Estudio', href: route('academico.malla-curricular.index') },
    { title: 'Crear Malla', subtitle: 'Nuevo Registro', href: route('academico.malla-curricular.create') },
];

const ROMANOS: Record<number, string> = {
    0: 'Inicial',
    1: 'I',
    2: 'II',
    3: 'III',
    4: 'IV',
    5: 'V',
};

// Determinar el Tipo de Período Semestral por defecto
const tipoPeriodoDefaultId = computed<number>(() => {
    const semestral = props.tiposPeriodo.find(
        (t) =>
            (t.nomenclatura && t.nomenclatura.toLowerCase().includes('semestr')) ||
            t.nombre_tpd.toLowerCase().includes('semestr')
    );
    return semestral ? semestral.id_tpd : props.tiposPeriodo[0]?.id_tpd ?? 4;
});

const tipoPeriodoGlobalId = ref<number>(tipoPeriodoDefaultId.value);
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
    pnf_id: '' as string | number,

    // Requisitos globales TSU
    creditos_minimos_tsu_mac: '' as string | number,
    horas_minimas_tsu_mac: '' as string | number,
    materias_obligatorias_tsu_mac: '' as string | number,

    // Requisitos globales Ingeniería
    creditos_minimos_ing_mac: '' as string | number,
    horas_minimas_ing_mac: '' as string | number,
    materias_obligatorias_ing_mac: '' as string | number,
    creditos_minimos_prosecucion_mac: '' as string | number,

    unidades_seleccionadas: [] as any[],
});

/**
 * Obtiene la configuración (cantidad de sub-períodos y etiqueta) según el Tipo de Período (`tpd_id`).
 */
const obtenerConfigTipoPeriodo = (tpdId: number) => {
    const item = props.tiposPeriodo.find((t) => Number(t.id_tpd) === Number(tpdId));
    const texto = `${item?.nomenclatura ?? ''} ${item?.nombre_tpd ?? ''}`.toLowerCase();

    if (texto.includes('trimestr')) {
        return { cantidad: 3, singular: 'Trimestre', duracion: item?.duracion_semanas_tpd || 12 };
    }
    if (texto.includes('anual') || texto.includes('año') || texto.includes('ano')) {
        return { cantidad: 1, singular: 'Año / Período Anual', duracion: item?.duracion_semanas_tpd || 36 };
    }
    return { cantidad: 2, singular: 'Semestre', duracion: item?.duracion_semanas_tpd || 18 };
};

/**
 * Construye los contenedores de períodos con numeración correlativa por Trayecto:
 * - Trayecto I   -> Semestre 1, Semestre 2 (o Trimestre 1, 2, 3 / Año 1)
 * - Trayecto II  -> Semestre 3, Semestre 4 (o Trimestre 4, 5, 6 / Año 2)
 * - Trayecto III -> Semestre 5, Semestre 6 (o Trimestre 7, 8, 9 / Año 3)
 * - Trayecto IV  -> Semestre 7, Semestre 8 (o Trimestre 10, 11, 12 / Año 4)
 */
const construirPeriodosParaTrayecto = (
    tpdId: number,
    numeroOrdenTra = 1,
    periodosPrevios: PeriodoContainer[] = []
): PeriodoContainer[] => {
    const cfg = obtenerConfigTipoPeriodo(tpdId);
    const nuevos: PeriodoContainer[] = [];

    if (numeroOrdenTra <= 0) {
        const previo = periodosPrevios[0];
        return [
            {
                numero: 1,
                semestreGlobal: 0,
                etiqueta: 'Período Inicial',
                busquedaActiva: previo ? previo.busquedaActiva : false,
                terminoBusqueda: '',
                uncIdSeleccionada: '',
                unidades: previo ? [...previo.unidades] : [],
            },
        ];
    }

    for (let i = 1; i <= cfg.cantidad; i++) {
        const previo = periodosPrevios.find((p) => p.numero === i);
        const semestreGlobal = (numeroOrdenTra - 1) * cfg.cantidad + i;
        const etiqueta =
            cfg.cantidad === 1
                ? `Año ${numeroOrdenTra} (Período ${semestreGlobal})`
                : `${cfg.singular} ${semestreGlobal}`;

        nuevos.push({
            numero: i,
            semestreGlobal,
            etiqueta,
            busquedaActiva: previo ? previo.busquedaActiva : false,
            terminoBusqueda: '',
            uncIdSeleccionada: '',
            unidades: previo ? [...previo.unidades] : [],
        });
    }
    return nuevos;
};

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

    const tpdId = tipoPeriodoGlobalId.value || tipoPeriodoDefaultId.value;
    const cfg = obtenerConfigTipoPeriodo(tpdId);
    const etiquetaRomana = ROMANOS[siguienteOrden] ?? String(siguienteOrden);
    const nombreTra = siguienteOrden === 0 ? 'Trayecto Inicial' : `Trayecto ${etiquetaRomana}`;
    const uid = `tra-${siguienteOrden}-${Date.now()}`;
    const periodosCreados = construirPeriodosParaTrayecto(tpdId, siguienteOrden);

    trayectos.value.push({
        uid,
        numero_orden_tra: siguienteOrden,
        nombre_tra: nombreTra,
        tpd_id: tpdId,
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
 * Cambia el Tipo de Período (Semestres, Trimestres, Años) de un Trayecto específico.
 */
const cambiarTipoPeriodoTrayecto = (trayecto: TrayectoBuilderBlock, nuevoTpdId: number, notificar = true) => {
    trayecto.tpd_id = Number(nuevoTpdId);
    const cfg = obtenerConfigTipoPeriodo(trayecto.tpd_id);
    trayecto.tipo_periodo_nombre = cfg.singular;
    trayecto.periodos = construirPeriodosParaTrayecto(trayecto.tpd_id, trayecto.numero_orden_tra, trayecto.periodos);
    if (notificar) {
        const nombresPeriodos = trayecto.periodos.map((p) => p.etiqueta).join(', ');
        emitirNotificacionLocal(
            'Tipo de Período Actualizado',
            `${trayecto.nombre_tra} ahora contiene: ${nombresPeriodos}.`,
            'info'
        );
    }
};

/**
 * Aplica el Tipo de Período global a todos los trayectos creados.
 */
const aplicarTipoPeriodoGlobal = () => {
    const cfg = obtenerConfigTipoPeriodo(tipoPeriodoGlobalId.value);
    trayectos.value.forEach((tr) => {
        cambiarTipoPeriodoTrayecto(tr, tipoPeriodoGlobalId.value, false);
    });
    emitirNotificacionLocal(
        'Tipo de Período Aplicado',
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
 * Prioriza las materias del PNF seleccionado y evita duplicar materias ya insertadas en la malla.
 */
const pnfSeleccionadoObj = computed(() =>
    props.pnfs.find((p) => Number(p.id_pnf) === Number(form.pnf_id))
);

const idsUnidadesYaEnMalla = computed<Set<number>>(() => {
    const set = new Set<number>();
    trayectos.value.forEach((t) => {
        t.periodos.forEach((p) => {
            p.unidades.forEach((u) => set.add(Number(u.unc_id)));
        });
    });
    return set;
});

const obtenerUnidadesDisponiblesParaPeriodo = (periodo: PeriodoContainer) => {
    const q = periodo.terminoBusqueda.trim().toLowerCase();
    const prefix = pnfSeleccionadoObj.value?.codigo_pnf
        ? pnfSeleccionadoObj.value.codigo_pnf.substring(0, 3).toUpperCase()
        : null;

    const disponibles = props.unidadesCatalogo.filter((u) => {
        if (idsUnidadesYaEnMalla.value.has(Number(u.id_unc))) {
            return false;
        }
        if (!q) {
            return true;
        }
        return (
            u.codigo_unc.toLowerCase().includes(q) ||
            u.nombre_unc.toLowerCase().includes(q)
        );
    });

    // Ordenar poniendo primero las que corresponden exactamente al semestreGlobal del período y al PNF activo
    return [...disponibles].sort((a, b) => {
        const aSem = Number(a.semestre_unc) === Number(periodo.semestreGlobal) ? 0 : 1;
        const bSem = Number(b.semestre_unc) === Number(periodo.semestreGlobal) ? 0 : 1;
        if (aSem !== bSem) return aSem - bSem;

        if (prefix) {
            const aMatch = a.codigo_unc.toUpperCase().startsWith(prefix) ? 0 : 1;
            const bMatch = b.codigo_unc.toUpperCase().startsWith(prefix) ? 0 : 1;
            if (aMatch !== bMatch) return aMatch - bMatch;
        }
        return a.codigo_unc.localeCompare(b.codigo_unc);
    });
};

/**
 * Abre el selector inline para agregar una unidad curricular dentro de un Semestre/Período.
 */
const abrirSelectorUnidad = (periodo: PeriodoContainer) => {
    periodo.busquedaActiva = true;
    periodo.terminoBusqueda = '';
    const disponibles = obtenerUnidadesDisponiblesParaPeriodo(periodo);
    periodo.uncIdSeleccionada = disponibles[0]?.id_unc ?? '';
};

/**
 * Inserta la Unidad Curricular elegida dentro de la tabla del Semestre/Período.
 */
const confirmarAgregarUnidad = (trayecto: TrayectoBuilderBlock, periodo: PeriodoContainer, itemDirecto?: UnidadCatalogoItem) => {
    const catalogoItem =
        itemDirecto ??
        props.unidadesCatalogo.find((u) => Number(u.id_unc) === Number(periodo.uncIdSeleccionada));

    if (!catalogoItem) return;

    if (periodo.unidades.some((u) => Number(u.unc_id) === Number(catalogoItem.id_unc))) {
        return;
    }

    const cfg = obtenerConfigTipoPeriodo(trayecto.tpd_id);

    periodo.unidades.push({
        unc_id: catalogoItem.id_unc,
        codigo: catalogoItem.codigo_unc,
        nombre: catalogoItem.nombre_unc,
        tipo: catalogoItem.tipo,
        tuc_id: catalogoItem.tuc_id ?? null,
        uc: catalogoItem.uc || 3,
        htea: catalogoItem.htea || 3,
        htei: catalogoItem.htei || 3,
        thte: catalogoItem.thte || 6,
        duracion_semanas: cfg.duracion || 18,
    });

    emitirNotificacionLocal(
        '¡Unidad Curricular Agregada!',
        `[${catalogoItem.codigo_unc}] ${catalogoItem.nombre_unc} insertada en ${periodo.etiqueta} (${trayecto.nombre_tra}).`
    );

    // Preparar siguiente selección o cerrar si no quedan
    const restantes = obtenerUnidadesDisponiblesParaPeriodo(periodo);
    if (restantes.length > 0) {
        periodo.uncIdSeleccionada = restantes[0].id_unc;
    } else {
        periodo.busquedaActiva = false;
        periodo.uncIdSeleccionada = '';
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
 * Carga sugerida opcional desde el endpoint del PNF y la distribuye en la estructura de Trayectos y Semestres.
 */
const autocompletarDesdeCatalogoPnf = async () => {
    if (!form.pnf_id) return;
    cargandoSugerencia.value = true;
    try {
        let endpoint = `/academico/malla-curricular/pnf/${form.pnf_id}/unidades`;
        try {
            if (typeof route === 'function') {
                endpoint = route('academico.malla-curricular.unidades-pnf', { id: form.pnf_id });
            }
        } catch {
            endpoint = `/academico/malla-curricular/pnf/${form.pnf_id}/unidades`;
        }

        const { data } = await axios.get(endpoint);
        const bloques = data.bloques || [];
        if (!bloques.length) return;

        // Conservar TODOS los trayectos y materias que el usuario ya haya agregado en pantalla
        const mapaTrayectos = new Map<number, TrayectoBuilderBlock>();
        trayectos.value.forEach((trActual) => {
            mapaTrayectos.set(trActual.numero_orden_tra, trActual);
        });

        const tpdId = tipoPeriodoGlobalId.value || tipoPeriodoDefaultId.value;
        const cfg = obtenerConfigTipoPeriodo(tpdId);
        let agregadasCount = 0;

        const prefix = pnfSeleccionadoObj.value?.codigo_pnf
            ? pnfSeleccionadoObj.value.codigo_pnf.substring(0, 3).toUpperCase()
            : null;

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
                        perContainer.unidades.push({
                            unc_id: uCat.id_unc,
                            codigo: uCat.codigo_unc,
                            nombre: uCat.nombre_unc,
                            tipo: uCat.tipo || 'Regular',
                            tuc_id: uCat.tuc_id ?? null,
                            uc: Number(uCat.uc) || 3,
                            htea: Number(uCat.htea) || 3,
                            htei: Number(uCat.htei) || 3,
                            thte: Number(uCat.thte) || 6,
                            duracion_semanas: cfg.duracion || 18,
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

// Autocompletar sugerencia de Nombre de Malla si está vacío al seleccionar PNF
watch(
    () => form.pnf_id,
    (val) => {
        if (!val) return;
        const pnf = props.pnfs.find((p) => Number(p.id_pnf) === Number(val));
        if (pnf && !form.nombre_mac.trim()) {
            form.nombre_mac = `Malla Curricular ${pnf.nombre_pnf}`;
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
                    numero_orden_tra: tr.numero_orden_tra,
                    tpd_id: tr.tpd_id,
                    semestre: semestreGlobal > 0 ? semestreGlobal : per.numero,
                    periodo_local: per.numero,
                    etapa,
                    uc: Number(u.uc) || 3,
                    htea: Number(u.htea) || 3,
                    htei: Number(u.htei) || 3,
                    thte: Number(u.thte) || 6,
                    duracion_semanas: Number(u.duracion_semanas) || 18,
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
        form.creditos_minimos_tsu_mac = tot.creditosTsu;
        form.horas_minimas_tsu_mac = tot.horasTsu;
        form.materias_obligatorias_tsu_mac = tot.materiasTsu;
        form.creditos_minimos_ing_mac = tot.creditosIng;
        form.horas_minimas_ing_mac = tot.horasIng;
        form.materias_obligatorias_ing_mac = tot.materiasIng;
        form.creditos_minimos_prosecucion_mac = tot.creditosProsecucion;
    },
    { immediate: true }
);

const submit = () => {
    form.unidades_seleccionadas = unidadesPlanas.value;
    form.post(route('academico.malla-curricular.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Crear Malla Curricular" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6 max-w-6xl mx-auto w-full">
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
                                <Label for="pnf_id" class="font-semibold">
                                    Programa / PNF <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="pnf_id"
                                    v-model="form.pnf_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    :class="{ 'border-destructive': form.errors.pnf_id }"
                                    required
                                >
                                    <option value="" disabled>Seleccione un Programa / PNF...</option>
                                    <option v-for="pnf in pnfs" :key="pnf.id_pnf" :value="pnf.id_pnf">
                                        {{ pnf.codigo_pnf ? `[${pnf.codigo_pnf}] ` : '' }}{{ pnf.nombre_pnf }}
                                    </option>
                                </select>
                                <p v-if="form.errors.pnf_id" class="text-xs text-destructive font-medium">
                                    {{ form.errors.pnf_id }}
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
                                        v-model="tipoPeriodoGlobalId"
                                        @change="aplicarTipoPeriodoGlobal"
                                        class="h-8 bg-transparent text-xs font-medium focus:outline-none pr-2"
                                    >
                                        <option
                                            v-for="tp in tiposPeriodo"
                                            :key="tp.id_tpd"
                                            :value="tp.id_tpd"
                                        >
                                            {{ tp.nomenclatura || tp.nombre_tpd }} ({{ tp.duracion_semanas_tpd || 18 }} sem.)
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

                                <!-- Autocompletar opcional desde el catálogo del PNF -->
                                <Button
                                    v-if="form.pnf_id"
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    class="gap-1.5 text-xs"
                                    :disabled="cargandoSugerencia"
                                    @click="autocompletarDesdeCatalogoPnf"
                                >
                                    <Loader2 v-if="cargandoSugerencia" class="h-3.5 w-3.5 animate-spin" />
                                    <Sparkles v-else class="h-3.5 w-3.5 text-amber-500" />
                                    Cargar Materias Sugeridas del PNF
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <!-- ========================================================= -->
                    <!-- SECCIÓN 3: DETALLE DE UNIDADES POR TRAYECTO Y SEMESTRE    -->
                    <!-- ========================================================= -->
                    <CardContent class="pt-6 space-y-6">
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
                            class="rounded-xl border-2 bg-card shadow-xs overflow-hidden transition-all duration-300"
                            :class="
                                ultimoTrayectoUid === trayecto.uid
                                    ? 'border-emerald-500 ring-2 ring-emerald-500/30'
                                    : 'border-border/80'
                            "
                        >
                            <!-- Cabecera del Trayecto -->
                            <div class="flex flex-wrap items-center justify-between gap-3 bg-muted/40 px-4 py-3 border-b">
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
                                            :value="trayecto.tpd_id"
                                            @change="cambiarTipoPeriodoTrayecto(trayecto, Number(($event.target as HTMLSelectElement).value))"
                                            class="h-7 rounded border border-input bg-background px-2 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-ring"
                                        >
                                            <option
                                                v-for="tp in tiposPeriodo"
                                                :key="tp.id_tpd"
                                                :value="tp.id_tpd"
                                            >
                                                {{ tp.nomenclatura || tp.nombre_tpd }}
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
                                class="p-4 grid gap-4"
                                :class="{
                                    'grid-cols-1': trayecto.periodos.length === 1,
                                    'grid-cols-1 lg:grid-cols-2': trayecto.periodos.length === 2,
                                    'grid-cols-1 lg:grid-cols-3': trayecto.periodos.length >= 3,
                                }"
                            >
                                <div
                                    v-for="periodo in trayecto.periodos"
                                    :key="periodo.numero"
                                    class="rounded-lg border border-border bg-background flex flex-col justify-between overflow-hidden shadow-2xs"
                                >
                                    <div>
                                        <!-- Encabezado del Semestre / Período + Botón [Agregar Unidad] -->
                                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-muted/25 border-b">
                                            <div class="flex items-center gap-2">
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
                                                class="h-7 text-xs gap-1 font-semibold border-primary/40 text-primary hover:bg-primary/10"
                                                @click="abrirSelectorUnidad(periodo)"
                                            >
                                                <Plus class="h-3.5 w-3.5" />
                                                Agregar Unidad
                                            </Button>
                                        </div>

                                        <!-- Buscador / Selector Inline al pulsar [+ Agregar Unidad] -->
                                        <div
                                            v-if="periodo.busquedaActiva"
                                            class="p-3 bg-primary/5 border-b border-primary/20 space-y-2.5"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-primary flex items-center gap-1.5">
                                                    <Search class="h-3.5 w-3.5" />
                                                    Seleccionar Unidad Curricular para {{ periodo.etiqueta }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="text-muted-foreground hover:text-foreground"
                                                    @click="periodo.busquedaActiva = false"
                                                >
                                                    <X class="h-4 w-4" />
                                                </button>
                                            </div>

                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <Input
                                                    v-model="periodo.terminoBusqueda"
                                                    type="text"
                                                    placeholder="Filtrar por código (ej. INF-1101) o nombre..."
                                                    class="h-8 text-xs sm:w-52 bg-background"
                                                />

                                                <select
                                                    v-model="periodo.uncIdSeleccionada"
                                                    class="flex-1 h-8 rounded-md border border-input bg-background px-2.5 text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                                >
                                                    <option value="" disabled>
                                                        Seleccione una asignatura del catálogo...
                                                    </option>
                                                    <option
                                                        v-for="uCat in obtenerUnidadesDisponiblesParaPeriodo(periodo)"
                                                        :key="uCat.id_unc"
                                                        :value="uCat.id_unc"
                                                    >
                                                        {{ uCat.codigo_unc }} — {{ uCat.nombre_unc }} ({{ uCat.uc }} UC)
                                                    </option>
                                                </select>

                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    class="h-8 px-3 text-xs gap-1 shrink-0"
                                                    :disabled="!periodo.uncIdSeleccionada"
                                                    @click="confirmarAgregarUnidad(trayecto, periodo)"
                                                >
                                                    <Check class="h-3.5 w-3.5" />
                                                    Insertar
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Tabla de Materias del Semestre (N°, Código, Nombre de la Asignatura) -->
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="border-b bg-muted/15 text-muted-foreground">
                                                        <th class="py-2 px-3 text-left font-semibold w-10">#</th>
                                                        <th class="py-2 px-3 text-left font-semibold w-28">Código</th>
                                                        <th class="py-2 px-3 text-left font-semibold">Asignatura</th>
                                                        <th class="py-2 px-2 text-center font-semibold w-16">U.C.</th>
                                                        <th class="py-2 px-2 text-right font-semibold w-10"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y">
                                                    <tr
                                                        v-if="periodo.unidades.length === 0"
                                                        class="text-muted-foreground"
                                                    >
                                                        <td colspan="5" class="py-6 text-center italic">
                                                            Sin unidades asignadas en {{ periodo.etiqueta }}. Pulse
                                                            <strong>"+ Agregar Unidad"</strong>.
                                                        </td>
                                                    </tr>

                                                    <tr
                                                        v-for="(unidad, idx) in periodo.unidades"
                                                        :key="unidad.unc_id"
                                                        class="hover:bg-muted/20 transition-colors"
                                                    >
                                                        <td class="py-2 px-3 font-mono font-semibold text-muted-foreground">
                                                            {{ idx + 1 }}
                                                        </td>
                                                        <td class="py-2 px-3 font-mono font-semibold text-primary">
                                                            {{ unidad.codigo }}
                                                        </td>
                                                        <td class="py-2 px-3 font-medium text-foreground">
                                                            {{ unidad.nombre }}
                                                        </td>
                                                        <td class="py-1.5 px-2 text-center">
                                                            <input
                                                                v-model.number="unidad.uc"
                                                                type="number"
                                                                min="0"
                                                                max="20"
                                                                class="w-12 h-6 text-center rounded border border-input bg-background text-xs font-medium"
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
                                    <Input v-model="form.creditos_minimos_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Horas TSU</Label>
                                    <Input v-model="form.horas_minimas_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Materias TSU</Label>
                                    <Input v-model="form.materias_obligatorias_tsu_mac" type="number" min="0" class="h-8 text-xs mt-1" />
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
                                    <Input v-model="form.creditos_minimos_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Horas Ing.</Label>
                                    <Input v-model="form.horas_minimas_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                                <div>
                                    <Label class="text-xs">Materias Ing.</Label>
                                    <Input v-model="form.materias_obligatorias_ing_mac" type="number" min="0" class="h-8 text-xs mt-1" />
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button variant="outline" type="button" as-child :disabled="form.processing">
                            <Link :href="route('academico.malla-curricular.index')">Cancelar</Link>
                        </Button>

                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'Guardando Malla...' : 'Guardar Malla Curricular' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
