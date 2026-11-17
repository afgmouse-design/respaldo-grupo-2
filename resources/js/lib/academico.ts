/**
 * Utilidades compartidas del dominio académico para vistas Vue (`programa`, `tipo_lapso` y constructor de `malla_curricular`).
 *
 * Propósito: Centralizar interfaces y adaptadores de lectura sobre las tablas oficiales de
 * `sogac_mio.sql` (`programa.id_prg`, `tipo_lapso.id_tla`) evitando duplicación entre formularios y reportes.
 *
 * Historial:
 *  - 2026-11-14: Creación para unificar constructores de mallas curriculares (`create.vue`, `edit.vue`) y catálogos académicos.
 *  - 2026-11-16: Ampliado con `EscalaMedicionCatalogoItem`, `EjeCurricularCatalogoItem`, parámetros académicos de detalle
 *    (`ecu_id`, `htea`, `htei`, `thte`, `duracion_semestres`, `nota_minima_aprobatoria_mcd`) y helpers de precarga/escala.
 */

export interface EscalaMedicionCatalogoItem {
    id_esm: number;
    nombre_esm: string;
    nota_aprobatoria_esm: number;
    nota_minima_esm?: number | null;
    nota_maxima_esm?: number | null;
}

export interface EjeCurricularCatalogoItem {
    id_ecu: number;
    codigo_ecu?: string | null;
    nombre_ecu: string;
}

export interface ProgramaCatalogoItem {
    id_prg?: number;
    id_pnf?: number;
    esm_id?: number | null;
    codigo_prg?: string | null;
    codigo_ministerio_prg?: string | null;
    codigo_pnf?: string | null;
    nombre_prg?: string;
    nombre_pnf?: string;
    siglas_prg?: string | null;
    escala_medicion?: EscalaMedicionCatalogoItem | null;
    escalaMedicion?: EscalaMedicionCatalogoItem | null;
}

export interface TipoLapsoCatalogoItem {
    id_tla?: number;
    id_tpd?: number;
    codigo_tla?: string | null;
    codigo_tpd?: string | null;
    nombre_tla?: string;
    nombre_tpd?: string;
    nomenclatura_tla?: string | null;
    nomenclatura?: string | null;
    duracion_semanas_tla?: number | null;
    duracion_semanas_tpd?: number | null;
}

export interface UnidadCatalogoItem {
    id_unc: number;
    codigo_unc: string;
    nombre_unc: string;
    tipo: string;
    tuc_id?: number | null;
    tra_id: number;
    semestre_unc: number;
    ecu_id?: number | null;
    uc: number;
    htea: number;
    htei: number;
    thte: number;
    duracion_semestres?: 1 | 2;
    nota_aprobatoria?: number | null;
}

export interface UnidadSeleccionadaRow {
    unc_id: number;
    codigo: string;
    nombre: string;
    tipo: string;
    tuc_id?: number | null;
    ecu_id: number | null;
    esm_id?: number | null;
    uc: number;
    htea: number;
    htei: number;
    thte: number;
    duracion_semestres: 1 | 2;
    duracion_semanas: number;
    nota_minima_aprobatoria_mcd: number | null;
}

export interface PeriodoContainer {
    numero: number;
    semestreGlobal: number;
    etiqueta: string;
    busquedaActiva: boolean;
    terminoBusqueda: string;
    uncIdSeleccionada: number | string;
    ecuIdSeleccionado: number | string;
    hteaSeleccionado: number;
    hteiSeleccionado: number;
    thteSeleccionado: number;
    duracionSemestresSeleccionada: 1 | 2;
    notaAprobatoriaSeleccionada: number | string;
    unidades: UnidadSeleccionadaRow[];
}

export interface TrayectoBuilderBlock {
    uid: string;
    numero_orden_tra: number;
    nombre_tra: string;
    tla_id: number;
    tipo_periodo_nombre: string;
    periodos: PeriodoContainer[];
}

export const ROMANOS_TRAYECTO: Record<number, string> = {
    0: 'Inicial',
    1: 'I',
    2: 'II',
    3: 'III',
    4: 'IV',
    5: 'V',
};

export function obtenerIdPrograma(programa?: ProgramaCatalogoItem | null): number {
    return Number(programa?.id_prg ?? programa?.id_pnf ?? 0);
}

export function obtenerCodigoPrograma(programa?: ProgramaCatalogoItem | null): string {
    return String(programa?.codigo_prg ?? programa?.codigo_ministerio_prg ?? programa?.codigo_pnf ?? '').trim();
}

export function obtenerNombrePrograma(programa?: ProgramaCatalogoItem | null): string {
    return String(programa?.nombre_prg ?? programa?.nombre_pnf ?? '');
}

export function obtenerIdTipoLapso(tipo?: TipoLapsoCatalogoItem | null): number {
    return Number(tipo?.id_tla ?? tipo?.id_tpd ?? 0);
}

export function obtenerNombreTipoLapso(tipo?: TipoLapsoCatalogoItem | null): string {
    return String(tipo?.nombre_tla ?? tipo?.nombre_tpd ?? '');
}

export function obtenerNomenclaturaTipoLapso(tipo?: TipoLapsoCatalogoItem | null): string {
    return String(tipo?.nomenclatura_tla ?? tipo?.nomenclatura ?? '');
}

export function obtenerDuracionTipoLapso(tipo?: TipoLapsoCatalogoItem | null): number {
    return Number(tipo?.duracion_semanas_tla ?? tipo?.duracion_semanas_tpd ?? 18);
}

export function obtenerTipoLapsoSemestralPorDefecto(tipos: TipoLapsoCatalogoItem[]): number {
    const semestral = tipos.find((t) => {
        const nom = obtenerNomenclaturaTipoLapso(t).toLowerCase();
        const nombre = obtenerNombreTipoLapso(t).toLowerCase();
        return nom.includes('semestr') || nombre.includes('semestr');
    });

    return semestral ? obtenerIdTipoLapso(semestral) : (obtenerIdTipoLapso(tipos[0]) || 1);
}

export function obtenerConfigTipoLapso(tipos: TipoLapsoCatalogoItem[], tlaId: number) {
    const item = tipos.find((t) => obtenerIdTipoLapso(t) === Number(tlaId));
    const texto = `${obtenerNomenclaturaTipoLapso(item)} ${obtenerNombreTipoLapso(item)}`.toLowerCase();

    if (texto.includes('trimestr')) {
        return { cantidad: 3, singular: 'Trimestre', duracion: obtenerDuracionTipoLapso(item) || 12 };
    }
    if (texto.includes('anual') || texto.includes('año') || texto.includes('ano')) {
        return { cantidad: 1, singular: 'Año / Período Anual', duracion: obtenerDuracionTipoLapso(item) || 36 };
    }

    return { cantidad: 2, singular: 'Semestre', duracion: obtenerDuracionTipoLapso(item) || 18 };
}

export function construirPeriodosParaTrayecto(
    tipos: TipoLapsoCatalogoItem[],
    tlaId: number,
    numeroOrdenTra = 1,
    periodosPrevios: PeriodoContainer[] = []
): PeriodoContainer[] {
    const cfg = obtenerConfigTipoLapso(tipos, tlaId);

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
                ecuIdSeleccionado: previo ? previo.ecuIdSeleccionado : '',
                hteaSeleccionado: previo ? previo.hteaSeleccionado : 3,
                hteiSeleccionado: previo ? previo.hteiSeleccionado : 3,
                thteSeleccionado: previo ? previo.thteSeleccionado : 6,
                duracionSemestresSeleccionada: previo ? previo.duracionSemestresSeleccionada : 1,
                notaAprobatoriaSeleccionada: previo ? previo.notaAprobatoriaSeleccionada : 12,
                unidades: previo ? [...previo.unidades] : [],
            },
        ];
    }

    const nuevos: PeriodoContainer[] = [];
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
            ecuIdSeleccionado: previo ? previo.ecuIdSeleccionado : '',
            hteaSeleccionado: previo ? previo.hteaSeleccionado : 3,
            hteiSeleccionado: previo ? previo.hteiSeleccionado : 3,
            thteSeleccionado: previo ? previo.thteSeleccionado : 6,
            duracionSemestresSeleccionada: previo ? previo.duracionSemestresSeleccionada : 1,
            notaAprobatoriaSeleccionada: previo ? previo.notaAprobatoriaSeleccionada : 12,
            unidades: previo ? [...previo.unidades] : [],
        });
    }

    return nuevos;
}

export interface EscalaResuelta {
    esm_id: number | null;
    nombre: string;
    min: number;
    max: number;
    notaAprobatoria: number;
}

/**
 * Resuelve la escala de medición efectiva para un Programa seleccionado y su rango válido.
 */
export function resolverEscalaPrograma(
    programa?: ProgramaCatalogoItem | null,
    escalas: EscalaMedicionCatalogoItem[] = []
): EscalaResuelta {
    const raw =
        programa?.escala_medicion ??
        programa?.escalaMedicion ??
        escalas.find((e) => Number(e.id_esm) === Number(programa?.esm_id)) ??
        escalas[0] ??
        null;

    if (!raw) {
        return {
            esm_id: null,
            nombre: 'Estándar (0–20)',
            min: 0,
            max: 20,
            notaAprobatoria: 12,
        };
    }

    const min = raw.nota_minima_esm !== null && raw.nota_minima_esm !== undefined ? Number(raw.nota_minima_esm) : 0;
    const max = raw.nota_maxima_esm !== null && raw.nota_maxima_esm !== undefined ? Number(raw.nota_maxima_esm) : 20;
    const notaAprobatoria = Number(raw.nota_aprobatoria_esm ?? 12);

    return {
        esm_id: Number(raw.id_esm),
        nombre: raw.nombre_esm,
        min,
        max,
        notaAprobatoria,
    };
}

/**
 * Precarga los parámetros académicos editables en el panel inline al elegir una Unidad Curricular.
 */
export function inicializarParametrosPeriodo(
    periodo: PeriodoContainer,
    unidad: UnidadCatalogoItem | undefined,
    escala: EscalaResuelta,
    ejes: EjeCurricularCatalogoItem[] = []
): void {
    const htea = unidad && Number.isFinite(Number(unidad.htea)) ? Math.max(0, Number(unidad.htea)) : 3;
    const htei = unidad && Number.isFinite(Number(unidad.htei)) ? Math.max(0, Number(unidad.htei)) : 3;
    const thte = unidad && Number.isFinite(Number(unidad.thte)) ? Math.max(0, Number(unidad.thte)) : htea + htei;

    periodo.hteaSeleccionado = htea;
    periodo.hteiSeleccionado = htei;
    periodo.thteSeleccionado = thte;
    periodo.ecuIdSeleccionado = unidad?.ecu_id ?? ejes[0]?.id_ecu ?? '';
    periodo.duracionSemestresSeleccionada = unidad?.duracion_semestres === 2 ? 2 : 1;

    const notaCat = unidad?.nota_aprobatoria;
    if (
        notaCat !== null &&
        notaCat !== undefined &&
        Number(notaCat) >= escala.min &&
        Number(notaCat) <= escala.max
    ) {
        periodo.notaAprobatoriaSeleccionada = Number(notaCat);
    } else {
        periodo.notaAprobatoriaSeleccionada = escala.notaAprobatoria;
    }
}

/**
 * Recalcula HTE = HEA + HEI en tiempo real dentro del selector inline de inserción.
 */
export function recalcularHtePeriodo(periodo: PeriodoContainer): void {
    const hea = Math.max(0, Math.trunc(Number(periodo.hteaSeleccionado) || 0));
    const hei = Math.max(0, Math.trunc(Number(periodo.hteiSeleccionado) || 0));
    periodo.hteaSeleccionado = hea;
    periodo.hteiSeleccionado = hei;
    periodo.thteSeleccionado = hea + hei;
}

/**
 * Recalcula HTE = HEA + HEI al editar horas en una fila de la tabla de semestre.
 */
export function recalcularHteUnidad(unidad: UnidadSeleccionadaRow): void {
    const hea = Math.max(0, Math.trunc(Number(unidad.htea) || 0));
    const hei = Math.max(0, Math.trunc(Number(unidad.htei) || 0));
    unidad.htea = hea;
    unidad.htei = hei;
    unidad.thte = hea + hei;
}

/**
 * Verifica si una nota aprobatoria pertenece al rango válido de la escala de medición activa.
 */
export function esNotaValidaEnEscala(
    valor: number | string | null | undefined,
    escala: EscalaResuelta
): boolean {
    if (valor === '' || valor === null || valor === undefined) {
        return true;
    }
    const num = Number(valor);
    if (!Number.isFinite(num)) {
        return false;
    }
    return num >= escala.min && num <= escala.max;
}

/**
 * Retorna el mensaje descriptivo de validación para la nota aprobatoria del selector inline.
 */
export function obtenerErrorNotaPeriodo(
    periodo: PeriodoContainer,
    escala: EscalaResuelta
): string | null {
    const val = periodo.notaAprobatoriaSeleccionada;
    if (val === '' || val === null || val === undefined) {
        return 'Indique la nota aprobatoria.';
    }
    if (!esNotaValidaEnEscala(val, escala)) {
        return `Debe estar entre ${escala.min} y ${escala.max} (${escala.nombre}).`;
    }
    return null;
}

export interface DocenteSelectOption {
    id_pep?: number;
    id?: number;
    id_per?: number;
    cedula_per?: string | null;
    cedula?: string | null;
    nombres_per?: string | null;
    apellidos_per?: string | null;
    nombre_completo?: string | null;
    name?: string | null;
    correo_per?: string | null;
    email?: string | null;
    doc_ded_id?: number | null;
    dedicacion_nombre?: string | null;
    horas_maximas_ded?: number | null;
    horas_asignadas_lapso?: number | null;
    horas_por_lapso?: Record<string, number> | null;
    persona?: {
        nombres_per?: string | null;
        apellidos_per?: string | null;
        cedula_per?: string | null;
        correo_per?: string | null;
    } | null;
}

export function obtenerIdDocente(doc?: DocenteSelectOption | null): number {
    return Number(doc?.id_pep ?? doc?.id ?? 0);
}

export function obtenerNombreDocente(doc?: DocenteSelectOption | null, fallback = 'Por Asignar'): string {
    if (!doc) return fallback;
    if (doc.nombre_completo) return doc.nombre_completo;
    const p = doc.persona ?? doc;
    const combinado = `${p.nombres_per ?? ''} ${p.apellidos_per ?? ''}`.trim();
    return combinado || doc.name || fallback;
}

export function obtenerCorreoDocente(doc?: DocenteSelectOption | null): string {
    if (!doc) return '';
    return String(doc.persona?.correo_per ?? doc.correo_per ?? doc.email ?? '');
}

export function obtenerCedulaDocente(doc?: DocenteSelectOption | null): string {
    if (!doc) return '';
    return String(doc.persona?.cedula_per ?? doc.cedula_per ?? doc.cedula ?? '');
}

export function obtenerHorasAsignadasDocenteLapso(
    doc?: DocenteSelectOption | null,
    laaId?: number | string | null,
    excluirHoras = 0
): number {
    if (!doc) return 0;
    const key = laaId !== null && laaId !== undefined && laaId !== '' ? String(laaId) : '';
    const raw = key && doc.horas_por_lapso
        ? Number(doc.horas_por_lapso[key] ?? 0)
        : Number(doc.horas_asignadas_lapso ?? 0);
    return Math.max(0, raw - Math.max(0, Number(excluirHoras || 0)));
}

export function filtrarDocentesCatalogo<T extends DocenteSelectOption>(
    docentes: T[],
    termino: string
): T[] {
    const query = normalizarTextoBusqueda(termino);
    if (!query) return docentes;

    return docentes
        .map((doc) => {
            const cedula = normalizarTextoBusqueda(obtenerCedulaDocente(doc));
            const nombre = normalizarTextoBusqueda(obtenerNombreDocente(doc, ''));
            const correo = normalizarTextoBusqueda(obtenerCorreoDocente(doc));

            let prioridad = 99;
            if (cedula === query) prioridad = 0;
            else if (cedula.startsWith(query)) prioridad = 1;
            else if (cedula.includes(query)) prioridad = 2;
            else if (nombre.startsWith(query)) prioridad = 3;
            else if (nombre.includes(query)) prioridad = 4;
            else if (correo.includes(query)) prioridad = 5;

            return { doc, prioridad };
        })
        .filter((item) => item.prioridad < 99)
        .sort((a, b) => a.prioridad - b.prioridad)
        .map((item) => item.doc);
}

export function obtenerNombreDocenteCarga(carga?: any, fallback = 'Por Asignar'): string {
    return obtenerNombreDocente(carga?.docente_perfil ?? carga?.docente, fallback);
}

export function obtenerAulaEtiqueta(aula?: any, fallback = 'Aula'): string {
    return String(aula?.codigo_aula_aul ?? aula?.nombre_aul ?? fallback);
}

const MAPA_DIAS_SEMANA: Record<number, string[]> = {
    1: ['1', 'lunes'],
    2: ['2', 'martes'],
    3: ['3', 'miercoles', 'miércoles'],
    4: ['4', 'jueves'],
    5: ['5', 'viernes'],
    6: ['6', 'sabado', 'sábado'],
    7: ['7', 'domingo'],
};

export function coincideDiaHorario(horario: any, diaId: number, nombreDia?: string): boolean {
    const raw = String(horario?.dia_semana_hor ?? horario?.dia_semana ?? '').trim().toLowerCase();
    if (!raw) return false;
    const candidatos = MAPA_DIAS_SEMANA[diaId] ?? (nombreDia ? [String(diaId), nombreDia.toLowerCase()] : [String(diaId)]);
    return candidatos.includes(raw);
}

export function formatearHoraCorta(hora?: string | null): string {
    if (!hora) return '';
    return String(hora).slice(0, 5);
}

export function obtenerNombreMateriaHorario(horario: any, fallback = 'Materia'): string {
    return String(
        horario?.carga_academica?.unidad_curricular?.nombre_unc ??
        horario?.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.nombre_unc ??
        horario?.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.nombre_ucu ??
        fallback
    );
}

export function obtenerCodigoMateriaHorario(horario: any, fallback = 'S/C'): string {
    return String(
        horario?.carga_academica?.unidad_curricular?.codigo_unc ??
        horario?.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.codigo_unc ??
        horario?.seccion_unidad_docente?.malla_curricular_detalle?.unidad_curricular?.codigo_ucu ??
        fallback
    );
}

export function obtenerSeccionHorario(horario: any, fallback = 'N/A'): string {
    return String(
        horario?.carga_academica?.seccion?.nombre_sec ??
        horario?.seccion_unidad_docente?.seccion?.nombre_sec ??
        fallback
    );
}

/**
 * Normaliza cadenas para búsqueda en tiempo real ignorando mayúsculas, minúsculas y acentos.
 */
export function normalizarTextoBusqueda(texto?: string | null): string {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

export interface ResultadoBusquedaUnidades {
    items: UnidadCatalogoItem[];
    totalCoincidencias: number;
    totalDisponibles: number;
}

/**
 * Filtra y ordena unidades curriculares priorizando coincidencias exactas o parciales por código,
 * luego por semestre esperado y prefijo de programa, limitando la salida visible para fluidez.
 */
export function filtrarUnidadesCatalogo(
    catalogo: UnidadCatalogoItem[],
    idsExcluidos: Set<number>,
    query: string,
    semestreGlobal = 1,
    codigoPrefix: string | null = null,
    limit = 30
): ResultadoBusquedaUnidades {
    const disponibles = catalogo.filter((u) => !idsExcluidos.has(Number(u.id_unc)));
    const q = normalizarTextoBusqueda(query);

    const filtradas = q
        ? disponibles.filter((u) => {
              const cod = normalizarTextoBusqueda(u.codigo_unc);
              const nom = normalizarTextoBusqueda(u.nombre_unc);
              return cod.includes(q) || nom.includes(q);
          })
        : disponibles;

    const ordenadas = [...filtradas].sort((a, b) => {
        if (q) {
            const rankMatch = (u: UnidadCatalogoItem): number => {
                const cod = normalizarTextoBusqueda(u.codigo_unc);
                const nom = normalizarTextoBusqueda(u.nombre_unc);
                if (cod === q) return 0;
                if (cod.startsWith(q)) return 1;
                if (cod.includes(q)) return 2;
                if (nom.startsWith(q)) return 3;
                return 4;
            };
            const ra = rankMatch(a);
            const rb = rankMatch(b);
            if (ra !== rb) return ra - rb;
        }

        const aSem = Number(a.semestre_unc) === Number(semestreGlobal) ? 0 : 1;
        const bSem = Number(b.semestre_unc) === Number(semestreGlobal) ? 0 : 1;
        if (aSem !== bSem) return aSem - bSem;

        if (codigoPrefix) {
            const aMatch = a.codigo_unc.toUpperCase().startsWith(codigoPrefix) ? 0 : 1;
            const bMatch = b.codigo_unc.toUpperCase().startsWith(codigoPrefix) ? 0 : 1;
            if (aMatch !== bMatch) return aMatch - bMatch;
        }

        return a.codigo_unc.localeCompare(b.codigo_unc);
    });

    return {
        items: ordenadas.slice(0, limit),
        totalCoincidencias: ordenadas.length,
        totalDisponibles: disponibles.length,
    };
}

