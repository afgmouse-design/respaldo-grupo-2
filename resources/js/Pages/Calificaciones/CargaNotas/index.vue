<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from '@/components/ui/card';
import {
  FileSpreadsheet,
  Save,
  CheckCircle2,
  AlertTriangle,
  History,
  Printer,
  ArrowLeft,
  ArrowRight,
  Search,
  MessageSquare,
  Award,
  Plus,
  Sparkles,
  SlidersHorizontal,
  Layers,
  Lock,
  Unlock,
  GraduationCap,
  Users,
  BookOpen,
  Calendar,
  Check,
  RefreshCw,
  School,
  CheckCircle,
} from 'lucide-vue-next';

// =========================================================================
// 1. CATÁLOGOS INSTITUCIONALES PARA FORMULARIOS DE BÚSQUEDA Y FILTRO
// =========================================================================
export interface PNFItem {
  id: number;
  nombre: string;
  descripcion: string;
  area: string;
}

export interface SeccionItem {
  id: number;
  pnf_id: number;
  codigo: string;
  materia: string;
  trayecto: string;
  turno: string;
  aula: string;
  estudiantes: number;
  periodo: string;
  docenteTitular: string;
}

const listaPNFs = ref<PNFItem[]>([
  { id: 1, nombre: 'PNF-Informática', descripcion: 'Desarrollo de software, bases de datos avanzadas, redes y ciberseguridad', area: 'Tecnología y Sistemas' },
  { id: 2, nombre: 'PNF-Administración', descripcion: 'Gestión pública, finanzas organizacionales, talento humano y presupuestos', area: 'Ciencias Económicas y Sociales' },
  { id: 3, nombre: 'PNF-Agroalimentación', descripcion: 'Soberanía alimentaria, agroecología, conservación de suelos y producción vegetal', area: 'Ciencias del Agro y del Mar' },
  { id: 4, nombre: 'PNF-Electricidad', descripcion: 'Sistemas eléctricos de potencia, automatización industrial y distribución', area: 'Ingeniería y Tecnología' },
  { id: 5, nombre: 'PNF-Mecánica', descripcion: 'Diseño computarizado, máquinas térmicas, manufactura y soldadura avanzada', area: 'Ingeniería y Producción' },
  { id: 6, nombre: 'PNF-Contaduría Pública', descripcion: 'Auditoría financiera, normas internacionales, peritaje contable y tributación', area: 'Ciencias Fiscales y Contables' },
  { id: 7, nombre: 'PNF-Medicina Veterinaria', descripcion: 'Salud animal, producción pecuaria sostenible y medicina preventiva', area: 'Ciencias del Agro y del Mar' },
  { id: 8, nombre: 'PNF-Distribución y Logística', descripcion: 'Cadenas de suministro, almacenamiento, operaciones de transporte y distribución', area: 'Ciencias Económicas y Sociales' },
]);

const catalogoSecciones = ref<SeccionItem[]>([
  // Secciones PNF Informática (id 1)
  { id: 101, pnf_id: 1, codigo: 'INF-401', materia: 'Bases de Datos Avanzadas', trayecto: 'Trayecto IV', turno: 'Diurno (8:00 AM - 12:00 PM)', aula: 'Laboratorio B-12', estudiantes: 28, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 102, pnf_id: 1, codigo: 'INF-302', materia: 'Ingeniería del Software II', trayecto: 'Trayecto III', turno: 'Vespertino (1:00 PM - 5:00 PM)', aula: 'Laboratorio 3', estudiantes: 32, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 103, pnf_id: 1, codigo: 'INF-201', materia: 'Algoritmos y Estructuras de Datos', trayecto: 'Trayecto II', turno: 'Diurno', aula: 'Laboratorio 1', estudiantes: 35, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 104, pnf_id: 1, codigo: 'INF-101', materia: 'Introducción a la Programación', trayecto: 'Trayecto I', turno: 'Nocturno', aula: 'Aula A-04', estudiantes: 24, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },

  // Secciones PNF Administración (id 2)
  { id: 201, pnf_id: 2, codigo: 'ADM-401', materia: 'Planificación Estratégica Organizacional', trayecto: 'Trayecto IV', turno: 'Diurno', aula: 'Aula C-08', estudiantes: 30, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 202, pnf_id: 2, codigo: 'ADM-301', materia: 'Gestión del Talento Humano y Nómina', trayecto: 'Trayecto III', turno: 'Vespertino', aula: 'Aula C-12', estudiantes: 29, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 203, pnf_id: 2, codigo: 'ADM-201', materia: 'Presupuesto y Finanzas Públicas', trayecto: 'Trayecto II', turno: 'Diurno', aula: 'Aula C-02', estudiantes: 33, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },

  // Secciones PNF Agroalimentación (id 3)
  { id: 301, pnf_id: 3, codigo: 'AGR-301', materia: 'Manejo Agroecológico de Suelos y Aguas', trayecto: 'Trayecto III', turno: 'Diurno', aula: 'Campo Experimental 1', estudiantes: 22, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 302, pnf_id: 3, codigo: 'AGR-201', materia: 'Sanidad Vegetal y Control Biológico', trayecto: 'Trayecto II', turno: 'Vespertino', aula: 'Laboratorio de Biología', estudiantes: 26, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },

  // Secciones PNF Electricidad (id 4)
  { id: 401, pnf_id: 4, codigo: 'ELE-301', materia: 'Controladores Lógicos Programables (PLC)', trayecto: 'Trayecto III', turno: 'Diurno', aula: 'Taller de Automatización', estudiantes: 20, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 402, pnf_id: 4, codigo: 'ELE-201', materia: 'Circuitos Eléctricos y Mediciones', trayecto: 'Trayecto II', turno: 'Vespertino', aula: 'Laboratorio de Electricidad', estudiantes: 25, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },

  // Secciones PNF Mecánica (id 5)
  { id: 501, pnf_id: 5, codigo: 'MEC-301', materia: 'Mantenimiento Mecánico Industrial', trayecto: 'Trayecto III', turno: 'Diurno', aula: 'Taller Central de Máquinas', estudiantes: 21, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
  { id: 502, pnf_id: 5, codigo: 'MEC-201', materia: 'Resistencia de Materiales y Ensayo', trayecto: 'Trayecto II', turno: 'Diurno', aula: 'Laboratorio de Materiales', estudiantes: 27, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },

  // Secciones Licenciatura Contaduría (id 6)
  { id: 601, pnf_id: 6, codigo: 'CON-301', materia: 'Auditoría Forense y Dictamen Financiero', trayecto: 'Trayecto III', turno: 'Diurno', aula: 'Aula D-05', estudiantes: 28, periodo: 'Cohorte 2026-I', docenteTitular: 'Prof. Carlos Mendoza' },
]);

// Control de flujo: 'FILTRO' (inicial) o 'CARGA' (tabla de notas)
const pasoActivo = ref<'FILTRO' | 'CARGA'>('FILTRO');

// Variables de búsqueda y selección de PNF
const busquedaPNFTexto = ref('');
const pnfSeleccionadoId = ref<number | null>(null);

// Variables de búsqueda y selección de Sección
const busquedaSeccionTexto = ref('');
const seccionSeleccionadaId = ref<number | null>(null);

// Selección del Corte / Cohorte evaluativo para calificar en el Paso 3
const corteSeleccionadoParaCarga = ref<number>(1);

const corteSeleccionadoNombre = computed(() => {
  const c = cortes.value.find(item => item.id === corteSeleccionadoParaCarga.value);
  return c ? c.nombre : 'Corte I';
});

const corteSeleccionadoPeso = computed(() => {
  const c = cortes.value.find(item => item.id === corteSeleccionadoParaCarga.value);
  return c ? c.pesoCorte : 25;
});

// =========================================================================
// REGLA INSTITUCIONAL: SOLO SE PUEDE PONER Y MODIFICAR EL COHORTE CORRESPONDIENTE
// =========================================================================
const esCorteCorrespondienteHabilitado = computed(() => {
  if (typeof corteActivoId.value !== 'number') return false;
  return corteActivoId.value === corteSeleccionadoParaCarga.value;
});

const establecerCohorteCorrespondiente = (corteId: number) => {
  corteSeleccionadoParaCarga.value = corteId;
  corteActivoId.value = corteId;
};

// Resetear sección si cambia el PNF
watch(pnfSeleccionadoId, (nuevoVal) => {
  seccionSeleccionadaId.value = null;
  busquedaSeccionTexto.value = '';
  corteSeleccionadoParaCarga.value = 1;
});

// Filtro en tiempo real para PNFs
const pnfsFiltrados = computed(() => {
  const query = busquedaPNFTexto.value.trim().toLowerCase();
  if (!query) return listaPNFs.value;
  return listaPNFs.value.filter(p =>
    p.nombre.toLowerCase().includes(query) ||
    p.area.toLowerCase().includes(query) ||
    p.descripcion.toLowerCase().includes(query)
  );
});

// PNF actualmente seleccionado
const pnfSeleccionado = computed(() => {
  return listaPNFs.value.find(p => p.id === pnfSeleccionadoId.value) || null;
});

// Secciones del PNF seleccionado
const seccionesDelPnf = computed(() => {
  if (!pnfSeleccionadoId.value) return [];
  return catalogoSecciones.value.filter(s => s.pnf_id === pnfSeleccionadoId.value);
});

// Filtro en tiempo real para Secciones
const seccionesFiltradas = computed(() => {
  const query = busquedaSeccionTexto.value.trim().toLowerCase();
  if (!query) return seccionesDelPnf.value;
  return seccionesDelPnf.value.filter(s =>
    s.codigo.toLowerCase().includes(query) ||
    s.materia.toLowerCase().includes(query) ||
    s.trayecto.toLowerCase().includes(query) ||
    s.turno.toLowerCase().includes(query)
  );
});

// Sección actualmente seleccionada
const seccionSeleccionada = computed(() => {
  return catalogoSecciones.value.find(s => s.id === seccionSeleccionadaId.value) || null;
});

// Validación para habilitar el botón "Siguiente"
const puedeAvanzar = computed(() => {
  return pnfSeleccionadoId.value !== null && seccionSeleccionadaId.value !== null;
});

const avanzarACarga = () => {
  if (puedeAvanzar.value) {
    corteActivoId.value = corteSeleccionadoParaCarga.value;
    pasoActivo.value = 'CARGA';
  }
};

const volverAFiltro = () => {
  pasoActivo.value = 'FILTRO';
};

// =========================================================================
// 2. CONFIGURACIÓN DE CORTES (DIVISIÓN EN 4 O 5 EVALUACIONES Y METODOLOGÍAS)
// =========================================================================
const metodologiasBase = [
  'Examen Parcial Escrito',
  'Proyecto Sociotecnológico / Práctico',
  'Taller / Laboratorio',
  'Defensa / Exposición Oral',
  'Prueba Corta (Quiz)',
  'Portafolio de Evidencias',
  'Estudio de Casos',
  'Resolución de Problemas / Algoritmos',
  'Práctica de Campo',
  'Ensayo / Informe Crítico',
];

const listaMetodologias = ref<string[]>([...metodologiasBase]);

export interface EvaluacionItem {
  id: number;
  nombre: string;
  metodologia: string;
  peso: number;
  semana: number;
}

export interface CorteConfig {
  id: number;
  nombre: string;
  pesoCorte: number; // 25%
  cantidadEvaluaciones: 4 | 5;
  evaluaciones: EvaluacionItem[];
}

const cortes = ref<CorteConfig[]>([
  {
    id: 1,
    nombre: 'Corte I',
    pesoCorte: 25,
    cantidadEvaluaciones: 4,
    evaluaciones: [
      { id: 1, nombre: 'Evaluación 1: Quiz Diagnóstico DDL', metodologia: 'Prueba Corta (Quiz)', peso: 5, semana: 3 },
      { id: 2, nombre: 'Evaluación 2: Taller de Triggers', metodologia: 'Taller / Laboratorio', peso: 5, semana: 5 },
      { id: 3, nombre: 'Evaluación 3: Práctica de Indexación', metodologia: 'Resolución de Problemas / Algoritmos', peso: 5, semana: 7 },
      { id: 4, nombre: 'Evaluación 4: Examen Parcial Unidad I', metodologia: 'Examen Parcial Escrito', peso: 10, semana: 9 },
    ],
  },
  {
    id: 2,
    nombre: 'Corte II',
    pesoCorte: 25,
    cantidadEvaluaciones: 4,
    evaluaciones: [
      { id: 1, nombre: 'Evaluación 1: Control de Lectura ACID', metodologia: 'Prueba Corta (Quiz)', peso: 5, semana: 11 },
      { id: 2, nombre: 'Evaluación 2: Laboratorio PL/pgSQL', metodologia: 'Taller / Laboratorio', peso: 5, semana: 13 },
      { id: 3, nombre: 'Evaluación 3: Casos de Optimización', metodologia: 'Estudio de Casos', peso: 5, semana: 15 },
      { id: 4, nombre: 'Evaluación 4: Prueba Teórico-Práctica', metodologia: 'Examen Parcial Escrito', peso: 10, semana: 17 },
    ],
  },
  {
    id: 3,
    nombre: 'Corte III',
    pesoCorte: 25,
    cantidadEvaluaciones: 5,
    evaluaciones: [
      { id: 1, nombre: 'Evaluación 1: Modelo Físico BD', metodologia: 'Proyecto Sociotecnológico / Práctico', peso: 5, semana: 19 },
      { id: 2, nombre: 'Evaluación 2: Seguridad y Roles DCL', metodologia: 'Taller / Laboratorio', peso: 5, semana: 21 },
      { id: 3, nombre: 'Evaluación 3: Práctica de Replicación', metodologia: 'Taller / Laboratorio', peso: 5, semana: 23 },
      { id: 4, nombre: 'Evaluación 4: Informe de Auditoría', metodologia: 'Portafolio de Evidencias', peso: 5, semana: 25 },
      { id: 5, nombre: 'Evaluación 5: Entrega del Sistema BD', metodologia: 'Proyecto Sociotecnológico / Práctico', peso: 5, semana: 27 },
    ],
  },
  {
    id: 4,
    nombre: 'Corte IV',
    pesoCorte: 25,
    cantidadEvaluaciones: 4,
    evaluaciones: [
      { id: 1, nombre: 'Evaluación 1: Prueba de Rendimiento', metodologia: 'Taller / Laboratorio', peso: 5, semana: 29 },
      { id: 2, nombre: 'Evaluación 2: Manual Técnico', metodologia: 'Portafolio de Evidencias', peso: 5, semana: 31 },
      { id: 3, nombre: 'Evaluación 3: Exposición de Arquitectura', metodologia: 'Defensa / Exposición Oral', peso: 5, semana: 33 },
      { id: 4, nombre: 'Evaluación 4: Defensa Final ante Jurado', metodologia: 'Defensa / Exposición Oral', peso: 10, semana: 35 },
    ],
  },
]);

// Estudiantes inscritos con sus notas desglosadas por cada Corte / Cohorte
interface EstudianteRegistro {
  ins_codigo: number;
  ins_cedula: string;
  nombres: string;
  notasDetalle: Record<number, (number | null)[]>;
  modificaciones: number;
  nota_hito: number | null;
  observacion: string;
}

const estudiantes = ref<EstudianteRegistro[]>([
  {
    ins_codigo: 1001,
    ins_cedula: '30123456',
    nombres: 'Alejandro González',
    notasDetalle: {
      1: [23, 22, 24, 23],
      2: [22, 23, 21, 22],
      3: [24, 24, 23, 25, 24],
      4: [23, 24, 22, 23],
    },
    modificaciones: 1,
    nota_hito: 23,
    observacion: 'Excelente desempeño en optimización de consultas SQL.',
  },
  {
    ins_codigo: 1002,
    ins_cedula: '28456789',
    nombres: 'Mariana Rivas Delgado',
    notasDetalle: {
      1: [20, 19, 21, 20],
      2: [19, 20, 19, 20],
      3: [20, 21, 20, 20, 19],
      4: [21, 20, 22, 21],
    },
    modificaciones: 0,
    nota_hito: 20,
    observacion: 'Participación constante.',
  },
  {
    ins_codigo: 1003,
    ins_cedula: '29876543',
    nombres: 'Gabriel Herrera Castillo',
    notasDetalle: {
      1: [12, 11, 13, 12],
      2: [11, 12, 10, 11],
      3: [13, 12, 14, 13, 12],
      4: [12, 13, 11, 12],
    },
    modificaciones: 2,
    nota_hito: 12,
    observacion: 'Requiere nivelación en procedimientos almacenados.',
  },
  {
    ins_codigo: 1004,
    ins_cedula: '31234567',
    nombres: 'Valeria Colmenárez Silva',
    notasDetalle: {
      1: [25, 25, 24, 25],
      2: [24, 25, 24, 24],
      3: [25, 25, 25, 24, 25],
      4: [25, 25, 25, 25],
    },
    modificaciones: 0,
    nota_hito: 25,
    observacion: 'Sobresaliente en desarrollo de triggers y transacciones.',
  },
  {
    ins_codigo: 1005,
    ins_cedula: '27123987',
    nombres: 'José Leonardo Mendoza',
    notasDetalle: {
      1: [18, 17, 19, 18],
      2: [17, 18, 17, 18],
      3: [19, 18, 20, 19, 18],
      4: [18, 19, 17, 18],
    },
    modificaciones: 0,
    nota_hito: 18,
    observacion: '',
  },
]);

// Control de navegación entre cortes: id del corte (1, 2, 3, 4) o 'CONSOLIDADO'
const corteActivoId = ref<number | 'CONSOLIDADO'>(1);
const searchEstudiante = ref('');

const corteSeleccionado = computed(() => {
  if (corteActivoId.value === 'CONSOLIDADO') return null;
  return cortes.value.find(c => c.id === corteActivoId.value) || null;
});

// Máximo de nota para un cohorte específico
const maxNotaCorte = (corteId: number): number => {
  const c = cortes.value.find(item => item.id === corteId);
  return c ? c.pesoCorte : 25;
};

// Escala máxima representativa (ej: 25 para 4 cohortes, 20 para 5 cohortes, 16.6 para 6 cohortes)
const maxNotaEscala = computed(() => cortes.value[0]?.pesoCorte || 25);

// Máximo de nota para el cohorte activo actualmente en vista
const maxNotaActiva = computed(() => {
  if (corteSeleccionado.value) {
    return corteSeleccionado.value.pesoCorte;
  }
  return maxNotaEscala.value;
});

const sumaPesosCorteActivo = computed(() => {
  if (!corteSeleccionado.value) return 25;
  return corteSeleccionado.value.evaluaciones.reduce((acc, ev) => acc + Number(ev.peso || 0), 0);
});

const tieneNotasRegistradas = (est: EstudianteRegistro, corteId: number): boolean => {
  const notas = est.notasDetalle[corteId];
  if (!notas || notas.length === 0) return false;
  return notas.some(n => n !== null && n !== undefined && n !== '');
};

const calcularNotaCorte = (est: EstudianteRegistro, corteId: number): number => {
  const corte = cortes.value.find(c => c.id === corteId);
  if (!corte) return 0;
  const notasArray = est.notasDetalle[corteId] || [];
  const sumaPesos = corte.evaluaciones.reduce((acc, e) => acc + Number(e.peso || 0), 0) || corte.pesoCorte || 25;

  let totalPonderado = 0;
  corte.evaluaciones.forEach((ev, idx) => {
    const rawNota = notasArray[idx];
    if (rawNota !== null && rawNota !== undefined && rawNota !== '' && !isNaN(Number(rawNota))) {
      // Regla estricta: ninguna calificación individual puede superar el máximo del cohorte dado
      const maxVal = corte.pesoCorte;
      const nota = Math.min(maxVal, Math.max(0, Number(rawNota)));
      totalPonderado += nota * (Number(ev.peso) / sumaPesos);
    }
  });

  return Math.min(corte.pesoCorte, Math.max(0, Number(totalPonderado.toFixed(2))));
};
const calcularNotaCorte25 = calcularNotaCorte;
const calcularPuntosCorte25 = calcularNotaCorte;

const calcularPromedioFinal = (est: EstudianteRegistro): number => {
  if (!cortes.value || cortes.value.length === 0) return 0;
  const suma = cortes.value.reduce((acc, c) => acc + calcularNotaCorte(est, c.id), 0);
  const prom = Number((suma / cortes.value.length).toFixed(2));
  return Math.min(maxNotaEscala.value, Math.max(0, prom));
};
const calcularPromedioFinal25 = calcularPromedioFinal;

const calcularTotalAcumulado100 = (est: EstudianteRegistro): number => {
  if (!cortes.value || cortes.value.length === 0) return 0;
  const total = cortes.value.reduce((acc, c) => {
    return acc + calcularNotaCorte(est, c.id);
  }, 0);
  return Math.min(100, Math.max(0, Number(total.toFixed(2))));
};

const condicionEstudiante = (promedio: number, est?: EstudianteRegistro) => {
  if (est && !cortes.value.some(c => tieneNotasRegistradas(est, c.id))) {
    return { text: 'En Curso', class: 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400' };
  }
  const escala = maxNotaEscala.value;
  const sobresaliente = Number((escala * 0.8).toFixed(2));
  const aprobado = Number((escala * 0.6).toFixed(2));
  const condicional = Number((escala * 0.5).toFixed(2));

  if (promedio >= sobresaliente) return { text: 'Aprobado Sobresaliente', class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400' };
  if (promedio >= aprobado) return { text: 'Aprobado Regular', class: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400' };
  if (promedio >= condicional) return { text: 'Nivelación / Condicional', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400' };
  if (promedio > 0) return { text: 'Reprobado', class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400' };
  return { text: 'Sin Calificar', class: 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-400' };
};

const filteredEstudiantes = computed(() => {
  const q = searchEstudiante.value.trim().toLowerCase();
  if (!q) return estudiantes.value;
  return estudiantes.value.filter(e =>
    e.ins_cedula.toLowerCase().includes(q) || e.nombres.toLowerCase().includes(q)
  );
});

const totalInscritos = computed(() => estudiantes.value.length);
const totalAprobados = computed(() => {
  const notaMinima = maxNotaEscala.value * 0.6;
  return estudiantes.value.filter(e => calcularPromedioFinal(e) >= notaMinima).length;
});
const promedioSeccion = computed(() => {
  if (estudiantes.value.length === 0) return '0.00';
  const suma = estudiantes.value.reduce((acc, e) => acc + calcularPromedioFinal(e), 0);
  return (suma / estudiantes.value.length).toFixed(2);
});

// =========================================================================
// GESTIÓN DINÁMICA DE COHORTES (MÍNIMO Y POR DEFECTO 4, LIBERTAD DE ESCOGER)
// =========================================================================
const toRoman = (num: number): string => {
  const romanMap: [number, string][] = [
    [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I']
  ];
  let result = '';
  let n = num;
  for (const [val, roman] of romanMap) {
    while (n >= val) {
      result += roman;
      n -= val;
    }
  }
  return result || String(num);
};

const distribuirPesosEquitativamente = (corte: CorteConfig) => {
  const n = corte.evaluaciones.length;
  if (n === 0) return;
  if (corte.pesoCorte === 25 && n === 4) {
    corte.evaluaciones[0].peso = 5;
    corte.evaluaciones[1].peso = 5;
    corte.evaluaciones[2].peso = 5;
    corte.evaluaciones[3].peso = 10;
    return;
  }
  if (corte.pesoCorte === 25 && n === 5) {
    corte.evaluaciones.forEach(ev => ev.peso = 5);
    return;
  }
  if (corte.pesoCorte === 20 && n === 4) {
    corte.evaluaciones.forEach(ev => ev.peso = 5);
    return;
  }
  if (corte.pesoCorte === 20 && n === 5) {
    corte.evaluaciones.forEach(ev => ev.peso = 4);
    return;
  }
  const pesoBase = Math.floor((corte.pesoCorte / n) * 10) / 10;
  const resto = Number((corte.pesoCorte - (pesoBase * n)).toFixed(1));
  corte.evaluaciones.forEach((ev, idx) => {
    ev.peso = Number((pesoBase + (idx === n - 1 ? resto : 0)).toFixed(1));
  });
};

const recalcularPesosCohortes = () => {
  const n = cortes.value.length;
  if (n === 0) return;
  const pesoBase = Math.floor((100 / n) * 10) / 10;
  const resto = Number((100 - (pesoBase * n)).toFixed(1));
  cortes.value.forEach((c, idx) => {
    c.pesoCorte = Number((pesoBase + (idx === n - 1 ? resto : 0)).toFixed(1));
    distribuirPesosEquitativamente(c);
  });
};

// Acomodar y sincronizar las notas de todos los estudiantes según la estructura de cohortes y evaluaciones
// Regla: Las notas dependiendo de cuántos cohortes tenga no deben superar el máximo de la nota dada (c.pesoCorte)
const acomodarNotasEstudiantes = () => {
  estudiantes.value.forEach(est => {
    cortes.value.forEach(c => {
      const maxPermitido = c.pesoCorte;
      if (!est.notasDetalle[c.id]) {
        // Si no existen notas para este cohorte, por defecto están en blanco (null)
        est.notasDetalle[c.id] = Array(c.evaluaciones.length).fill(null);
      } else {
        const notasCorte = est.notasDetalle[c.id];
        // Si se agregaron más evaluaciones, rellenar en blanco (null) por defecto
        while (notasCorte.length < c.evaluaciones.length) {
          notasCorte.push(null);
        }
        // Si se redujeron las evaluaciones, recortar
        if (notasCorte.length > c.evaluaciones.length) {
          est.notasDetalle[c.id] = notasCorte.slice(0, c.evaluaciones.length);
        }
        // Clamping estricto: ninguna nota guardada puede superar el máximo del cohorte dado
        notasCorte.forEach((nota, idx) => {
          if (nota !== null && nota !== undefined && !isNaN(Number(nota))) {
            if (Number(nota) > maxPermitido) {
              notasCorte[idx] = maxPermitido;
            } else if (Number(nota) < 0) {
              notasCorte[idx] = 0;
            }
          }
        });
      }
    });

    // Ajustar nota hito si supera el máximo de la escala
    if (est.nota_hito !== null && est.nota_hito !== undefined && !isNaN(Number(est.nota_hito))) {
      if (Number(est.nota_hito) > maxNotaEscala.value) {
        est.nota_hito = maxNotaEscala.value;
      } else if (Number(est.nota_hito) < 0) {
        est.nota_hito = 0;
      }
    }
  });
};

const actualizarCantidadCohortes = (nuevaCantidad: number) => {
  // Regla institucional: mínimo 4 y máximo 6 cohortes (por defecto 4)
  if (nuevaCantidad < 4) {
    nuevaCantidad = 4;
  }
  if (nuevaCantidad > 6) {
    nuevaCantidad = 6;
  }

  const cantidadActual = cortes.value.length;

  if (nuevaCantidad > cantidadActual) {
    for (let i = cantidadActual + 1; i <= nuevaCantidad; i++) {
      const nuevoId = i;
      const nombreCorte = `Corte ${toRoman(nuevoId)}`;
      cortes.value.push({
        id: nuevoId,
        nombre: nombreCorte,
        pesoCorte: 25, // Se recalcula inmediatamente abajo
        cantidadEvaluaciones: 4,
        evaluaciones: [
          { id: 1, nombre: `Evaluación 1: Taller Práctico (${nombreCorte})`, metodologia: 'Taller / Laboratorio', peso: 5, semana: (i * 3) - 2 },
          { id: 2, nombre: `Evaluación 2: Actividad Teórica (${nombreCorte})`, metodologia: 'Prueba Corta (Quiz)', peso: 5, semana: (i * 3) - 1 },
          { id: 3, nombre: `Evaluación 3: Caso de Estudio Aplicado`, metodologia: 'Estudio de Casos', peso: 5, semana: i * 3 },
          { id: 4, nombre: `Evaluación 4: Examen Sumativo (${nombreCorte})`, metodologia: 'Examen Parcial Escrito', peso: 5, semana: (i * 3) + 1 },
        ],
      });

      // Inicializar notas en este cohorte para cada estudiante por defecto en blanco (null)
      estudiantes.value.forEach(est => {
        if (!est.notasDetalle[nuevoId]) {
          est.notasDetalle[nuevoId] = Array(4).fill(null);
        }
      });
    }
  } else if (nuevaCantidad < cantidadActual) {
    cortes.value = cortes.value.slice(0, nuevaCantidad);
    if (typeof corteActivoId.value === 'number' && corteActivoId.value > nuevaCantidad) {
      corteActivoId.value = 1;
    }
    if (corteSeleccionadoParaCarga.value > nuevaCantidad) {
      corteSeleccionadoParaCarga.value = 1;
    }
  }

  recalcularPesosCohortes();
  acomodarNotasEstudiantes();
};

const agregarCohorte = () => {
  if (cortes.value.length < 6) {
    actualizarCantidadCohortes(cortes.value.length + 1);
  }
};

const quitarCohorte = () => {
  if (cortes.value.length > 4) {
    actualizarCantidadCohortes(cortes.value.length - 1);
  }
};

// Cambiar la división del corte entre 4 o 5 evaluaciones
const cambiarDivisionCorte = (corte: CorteConfig, cantidad: 4 | 5) => {
  if (corte.id !== corteSeleccionadoParaCarga.value) return;
  if (corte.cantidadEvaluaciones === cantidad) return;
  corte.cantidadEvaluaciones = cantidad;

  if (cantidad === 4) {
    corte.evaluaciones = [
      { id: 1, nombre: 'Evaluación 1: Actividad Teórica', metodologia: corte.evaluaciones[0]?.metodologia || 'Prueba Corta (Quiz)', peso: 5, semana: 3 },
      { id: 2, nombre: 'Evaluación 2: Taller Práctico', metodologia: corte.evaluaciones[1]?.metodologia || 'Taller / Laboratorio', peso: 5, semana: 5 },
      { id: 3, nombre: 'Evaluación 3: Resolución de Casos', metodologia: corte.evaluaciones[2]?.metodologia || 'Resolución de Problemas / Algoritmos', peso: 5, semana: 7 },
      { id: 4, nombre: 'Evaluación 4: Examen Sumativo', metodologia: corte.evaluaciones[3]?.metodologia || 'Examen Parcial Escrito', peso: 10, semana: 9 },
    ];
  } else {
    corte.evaluaciones = [
      { id: 1, nombre: 'Evaluación 1: Actividad Diagnóstica', metodologia: corte.evaluaciones[0]?.metodologia || 'Prueba Corta (Quiz)', peso: 5, semana: 2 },
      { id: 2, nombre: 'Evaluación 2: Taller Aplicado', metodologia: corte.evaluaciones[1]?.metodologia || 'Taller / Laboratorio', peso: 5, semana: 4 },
      { id: 3, nombre: 'Evaluación 3: Práctica de Laboratorio', metodologia: corte.evaluaciones[2]?.metodologia || 'Taller / Laboratorio', peso: 5, semana: 6 },
      { id: 4, nombre: 'Evaluación 4: Portafolio / Avance', metodologia: corte.evaluaciones[3]?.metodologia || 'Portafolio de Evidencias', peso: 5, semana: 8 },
      { id: 5, nombre: 'Evaluación 5: Prueba Sumativa de Corte', metodologia: 'Examen Parcial Escrito', peso: 5, semana: 10 },
    ];
  }

  distribuirPesosEquitativamente(corte);
  acomodarNotasEstudiantes();
};

// Sincronización inicial
acomodarNotasEstudiantes();

// Modal de configuración de evaluaciones
const modalConfiguracion = ref(false);
const corteEnEdicion = ref<CorteConfig | null>(null);

const abrirConfiguracionCorte = (corte: CorteConfig) => {
  if (corte.id !== corteSeleccionadoParaCarga.value) return;
  corteEnEdicion.value = JSON.parse(JSON.stringify(corte));
  modalConfiguracion.value = true;
};

const guardarConfiguracionCorte = () => {
  if (!corteEnEdicion.value) return;
  const idx = cortes.value.findIndex(c => c.id === corteEnEdicion.value?.id);
  if (idx !== -1) {
    cortes.value[idx] = JSON.parse(JSON.stringify(corteEnEdicion.value));
  }
  modalConfiguracion.value = false;
};

// Soporte de botón "Otro" para metodología
const evaluacionSeleccionadaParaOtraMetodologia = ref<EvaluacionItem | null>(null);
const nuevaMetodologiaInput = ref('');
const mostrarInputOtraMetodologia = ref(false);

const abrirModalOtraMetodologia = (evaluacion: EvaluacionItem) => {
  evaluacionSeleccionadaParaOtraMetodologia.value = evaluacion;
  nuevaMetodologiaInput.value = '';
  mostrarInputOtraMetodologia.value = true;
};

const aplicarOtraMetodologia = () => {
  const textoLimpio = nuevaMetodologiaInput.value.trim();
  if (!textoLimpio) return;

  if (!listaMetodologias.value.includes(textoLimpio)) {
    listaMetodologias.value.push(textoLimpio);
  }

  if (evaluacionSeleccionadaParaOtraMetodologia.value) {
    evaluacionSeleccionadaParaOtraMetodologia.value.metodologia = textoLimpio;
  }

  mostrarInputOtraMetodologia.value = false;
  nuevaMetodologiaInput.value = '';
};

// Observaciones por estudiante
const estudianteObservacion = ref<EstudianteRegistro | null>(null);
const abrirObservaciones = (est: EstudianteRegistro) => {
  estudianteObservacion.value = est;
};

const notificarCambioNota = (est: EstudianteRegistro) => {
  if (!esCorteCorrespondienteHabilitado.value) return;
  est.modificaciones += 1;
};

// =========================================================================
// VALIDACIÓN Y CONTROL ESTRICTO DE NOTAS (ESCALA OFICIAL EXCLUSIVA DE 0 A 25)
// RESTRICCIÓN: SOLAMENTE SE ACEPTAN NÚMEROS Y DECIMALES
// =========================================================================

const validarTeclaNumeroYDecimal = (event: KeyboardEvent) => {
  // Teclas de control y navegación permitidas siempre
  const teclasPermitidas = [
    'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
    'Home', 'End'
  ];

  if (teclasPermitidas.includes(event.key)) {
    return;
  }

  // Permitir atajos del sistema con Ctrl o Meta (Copiar, Pegar, Cortar, Seleccionar Todo, Deshacer)
  if (event.ctrlKey || event.metaKey) {
    if (['a', 'c', 'v', 'x', 'z'].includes(event.key.toLowerCase())) {
      return;
    }
  }

  const input = event.target as HTMLInputElement;

  // Si presiona coma (,), convertirla en punto decimal automáticamente
  if (event.key === ',') {
    event.preventDefault();
    if (!input.value.includes('.')) {
      const start = input.selectionStart ?? input.value.length;
      const end = input.selectionEnd ?? input.value.length;
      const val = input.value;
      input.value = val.substring(0, start) + '.' + val.substring(end);
      input.setSelectionRange(start + 1, start + 1);
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    return;
  }

  // Permitir punto decimal (.) solo si aún no contiene uno
  if (event.key === '.') {
    if (input.value.includes('.')) {
      event.preventDefault();
    }
    return;
  }

  // Permitir únicamente dígitos numéricos del 0 al 9
  if (/^[0-9]$/.test(event.key)) {
    return;
  }

  // Bloquear absolutamente cualquier otra tecla (letras como e/E, signos +/- y caracteres especiales)
  event.preventDefault();
};

const sanitizarPegado = (event: ClipboardEvent) => {
  event.preventDefault();
  const textoPegado = event.clipboardData?.getData('text') || '';
  // Convertir comas en puntos y extraer sólo dígitos y un único punto decimal
  let textoLimpio = textoPegado.replace(/,/g, '.').replace(/[^0-9.]/g, '');

  // Si contiene más de un punto, conservar sólo el primero
  const partes = textoLimpio.split('.');
  if (partes.length > 2) {
    textoLimpio = partes[0] + '.' + partes.slice(1).join('');
  }

  if (textoLimpio === '') return;

  let num = parseFloat(textoLimpio);
  if (!isNaN(num)) {
    const maxPermitido = corteSeleccionado.value ? corteSeleccionado.value.pesoCorte : maxNotaEscala.value;
    num = Math.min(maxPermitido, Math.max(0, num));
    num = Number(num.toFixed(2));
    const input = event.target as HTMLInputElement;
    input.value = String(num);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }
};

const sanitizarNotaInput = (est: EstudianteRegistro, corteId: number, evIdx: number, event: Event) => {
  if (!esCorteCorrespondienteHabilitado.value) return;
  const input = event.target as HTMLInputElement;
  let rawValue = input.value;
  if (!est.notasDetalle[corteId]) {
    est.notasDetalle[corteId] = [];
  }
  if (rawValue === '' || rawValue === null || rawValue === undefined) {
    est.notasDetalle[corteId][evIdx] = null;
    return;
  }
  if (typeof rawValue === 'string') {
    rawValue = rawValue.replace(',', '.');
  }
  let num = parseFloat(rawValue);
  if (isNaN(num)) {
    est.notasDetalle[corteId][evIdx] = null;
    return;
  }
  const maxPermitido = maxNotaCorte(corteId);
  if (num > maxPermitido) {
    num = maxPermitido;
    input.value = String(maxPermitido);
  } else if (num < 0) {
    num = 0;
    input.value = '0';
  }
  est.notasDetalle[corteId][evIdx] = num;
};

const confirmarNotaInput = (est: EstudianteRegistro, corteId: number, evIdx: number, event: Event) => {
  if (!esCorteCorrespondienteHabilitado.value) return;
  const input = event.target as HTMLInputElement;
  if (!est.notasDetalle[corteId]) {
    est.notasDetalle[corteId] = [];
  }
  let rawValue = input.value.trim();
  if (rawValue === '' || rawValue === null) {
    est.notasDetalle[corteId][evIdx] = null;
    input.value = '';
    notificarCambioNota(est);
    return;
  }
  if (typeof rawValue === 'string') {
    rawValue = rawValue.replace(',', '.');
  }
  let num = parseFloat(rawValue);
  if (isNaN(num)) {
    est.notasDetalle[corteId][evIdx] = null;
    input.value = '';
    notificarCambioNota(est);
    return;
  }
  const maxPermitido = maxNotaCorte(corteId);
  if (num > maxPermitido) {
    num = maxPermitido;
  } else if (num < 0) {
    num = 0;
  }
  num = Number(num.toFixed(2));
  input.value = String(num);
  est.notasDetalle[corteId][evIdx] = num;
  notificarCambioNota(est);
};

const sanitizarNotaHito = (est: EstudianteRegistro, event: Event) => {
  const input = event.target as HTMLInputElement;
  let rawValue = input.value;
  if (rawValue === '' || rawValue === null || rawValue === undefined) return;
  if (typeof rawValue === 'string') {
    rawValue = rawValue.replace(',', '.');
  }
  let num = parseFloat(rawValue);
  if (isNaN(num)) return;
  const maxPermitido = maxNotaActiva.value;
  if (num > maxPermitido) {
    num = maxPermitido;
    input.value = String(maxPermitido);
  } else if (num < 0) {
    num = 0;
    input.value = '0';
  }
  est.nota_hito = num;
};

const confirmarNotaHito = (est: EstudianteRegistro, event: Event) => {
  const input = event.target as HTMLInputElement;
  let rawValue = input.value;
  if (typeof rawValue === 'string') {
    rawValue = rawValue.replace(',', '.');
  }
  let num = parseFloat(rawValue);
  const maxPermitido = maxNotaActiva.value;
  if (isNaN(num) || rawValue === '') {
    num = 0;
  } else if (num > maxPermitido) {
    num = maxPermitido;
  } else if (num < 0) {
    num = 0;
  }
  num = Number(num.toFixed(2));
  input.value = String(num);
  est.nota_hito = num;
};

import { useConfirmDialog } from '@/composables/useConfirmDialog';
const { alertDialog } = useConfirmDialog();

const guardarCalificaciones = async () => {
  if (!esCorteCorrespondienteHabilitado.value) {
    await alertDialog(
      'Acción restringida: Este cohorte se encuentra en modo solo lectura. Solo puede calificar y modificar el cohorte correspondiente.',
      'Acción Restringida',
      'warning'
    );
    return;
  }
  await alertDialog(
    `Calificaciones del ${corteSeleccionadoNombre.value} registradas y consolidadas con éxito en el acta oficial.`,
    '¡Calificaciones Consolidadas!',
    'success'
  );
};
</script>

<template>
  <Head title="Carga de Calificaciones Docente - SOGAC" />

  <AppLayout :breadcrumbs="[
    { title: 'Calificaciones', subtitle: 'Plan de Evaluación', href: route('calificaciones.planificacion') },
    { title: 'Carga de Notas', subtitle: 'Registro por Sección', href: route('calificaciones.carga') }
  ]">
    <div class="w-full p-4 sm:p-6 space-y-6">

      <!-- ========================================================================= -->
      <!-- PASO 1 Y 2: FORMULARIOS DE BÚSQUEDA PREVIA (PNF -> SECCIÓN BLOQUEADA)      -->
      <!-- ========================================================================= -->
      <div v-if="pasoActivo === 'FILTRO'" class="space-y-6 animate-in fade-in duration-300">
        
        <!-- ENCABEZADO DEL FLUJO DE SELECCIÓN -->
        <div class="border-b border-slate-200 dark:border-slate-800 pb-5">
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
            <School class="h-3.5 w-3.5" />
            Acceso Docente a Actas de Calificación
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Búsqueda y Selección de Sección Académica
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl mt-1">
            Para cargar notas, primero busque y seleccione el <strong>Programa Nacional de Formación (PNF)</strong>. La búsqueda de sección se desbloqueará automáticamente al elegir el programa.
          </p>

          <!-- INDICADOR DE PASOS -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5">
            <div class="flex items-center gap-3 p-3 rounded-xl border" :class="pnfSeleccionadoId ? 'bg-emerald-50/50 border-emerald-300 text-emerald-800 dark:bg-emerald-950/20 dark:border-emerald-800 dark:text-emerald-300' : 'bg-red-50/60 border-red-300 text-red-800 dark:bg-red-950/30 dark:border-red-800 dark:text-red-200'">
              <div class="flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs" :class="pnfSeleccionadoId ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'">
                1
              </div>
              <div class="text-xs">
                <span class="font-bold block">Paso 1: Seleccionar PNF</span>
                <span class="text-[11px] opacity-80">{{ pnfSeleccionado ? pnfSeleccionado.nombre : 'Pendiente por buscar' }}</span>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl border transition-all" :class="!pnfSeleccionadoId ? 'bg-slate-100/60 border-slate-200 text-slate-400 dark:bg-slate-900/40 dark:border-slate-800' : seccionSeleccionadaId ? 'bg-emerald-50/50 border-emerald-300 text-emerald-800 dark:bg-emerald-950/20 dark:border-emerald-800 dark:text-emerald-300' : 'bg-amber-50/60 border-amber-300 text-amber-800 dark:bg-amber-950/30 dark:border-amber-800 dark:text-amber-200'">
              <div class="flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs" :class="!pnfSeleccionadoId ? 'bg-slate-300 text-slate-600 dark:bg-slate-800 dark:text-slate-400' : seccionSeleccionadaId ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white'">
                2
              </div>
              <div class="text-xs">
                <span class="font-bold flex items-center gap-1.5">
                  Paso 2: Elegir Sección
                  <Lock v-if="!pnfSeleccionadoId" class="h-3.5 w-3.5 text-slate-400" />
                  <Unlock v-else class="h-3.5 w-3.5 text-emerald-600" />
                </span>
                <span class="text-[11px] opacity-80">
                  {{ !pnfSeleccionadoId ? 'Bloqueado (requiere PNF)' : seccionSeleccionada ? seccionSeleccionada.codigo : 'Disponible para elegir' }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-xl border" :class="puedeAvanzar ? 'bg-red-50/50 border-red-300 text-red-800 dark:bg-red-950/20 dark:border-red-800 dark:text-red-300' : 'bg-slate-100/60 border-slate-200 text-slate-400 dark:bg-slate-900/40 dark:border-slate-800'">
              <div class="flex h-7 w-7 items-center justify-center rounded-full font-bold text-xs" :class="puedeAvanzar ? 'bg-red-600 text-white' : 'bg-slate-300 text-slate-600 dark:bg-slate-800 dark:text-slate-400'">
                3
              </div>
              <div class="text-xs">
                <span class="font-bold block">Paso 3: Cargar Notas de Estudiantes</span>
                <span class="text-[11px] opacity-80">{{ puedeAvanzar ? `${corteSeleccionadoNombre} (${corteSeleccionadoPeso}%)` : 'Esperando selección' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- GRILLA DE LOS 2 FORMULARIOS DE BÚSQUEDA -->
        <div class="grid gap-6 md:grid-cols-2 items-start">
          
          <!-- FORMULARIO 1: BÚSQUEDA Y SELECTOR DE PNF -->
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
            <div class="h-1.5 bg-red-600 w-full"></div>
            <CardHeader class="pb-3">
              <div class="flex items-center justify-between">
                <CardTitle class="text-base font-bold flex items-center gap-2 text-slate-900 dark:text-slate-100">
                  <GraduationCap class="h-5 w-5 text-red-600" />
                  1. Búsqueda de PNF
                </CardTitle>
                <Badge variant="outline" class="text-[10px] font-mono">Paso 1 Obligatorio</Badge>
              </div>
              <CardDescription class="text-xs">
                Escriba el nombre para filtrar en tiempo real y elija el programa de la lista desplegable.
              </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4 pt-1">
              <!-- Input de búsqueda en tiempo real de PNF -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Búsqueda en tiempo real por PNF:
                </label>
                <div class="relative">
                  <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                  <input
                    v-model="busquedaPNFTexto"
                    type="text"
                    placeholder="Ej. PNF-Informática, PNF-Administración, PNF-Electricidad..."
                    class="input-base w-full pl-9 text-xs h-9"
                  />
                  <span v-if="busquedaPNFTexto" class="absolute right-3 top-2.5 text-[10px] text-slate-400">
                    {{ pnfsFiltrados.length }} encontrado(s)
                  </span>
                </div>
              </div>

              <!-- Selector tipo Select interactivo -->
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                  Seleccionar PNF de la lista:
                </label>
                <select
                  v-model="pnfSeleccionadoId"
                  class="input-base w-full text-xs font-semibold cursor-pointer h-10"
                >
                  <option :value="null">-- Seleccione un PNF --</option>
                  <option
                    v-for="pnf in pnfsFiltrados"
                    :key="pnf.id"
                    :value="pnf.id"
                  >
                    {{ pnf.nombre }} ({{ pnf.area }})
                  </option>
                </select>
              </div>

              <!-- Vista previa del PNF seleccionado -->
              <div v-if="pnfSeleccionado" class="p-3.5 rounded-xl bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 text-xs space-y-1.5 animate-in fade-in">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-red-700 dark:text-red-300 text-sm">
                    {{ pnfSeleccionado.nombre }}
                  </span>
                  <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                    <CheckCircle class="h-3.5 w-3.5" /> PNF Seleccionado
                  </span>
                </div>
                <p class="text-[11px] text-slate-500">
                  {{ pnfSeleccionado.descripcion }}
                </p>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-medium">
                  Área: {{ pnfSeleccionado.area }}
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- FORMULARIO 2: BÚSQUEDA Y SELECTOR DE SECCIÓN (BLOQUEADO HASTA ELEGIR PNF) -->
          <Card
            class="shadow-sm relative overflow-hidden transition-all duration-300 border"
            :class="!pnfSeleccionadoId
              ? 'border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/30 opacity-75'
              : 'border-emerald-300 dark:border-emerald-900/60 bg-white dark:bg-slate-900'"
          >
            <div class="h-1.5 w-full" :class="!pnfSeleccionadoId ? 'bg-slate-300 dark:bg-slate-700' : 'bg-emerald-600'"></div>
            <CardHeader class="pb-3">
              <div class="flex items-center justify-between">
                <CardTitle class="text-base font-bold flex items-center gap-2" :class="!pnfSeleccionadoId ? 'text-slate-400' : 'text-slate-900 dark:text-slate-100'">
                  <BookOpen class="h-5 w-5" :class="!pnfSeleccionadoId ? 'text-slate-400' : 'text-emerald-600'" />
                  2. Búsqueda de Sección Académica
                </CardTitle>
                <Badge
                  variant="outline"
                  class="text-[10px] font-mono flex items-center gap-1"
                  :class="!pnfSeleccionadoId ? 'border-slate-300 text-slate-400' : 'border-emerald-300 text-emerald-600'"
                >
                  <Lock v-if="!pnfSeleccionadoId" class="h-3 w-3" />
                  <Unlock v-else class="h-3 w-3" />
                  {{ !pnfSeleccionadoId ? 'Bloqueado' : 'Desbloqueado' }}
                </Badge>
              </div>
              <CardDescription class="text-xs">
                {{ !pnfSeleccionadoId ? 'Este formulario permanecerá inactivo hasta que seleccione un PNF en el formulario 1.' : 'Filtre en tiempo real por código o materia y elija la sección correspondiente.' }}
              </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4 pt-1">
              
              <!-- AVISO VISUAL SI ESTÁ BLOQUEADO -->
              <div v-if="!pnfSeleccionadoId" class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-800/40 text-center space-y-2 py-8">
                <div class="h-10 w-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center mx-auto text-slate-500">
                  <Lock class="h-5 w-5" />
                </div>
                <h4 class="font-bold text-xs text-slate-700 dark:text-slate-300">Sección Académica Bloqueada</h4>
                <p class="text-[11px] text-slate-500 max-w-xs mx-auto">
                  Seleccione primero un PNF en el formulario de la izquierda para cargar y habilitar sus secciones correspondientes.
                </p>
              </div>

              <!-- CONTENIDO HABILITADO AL SELECCIONAR PNF -->
              <div v-else class="space-y-4 animate-in fade-in">
                <!-- Input de búsqueda en tiempo real de sección -->
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Búsqueda en tiempo real por sección o materia:
                  </label>
                  <div class="relative">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                      v-model="busquedaSeccionTexto"
                      type="text"
                      placeholder="Ej. INF-401, Bases de Datos, Trayecto IV..."
                      class="input-base w-full pl-9 text-xs h-9"
                    />
                    <span v-if="busquedaSeccionTexto" class="absolute right-3 top-2.5 text-[10px] text-slate-400">
                      {{ seccionesFiltradas.length }} encontrada(s)
                    </span>
                  </div>
                </div>

                <!-- Selector tipo Select interactivo de sección -->
                <div>
                  <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Seleccionar Sección de {{ pnfSeleccionado?.nombre }}:
                  </label>
                  <select
                    v-model="seccionSeleccionadaId"
                    class="input-base w-full text-xs font-semibold cursor-pointer h-10"
                  >
                    <option :value="null">-- Seleccione una sección ofertada --</option>
                    <option
                      v-for="sec in seccionesFiltradas"
                      :key="sec.id"
                      :value="sec.id"
                    >
                      {{ sec.codigo }} — {{ sec.materia }} ({{ sec.trayecto }} • {{ sec.estudiantes }} est.)
                    </option>
                  </select>
                </div>

                <!-- Vista previa de la sección seleccionada y selección de cohorte evaluativo -->
                <div v-if="seccionSeleccionada" class="p-3.5 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 text-xs space-y-3 animate-in fade-in">
                  <div class="flex items-center justify-between">
                    <span class="font-mono text-[11px] font-bold text-emerald-800 dark:text-emerald-300 px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950">
                      {{ seccionSeleccionada.codigo }}
                    </span>
                    <Badge variant="outline" class="font-mono text-[10px] text-emerald-700 border-emerald-300">
                      {{ seccionSeleccionada.estudiantes }} Alumnos Inscritos
                    </Badge>
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">
                      {{ seccionSeleccionada.materia }}
                    </div>
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center gap-2 pt-1 flex-wrap">
                      <span>{{ seccionSeleccionada.trayecto }}</span>
                      <span>•</span>
                      <span>{{ seccionSeleccionada.turno }}</span>
                      <span>•</span>
                      <span>{{ seccionSeleccionada.aula }}</span>
                      <span>•</span>
                      <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ seccionSeleccionada.periodo }}</span>
                    </div>
                  </div>

                  <!-- CONFIGURACIÓN Y SELECCIÓN DE COHORTES EVALUATIVOS (MÍNIMO 4, MÁXIMO 6) -->
                  <div class="pt-3 border-t border-emerald-200/70 dark:border-emerald-900/50 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white/80 dark:bg-slate-900/80 p-2.5 rounded-xl border border-emerald-300 dark:border-emerald-800">
                      <div>
                        <div class="flex items-center gap-1.5">
                          <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs">
                            Planificación de Cohortes Evaluativos:
                          </label>
                          <Badge variant="outline" class="text-[9px] font-mono text-emerald-700 border-emerald-300">
                            4 a 6 Cohortes (Máximo 6)
                          </Badge>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                          Mínimo y por defecto 4 cohortes, hasta un máximo de 6. El docente puede elegir entre 4, 5 o 6 cohortes.
                        </p>
                      </div>

                      <!-- Control de Cantidad de Cohortes -->
                      <div class="flex items-center gap-1.5 self-start sm:self-auto">
                        <div class="inline-flex items-center bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-300 dark:border-slate-700 p-0.5 shadow-sm">
                          <button
                            type="button"
                            @click="quitarCohorte"
                            :disabled="cortes.length <= 4"
                            class="h-7 w-7 rounded flex items-center justify-center text-xs font-bold transition-all"
                            :class="cortes.length <= 4 ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'"
                            title="El mínimo reglamentario es de 4 cohortes"
                          >
                            -
                          </button>
                          <span class="px-2.5 text-xs font-extrabold text-red-600 tabular-nums">
                            {{ cortes.length }} Cohortes
                          </span>
                          <button
                            type="button"
                            @click="agregarCohorte"
                            :disabled="cortes.length >= 6"
                            class="h-7 w-7 rounded flex items-center justify-center text-xs font-bold transition-all"
                            :class="cortes.length >= 6 ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer'"
                            :title="cortes.length >= 6 ? 'El máximo reglamentario es de 6 cohortes' : 'Añadir un nuevo cohorte evaluativo (máx. 6)'"
                          >
                            +
                          </button>
                        </div>

                        <!-- Atajos directos: 4, 5 o 6 -->
                        <div class="flex items-center gap-1">
                          <button
                            type="button"
                            @click="actualizarCantidadCohortes(4)"
                            class="px-2 py-1 rounded text-[11px] font-bold border transition-colors cursor-pointer"
                            :class="cortes.length === 4 ? 'bg-red-600 text-white border-red-600 shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-red-400'"
                          >
                            4 (Defecto)
                          </button>
                          <button
                            type="button"
                            @click="actualizarCantidadCohortes(5)"
                            class="px-2 py-1 rounded text-[11px] font-bold border transition-colors cursor-pointer"
                            :class="cortes.length === 5 ? 'bg-red-600 text-white border-red-600 shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-red-400'"
                          >
                            5
                          </button>
                          <button
                            type="button"
                            @click="actualizarCantidadCohortes(6)"
                            class="px-2 py-1 rounded text-[11px] font-bold border transition-colors cursor-pointer"
                            :class="cortes.length === 6 ? 'bg-red-600 text-white border-red-600 shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-red-400'"
                          >
                            6 (Máx)
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Cuadrícula para seleccionar cuál cohorte calificar en el Paso 3 -->
                    <div class="space-y-1.5">
                      <label class="block font-semibold text-slate-800 dark:text-slate-200 text-xs">
                        Seleccione el Cohorte a Calificar en el Paso 3:
                      </label>
                      <div
                        class="grid gap-1.5"
                        :class="cortes.length >= 6 ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6' : (cortes.length === 5 ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6' : 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5')"
                      >
                        <button
                          v-for="c in cortes"
                          :key="c.id"
                          type="button"
                          @click="corteSeleccionadoParaCarga = c.id"
                          class="rounded-lg border text-center transition-all font-semibold flex flex-col items-center justify-center gap-0.5 cursor-pointer"
                          :class="[
                            cortes.length >= 5 ? 'p-1.5 text-xs' : 'p-2 text-xs',
                            corteSeleccionadoParaCarga === c.id
                              ? 'border-red-600 bg-red-600 text-white shadow-sm ring-2 ring-red-400/40'
                              : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-red-400'
                          ]"
                        >
                          <span class="font-bold">{{ c.nombre }}</span>
                          <span class="text-[10px] opacity-85">{{ c.pesoCorte }}% • {{ c.cantidadEvaluaciones }} evals</span>
                        </button>

                        <!-- Botón añadir cohorte dinámico (hasta 6) -->
                        <button
                          v-if="cortes.length < 6"
                          type="button"
                          @click="agregarCohorte"
                          class="rounded-lg border border-dashed border-red-300 dark:border-red-800 bg-red-50/50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-center transition-all font-semibold flex flex-col items-center justify-center gap-0.5 hover:bg-red-100/60 dark:hover:bg-red-950/40 cursor-pointer"
                          :class="cortes.length >= 5 ? 'p-1.5 text-xs' : 'p-2 text-xs'"
                          title="Añadir otro cohorte de evaluación (máximo 6)"
                        >
                          <span class="flex items-center gap-1 font-bold"><Plus class="h-3 w-3" /> Añadir Cohorte</span>
                          <span class="text-[10px] opacity-75">{{ cortes.length }}/6 cohortes</span>
                        </button>
                      </div>

                      <p class="text-[10px] text-slate-500 mt-1">
                        Al pulsar «Siguiente», pasará al <strong>Paso 3</strong> para que coloque directamente las notas de los estudiantes de la <strong>{{ seccionSeleccionada.periodo }}</strong> en el <strong>{{ corteSeleccionadoNombre }} ({{ corteSeleccionadoPeso }}%)</strong>.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

            </CardContent>
          </Card>
        </div>

        <!-- BARRA INFERIOR CON EL BOTÓN "SIGUIENTE" -->
        <Card class="border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
          <CardContent class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 block">
                Estado de la consulta:
              </span>
              <div class="flex items-center gap-4 text-xs text-slate-500 flex-wrap">
                <span class="flex items-center gap-1.5" :class="pnfSeleccionadoId ? 'text-emerald-600 font-semibold' : 'text-slate-400'">
                  <CheckCircle2 v-if="pnfSeleccionadoId" class="h-4 w-4" />
                  <span v-else class="h-4 w-4 rounded-full border border-slate-300 inline-block"></span>
                  1. PNF: {{ pnfSeleccionado ? pnfSeleccionado.nombre : 'No seleccionado' }}
                </span>
                <span class="flex items-center gap-1.5" :class="seccionSeleccionadaId ? 'text-emerald-600 font-semibold' : 'text-slate-400'">
                  <CheckCircle2 v-if="seccionSeleccionadaId" class="h-4 w-4" />
                  <span v-else class="h-4 w-4 rounded-full border border-slate-300 inline-block"></span>
                  2. Sección: {{ seccionSeleccionada ? seccionSeleccionada.codigo : 'No seleccionada' }}
                </span>
                <span class="flex items-center gap-1.5" :class="seccionSeleccionadaId ? 'text-emerald-600 font-semibold' : 'text-slate-400'">
                  <CheckCircle2 v-if="seccionSeleccionadaId" class="h-4 w-4" />
                  <span v-else class="h-4 w-4 rounded-full border border-slate-300 inline-block"></span>
                  3. Cohorte a Calificar: {{ corteSeleccionadoNombre }} ({{ corteSeleccionadoPeso }}%)
                </span>
              </div>
            </div>

            <div>
              <Button
                size="lg"
                :disabled="!puedeAvanzar"
                @click="avanzarACarga"
                class="w-full sm:w-auto h-11 px-6 font-bold text-sm shadow-md transition-all gap-2"
                :class="puedeAvanzar
                  ? 'bg-red-600 hover:bg-red-700 text-white cursor-pointer'
                  : 'bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-600 cursor-not-allowed'"
              >
                <span>Paso 3: Cargar Notas de Estudiantes ({{ corteSeleccionadoNombre }} - {{ corteSeleccionadoPeso }}%)</span>
                <ArrowRight class="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>

      </div>

      <!-- ========================================================================= -->
      <!-- PASO 3: FORMULARIO Y MATRIZ DE CARGA DE CALIFICACIONES (LO QUE YA ESTABA) -->
      <!-- ========================================================================= -->
      <div v-else class="space-y-6 animate-in fade-in duration-300">

        <!-- BARRA RESUMEN CON BOTÓN PARA REGRESAR A FILTRAR O CAMBIAR SECCIÓN -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center gap-3">
            <Button variant="outline" size="sm" @click="volverAFiltro" class="gap-1.5 shrink-0">
              <ArrowLeft class="h-4 w-4" />
              Cambiar PNF / Sección / Corte
            </Button>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-bold text-red-600 px-2 py-0.5 rounded bg-red-100 dark:bg-red-950">
                  {{ seccionSeleccionada?.codigo || 'INF-401' }}
                </span>
                <span class="text-xs text-slate-500 font-medium">
                  {{ pnfSeleccionado?.nombre || 'PNF-Informática' }}
                </span>
                <span class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-900">
                  {{ seccionSeleccionada?.periodo || 'Cohorte 2026-I' }}
                </span>
                <span
                  class="text-xs font-bold px-2.5 py-0.5 rounded border flex items-center gap-1.5"
                  :class="corteSeleccionado
                    ? esCorteCorrespondienteHabilitado
                      ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border-emerald-300'
                      : 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border-amber-300'
                    : 'text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border-slate-300'"
                >
                  <span v-if="corteSeleccionado" class="flex items-center gap-1">
                    <Unlock v-if="esCorteCorrespondienteHabilitado" class="h-3.5 w-3.5 text-emerald-600" />
                    <Lock v-else class="h-3.5 w-3.5 text-amber-600" />
                    <span>{{ corteSeleccionado.nombre }} ({{ corteSeleccionado.pesoCorte }}%)</span>
                    <span class="text-[10px] font-normal opacity-85">
                      • {{ esCorteCorrespondienteHabilitado ? 'Habilitado para Modificar' : 'Solo Lectura' }}
                    </span>
                  </span>
                  <span v-else>Resumen Consolidado</span>
                </span>
              </div>
              <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 mt-1">
                Paso 3: Carga de Calificaciones — {{ seccionSeleccionada?.materia || 'Bases de Datos Avanzadas' }}
              </h2>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Selector rápido del cohorte correspondiente a modificar -->
            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
              <span class="text-[11px] text-slate-500 font-semibold">Cohorte a Modificar:</span>
              <select
                :value="corteSeleccionadoParaCarga"
                @change="establecerCohorteCorrespondiente(Number(($event.target as HTMLSelectElement).value))"
                class="text-xs font-bold bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded px-2 py-0.5 text-red-600 cursor-pointer"
                title="Cambiar el cohorte correspondiente habilitado para colocar y modificar notas"
              >
                <option v-for="c in cortes" :key="c.id" :value="c.id">
                  {{ c.nombre }} ({{ c.pesoCorte }}%)
                </option>
              </select>
            </div>

            <Button variant="outline" size="sm" @click="guardarCalificaciones" class="text-slate-700 dark:text-slate-200">
              <Printer class="h-4 w-4 mr-1.5" />
              Imprimir Acta
            </Button>
            <Button
              size="sm"
              :disabled="!esCorteCorrespondienteHabilitado"
              class="transition-all"
              :class="esCorteCorrespondienteHabilitado ? 'bg-red-600 hover:bg-red-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-600 cursor-not-allowed'"
              @click="guardarCalificaciones"
            >
              <Save class="h-4 w-4 mr-1.5" />
              {{ esCorteCorrespondienteHabilitado ? `Guardar Notas del ${corteSeleccionado?.nombre}` : 'Solo Lectura (Bloqueado)' }}
            </Button>
          </div>
        </div>

        <!-- KPI METRICS DE LA SECCIÓN -->
        <div class="grid gap-3 sm:grid-cols-4">
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm p-4">
            <span class="text-xs text-slate-500 font-medium">Estudiantes Inscritos</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ totalInscritos }}</div>
            <span class="text-xs text-slate-400">Matrícula activa en sección</span>
          </Card>
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm p-4">
            <span class="text-xs text-slate-500 font-medium">Estudiantes Aprobados</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ totalAprobados }}</div>
            <span class="text-xs text-emerald-600">{{ ((totalAprobados / (totalInscritos || 1)) * 100).toFixed(0) }}% de aprobación</span>
          </Card>
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm p-4">
            <span class="text-xs text-slate-500 font-medium">Promedio de la Sección</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ promedioSeccion }}</div>
            <span class="text-xs text-slate-400">Promedio general de la sección</span>
          </Card>
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm p-4">
            <div class="flex items-center justify-between">
              <span class="text-xs text-slate-500 font-medium">Esquema de Cohortes</span>
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  @click="quitarCohorte"
                  :disabled="cortes.length <= 4"
                  class="h-5 w-5 rounded border border-slate-200 dark:border-slate-700 flex items-center justify-center text-[11px] font-bold transition-colors"
                  :class="cortes.length <= 4 ? 'opacity-30 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 cursor-pointer'"
                  title="Quitar último cohorte (mínimo 4)"
                >-</button>
                <button
                  type="button"
                  @click="agregarCohorte"
                  :disabled="cortes.length >= 6"
                  class="h-5 w-5 rounded border border-slate-200 dark:border-slate-700 flex items-center justify-center text-[11px] font-bold transition-colors"
                  :class="cortes.length >= 6 ? 'opacity-30 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer'"
                  :title="cortes.length >= 6 ? 'Máximo alcanzado (6 cohortes)' : 'Añadir cohorte evaluativo (máximo 6)'"
                >+</button>
              </div>
            </div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ cortes.length }} Cohortes</div>
            <span class="text-xs text-slate-500">{{ cortes[0]?.pesoCorte }}% por cohorte (máx. 6)</span>
          </Card>
        </div>

        <!-- BARRA DE NAVEGACIÓN ENTRE VISTAS: CONSOLIDADO VS CORTES DESGLOSADOS -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 bg-slate-100/80 dark:bg-slate-900/60 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-1 overflow-x-auto w-full xl:w-auto scrollbar-thin pb-1 xl:pb-0">
            <button
              type="button"
              @click="corteActivoId = 'CONSOLIDADO'"
              class="rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
              :class="[
                cortes.length >= 5 ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-xs',
                corteActivoId === 'CONSOLIDADO'
                  ? 'bg-white dark:bg-slate-800 text-red-600 dark:text-red-400 shadow-sm'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
              ]"
            >
              <Layers class="h-4 w-4" />
              Resumen General ({{ cortes.length }} Cohortes)
            </button>

            <button
              v-for="corte in cortes"
              :key="corte.id"
              type="button"
              @click="corteActivoId = corte.id"
              class="rounded-xl font-bold transition-all shrink-0 flex items-center gap-1.5 cursor-pointer whitespace-nowrap"
              :class="[
                cortes.length >= 6 ? 'px-2 py-1.5 text-[11px]' : (cortes.length === 5 ? 'px-2.5 py-1.5 text-xs' : 'px-3.5 py-2 text-xs'),
                corteActivoId === corte.id
                  ? 'bg-red-600 text-white shadow-md'
                  : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
              ]"
            >
              <span class="flex items-center gap-1">
                <span v-if="corte.id === corteSeleccionadoParaCarga" class="text-xs" title="Cohorte Habilitado para Modificación">✏️</span>
                <Lock v-else class="h-3 w-3 opacity-60" title="Cohorte Bloqueado (Solo lectura)" />
                {{ corte.nombre }} <span class="opacity-80">({{ corte.pesoCorte }}%)</span>
              </span>
              <span
                class="px-1.5 py-0.5 rounded-full text-[10px]"
                :class="corteActivoId === corte.id ? 'bg-red-800 text-red-100' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
              >
                {{ corte.id === corteSeleccionadoParaCarga ? 'Habilitado' : (cortes.length >= 5 ? 'Lectura' : 'Solo lectura') }}
              </span>
            </button>

            <!-- Botón dinámico para añadir cohorte (máximo 6) -->
            <button
              v-if="cortes.length < 6"
              type="button"
              @click="agregarCohorte"
              class="px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 flex items-center gap-1 border border-dashed border-red-300 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 cursor-pointer whitespace-nowrap"
              title="Agregar un nuevo cohorte evaluativo (máximo 6)"
            >
              <Plus class="h-3.5 w-3.5" />
              <span>+ Cohorte ({{ cortes.length }}/6)</span>
            </button>

            <!-- Botón para quitar el último si > 4 -->
            <button
              v-if="cortes.length > 4"
              type="button"
              @click="quitarCohorte"
              class="px-2 py-1.5 rounded-xl text-[11px] font-medium transition-all shrink-0 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer whitespace-nowrap"
              :title="`Quitar último cohorte (${cortes[cortes.length - 1].nombre})`"
            >
              &times; Quitar último
            </button>
          </div>

          <div class="relative w-full xl:w-64 px-1 shrink-0">
            <Search class="absolute left-4 top-2.5 h-4 w-4 text-slate-400" />
            <input
              v-model="searchEstudiante"
              type="text"
              placeholder="Buscar por cédula o nombre..."
              class="input-base w-full pl-9 h-9 text-xs"
            />
          </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SUB-VISTA A: CORTE ESPECÍFICO DESGLOSADO EN 4 O 5 EVALUACIONES            -->
        <!-- ========================================================================= -->
        <div v-if="corteSeleccionado" class="space-y-4 animate-in fade-in duration-300">
          
          <!-- BANNER DE PROTECCIÓN DE COHORTE -->
          <div
            v-if="!esCorteCorrespondienteHabilitado"
            class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800/80 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-sm"
          >
            <div class="flex items-center gap-2.5">
              <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200 shrink-0">
                <Lock class="h-4 w-4" />
              </div>
              <div>
                <span class="font-bold block">Cohorte en Modo Solo Lectura (Bloqueado para modificación)</span>
                <span class="text-[11px] text-amber-800 dark:text-amber-300">
                  Por norma académica, solo puede poner y modificar calificaciones en el <strong>cohorte correspondiente habilitado ({{ corteSeleccionadoNombre }})</strong>. Las notas de este cohorte están protegidas.
                </span>
              </div>
            </div>
            <Button
              size="sm"
              variant="outline"
              @click="establecerCohorteCorrespondiente(corteSeleccionado.id)"
              class="gap-1.5 shrink-0 bg-white dark:bg-slate-900 border-amber-400 text-amber-900 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-950 cursor-pointer font-bold text-xs h-8"
            >
              <Unlock class="h-3.5 w-3.5 text-amber-600" />
              Habilitar {{ corteSeleccionado.nombre }} para Modificar
            </Button>
          </div>

          <div
            v-else
            class="p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 flex items-center justify-between text-xs shadow-xs"
          >
            <div class="flex items-center gap-2">
              <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-[11px]">✓</span>
              <span class="font-semibold">
                Este es el <strong>Cohorte Correspondiente Habilitado</strong>: Puede ingresar, calificar y modificar las notas de los estudiantes con total libertad.
              </span>
            </div>
            <Badge variant="outline" class="font-mono text-[10px] text-emerald-700 bg-white dark:bg-slate-900 border-emerald-300">
              Modificación Activa
            </Badge>
          </div>

          <Card class="border-red-200 bg-red-50/30 dark:border-red-950/60 dark:bg-red-950/10 shadow-sm">
            <CardContent class="p-4 sm:p-5">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <Badge variant="outline" class="font-bold text-red-600 border-red-300 dark:border-red-900 bg-white dark:bg-slate-900">
                      Ponderación Oficial: {{ corteSeleccionado.pesoCorte }}%
                    </Badge>
                    <Badge variant="outline" class="font-bold text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/40">
                      Escala Oficial: 0 a {{ corteSeleccionado.pesoCorte }} pts
                    </Badge>
                    <span class="text-xs text-slate-500 font-medium">
                      Suma planificada: <strong :class="sumaPesosCorteActivo === corteSeleccionado.pesoCorte ? 'text-emerald-600' : 'text-amber-600'">{{ sumaPesosCorteActivo }}%</strong> de {{ corteSeleccionado.pesoCorte }}%
                    </span>
                  </div>
                  <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-1">
                    Desglose de Calificaciones — {{ corteSeleccionado.nombre }}
                  </h2>
                  <p class="text-xs text-slate-500 mt-0.5">
                    Desglose del {{ corteSeleccionado.pesoCorte }}% en <strong>{{ corteSeleccionado.cantidadEvaluaciones }} evaluaciones sumativas</strong> (calificadas estrictamente de <strong>0 a {{ corteSeleccionado.pesoCorte }} puntos</strong>) con metodologías configurables.
                  </p>
                </div>

                <!-- SELECTOR INTERACTIVO: 4 O 5 EVALUACIONES (DESHABILITADO SI NO ES EL CORRESPONDIENTE) -->
                <div class="flex items-center gap-2 flex-wrap">
                  <div
                    class="flex items-center bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-300 dark:border-slate-700 shadow-sm"
                    :class="{ 'opacity-60 cursor-not-allowed': !esCorteCorrespondienteHabilitado }"
                  >
                    <span class="text-[11px] font-semibold text-slate-500 px-2.5">Dividir Corte en:</span>
                    <button
                      type="button"
                      :disabled="!esCorteCorrespondienteHabilitado"
                      @click="cambiarDivisionCorte(corteSeleccionado, 4)"
                      class="px-3 py-1 rounded-lg text-xs font-bold transition-colors"
                      :class="corteSeleccionado.cantidadEvaluaciones === 4
                        ? 'bg-red-600 text-white shadow-sm'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      4 Evaluaciones
                    </button>
                    <button
                      type="button"
                      :disabled="!esCorteCorrespondienteHabilitado"
                      @click="cambiarDivisionCorte(corteSeleccionado, 5)"
                      class="px-3 py-1 rounded-lg text-xs font-bold transition-colors"
                      :class="corteSeleccionado.cantidadEvaluaciones === 5
                        ? 'bg-red-600 text-white shadow-sm'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    >
                      5 Evaluaciones
                    </button>
                  </div>

                  <Button
                    size="sm"
                    variant="outline"
                    :disabled="!esCorteCorrespondienteHabilitado"
                    @click="abrirConfiguracionCorte(corteSeleccionado)"
                    class="gap-1.5 h-9 bg-white dark:bg-slate-900"
                    :class="{ 'opacity-60 cursor-not-allowed': !esCorteCorrespondienteHabilitado }"
                  >
                    <SlidersHorizontal class="h-3.5 w-3.5 text-red-600" />
                    Configurar Metodologías y Pesos
                  </Button>
                </div>
              </div>

              <!-- FICHAS RÁPIDAS DE CADA EVALUACIÓN -->
              <div class="grid gap-2.5 mt-4" :class="corteSeleccionado.cantidadEvaluaciones === 5 ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5' : 'grid-cols-2 sm:grid-cols-4'">
                <div
                  v-for="(ev, idx) in corteSeleccionado.evaluaciones"
                  :key="ev.id"
                  class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1.5 text-xs shadow-sm hover:border-red-300 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Eval {{ idx + 1 }}</span>
                    <Badge variant="outline" class="font-mono text-[10px] bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-300">
                      {{ ev.peso }}%
                    </Badge>
                  </div>
                  <div class="font-medium text-slate-900 dark:text-slate-100 truncate" :title="ev.nombre">
                    {{ ev.nombre }}
                  </div>
                  <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span class="truncate text-slate-600 dark:text-slate-400 font-medium" :title="ev.metodologia">
                      {{ ev.metodologia }}
                    </span>
                    <button
                      v-if="esCorteCorrespondienteHabilitado"
                      type="button"
                      @click="abrirModalOtraMetodologia(ev)"
                      class="text-[10px] text-red-600 dark:text-red-400 font-bold hover:underline shrink-0 ml-1"
                    >
                      + Otro
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- TABLA DE CARGA DE NOTAS POR EVALUACIÓN -->
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm">
            <CardContent class="p-0">
              <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[780px]">
                  <thead class="bg-slate-100 text-xs uppercase text-slate-700 dark:bg-slate-900 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                      <th class="px-4 py-3 font-bold w-12 text-center">N°</th>
                      <th class="px-4 py-3 font-bold w-32">Cédula</th>
                      <th class="px-4 py-3 font-bold min-w-[160px]">Estudiante</th>

                      <th
                        v-for="(ev, idx) in corteSeleccionado.evaluaciones"
                        :key="ev.id"
                        class="px-2 py-3 text-center bg-slate-50/80 dark:bg-slate-800/40 border-l border-slate-200 dark:border-slate-800"
                      >
                        <div class="font-bold text-slate-900 dark:text-slate-100">Eval {{ idx + 1 }}</div>
                        <div class="text-[10px] text-red-600 font-semibold">{{ ev.peso }}%</div>
                        <div class="text-[9px] text-slate-500 font-normal truncate max-w-[120px] mx-auto">{{ ev.metodologia }}</div>
                        <div class="text-[9px] text-slate-400 font-semibold mt-0.5">(0-{{ corteSeleccionado.pesoCorte }} pts)</div>
                      </th>

                      <th class="px-4 py-3 text-center bg-red-100/60 text-red-900 dark:bg-red-950/60 dark:text-red-200 font-bold w-32">
                        <div>Nota Cohorte</div>
                        <div class="text-[10px] font-semibold text-red-700 dark:text-red-300">/ {{ corteSeleccionado.pesoCorte }}%</div>
                      </th>

                      <th class="px-4 py-3 text-center font-bold w-32">Estatus</th>
                      <th class="px-3 py-3 text-center font-bold w-20">Obs.</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                      v-for="(est, idx) in filteredEstudiantes"
                      :key="est.ins_codigo"
                      class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors"
                    >
                      <td class="px-4 py-3 text-center text-xs font-mono font-bold text-slate-400">
                        {{ idx + 1 }}
                      </td>
                      <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                        {{ est.ins_cedula }}
                      </td>
                      <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                        {{ est.nombres }}
                        <div class="text-[10px] text-slate-400 font-normal">
                          {{ seccionSeleccionada?.periodo || 'Cohorte 2026-I' }}
                        </div>
                        <div v-if="est.modificaciones > 0" class="text-[10px] text-amber-600 flex items-center gap-1 mt-0.5">
                          <History class="h-3 w-3" />
                          {{ est.modificaciones }} cambio(s)
                        </div>
                      </td>

                      <!-- INPUTS DE CALIFICACIONES (EVALUACIONES 1 A 4 O 5: ESTRICTAMENTE 0 A CORTE.PESOCORTE PTS) -->
                      <td
                        v-for="(_, evIdx) in corteSeleccionado.evaluaciones"
                        :key="evIdx"
                        class="px-2 py-2 text-center bg-slate-50/30 dark:bg-slate-900/10 border-l border-slate-100 dark:border-slate-800"
                      >
                        <input
                          v-model.number="est.notasDetalle[corteSeleccionado.id][evIdx]"
                          @keydown="validarTeclaNumeroYDecimal($event)"
                          @paste="sanitizarPegado($event)"
                          @input="sanitizarNotaInput(est, corteSeleccionado.id, evIdx, $event)"
                          @change="confirmarNotaInput(est, corteSeleccionado.id, evIdx, $event)"
                          @blur="confirmarNotaInput(est, corteSeleccionado.id, evIdx, $event)"
                          type="number"
                          min="0"
                          :max="corteSeleccionado.pesoCorte"
                          step="any"
                          placeholder="0.00"
                          inputmode="decimal"
                          :disabled="!esCorteCorrespondienteHabilitado"
                          :readonly="!esCorteCorrespondienteHabilitado"
                          class="input-base w-18 text-center font-bold h-8 text-sm tabular-nums"
                          :class="{
                            'bg-slate-100 dark:bg-slate-800/80 cursor-not-allowed text-slate-400 dark:text-slate-500 border-dashed border-slate-300 dark:border-slate-700': !esCorteCorrespondienteHabilitado,
                            'text-emerald-600 dark:text-emerald-400 font-extrabold': esCorteCorrespondienteHabilitado && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== null && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== undefined && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) >= (corteSeleccionado.pesoCorte * 0.8),
                            'text-blue-600 dark:text-blue-400': esCorteCorrespondienteHabilitado && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== null && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== undefined && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) >= (corteSeleccionado.pesoCorte * 0.6) && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) < (corteSeleccionado.pesoCorte * 0.8),
                            'text-amber-600 dark:text-amber-400': esCorteCorrespondienteHabilitado && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== null && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== undefined && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) >= (corteSeleccionado.pesoCorte * 0.5) && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) < (corteSeleccionado.pesoCorte * 0.6),
                            'text-rose-600 dark:text-rose-400': esCorteCorrespondienteHabilitado && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== null && est.notasDetalle[corteSeleccionado.id]?.[evIdx] !== undefined && (est.notasDetalle[corteSeleccionado.id]?.[evIdx] ?? 0) < (corteSeleccionado.pesoCorte * 0.5),
                          }"
                          :title="!esCorteCorrespondienteHabilitado ? 'Este cohorte está protegido. Solo puede poner y modificar notas en el cohorte correspondiente.' : `Solo números y decimales (escala de 0 a ${corteSeleccionado.pesoCorte} puntos)`"
                        />
                      </td>

                      <!-- Nota Cohorte / [peso]% -->
                      <td class="px-4 py-3 text-center bg-red-50/40 dark:bg-red-950/20 font-extrabold text-base tabular-nums">
                        <span v-if="tieneNotasRegistradas(est, corteSeleccionado.id)" :class="calcularNotaCorte(est, corteSeleccionado.id) >= (corteSeleccionado.pesoCorte * 0.6) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                          {{ calcularNotaCorte(est, corteSeleccionado.id) }}
                        </span>
                        <span v-else class="text-slate-400 dark:text-slate-500 font-normal text-sm">
                          0.00
                        </span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 ml-1">/ {{ corteSeleccionado.pesoCorte }}%</span>
                      </td>

                      <!-- Estatus -->
                      <td class="px-4 py-3 text-center">
                        <Badge
                          variant="outline"
                          :class="tieneNotasRegistradas(est, corteSeleccionado.id)
                            ? (calcularNotaCorte(est, corteSeleccionado.id) >= (corteSeleccionado.pesoCorte * 0.6) ? 'border-emerald-300 text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40' : 'border-rose-300 text-rose-700 bg-rose-50 dark:bg-rose-950/40')
                            : 'border-slate-300 text-slate-500 bg-slate-50 dark:bg-slate-900'"
                          class="text-[11px]"
                        >
                          {{ tieneNotasRegistradas(est, corteSeleccionado.id)
                            ? (calcularNotaCorte(est, corteSeleccionado.id) >= (corteSeleccionado.pesoCorte * 0.6) ? 'Aprobado' : 'Reprobado')
                            : 'Sin Calificar' }}
                        </Badge>
                      </td>

                      <td class="px-3 py-3 text-center">
                        <Button size="xs" variant="ghost" @click="abrirObservaciones(est)" class="h-8 w-8 p-0 text-slate-500 hover:text-slate-900">
                          <MessageSquare class="h-4 w-4" />
                        </Button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- ========================================================================= -->
        <!-- SUB-VISTA B: MATRIZ CONSOLIDADA GENERAL DE LOS 4 CORTES                   -->
        <!-- ========================================================================= -->
        <div v-else class="space-y-4 animate-in fade-in duration-300">
          
          <Card class="border-slate-200 dark:border-slate-800 shadow-sm">
            <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <CardTitle class="text-base font-bold flex items-center gap-2">
                  <Award class="h-5 w-5 text-red-600" />
                  Matriz General Consolidada de los {{ cortes.length }} Cohortes
                </CardTitle>
                <CardDescription class="text-xs">
                  Resumen de los {{ cortes.length }} Cohortes evaluativos con cálculo automatizado de promedio y acumulado sobre 100 puntos.
                </CardDescription>
              </div>
              <div class="text-xs text-slate-500">
                Haz clic en cualquier cohorte en la barra superior para ver el desglose en <strong>4 o 5 evaluaciones</strong>.
              </div>
            </CardHeader>

            <CardContent class="p-0">
              <div class="overflow-x-auto">
                <table
                  class="w-full text-left text-sm transition-all"
                  :class="cortes.length >= 6 ? 'min-w-[1100px]' : (cortes.length === 5 ? 'min-w-[960px]' : 'min-w-[850px]')"
                >
                  <thead class="bg-slate-100/90 text-xs uppercase text-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    <tr>
                      <th class="px-3 py-3.5 font-bold w-10 text-center">N°</th>
                      <th class="px-3 py-3.5 font-bold w-28 whitespace-nowrap">Cédula</th>
                      <th class="px-4 py-3.5 font-bold min-w-[170px]">Estudiante</th>
                      
                      <th
                        v-for="corte in cortes"
                        :key="corte.id"
                        class="font-bold text-center bg-slate-50 dark:bg-slate-800/40 border-l border-slate-200 dark:border-slate-800 transition-all"
                        :class="cortes.length >= 6 ? 'w-24 px-1.5 py-2.5' : (cortes.length === 5 ? 'w-28 px-2 py-3' : 'w-32 px-3 py-3')"
                      >
                        <button
                          type="button"
                          @click="corteActivoId = corte.id"
                          class="hover:underline flex flex-col items-center mx-auto group cursor-pointer"
                        >
                          <span
                            class="text-slate-900 dark:text-slate-100 font-bold group-hover:text-red-600 transition-colors flex items-center justify-center gap-1"
                            :class="cortes.length >= 6 ? 'text-xs' : 'text-xs sm:text-sm'"
                          >
                            <Lock v-if="corte.id !== corteSeleccionadoParaCarga" class="h-3 w-3 text-slate-400" />
                            <span v-else class="text-xs">✏️</span>
                            {{ corte.nombre }}
                          </span>
                          <span
                            class="font-semibold"
                            :class="[
                              cortes.length >= 6 ? 'text-[9px]' : 'text-[10px]',
                              corte.id === corteSeleccionadoParaCarga ? 'text-emerald-600 font-bold' : 'text-slate-400'
                            ]"
                          >
                            {{ corte.id === corteSeleccionadoParaCarga ? `${corte.pesoCorte}% • Habilitado` : `${corte.pesoCorte}% • Bloqueado` }}
                          </span>
                        </button>
                      </th>

                      <th class="px-3 py-3.5 font-bold text-center w-24 bg-red-100/60 text-red-900 dark:bg-red-950/40 dark:text-red-300">
                        <div>Nota Final</div>
                        <div class="text-[10px] font-semibold opacity-85">(0 a {{ maxNotaEscala }} pts)</div>
                      </th>

                      <th class="px-3 py-3.5 font-bold text-center w-28 bg-slate-200/50 dark:bg-slate-800/50">
                        <div>Total Acumulado</div>
                        <div class="text-[10px] font-semibold text-slate-500">(0 a 100 pts)</div>
                      </th>

                      <th class="px-3 py-3.5 font-bold text-center w-32">Condición</th>
                      <th class="px-3 py-3.5 font-bold text-center w-16">Obs.</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                      v-for="(est, idx) in filteredEstudiantes"
                      :key="est.ins_codigo"
                      class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-colors"
                    >
                      <td class="px-3 py-3 text-center text-xs font-mono font-bold text-slate-400">
                        {{ idx + 1 }}
                      </td>
                      <td class="px-3 py-3 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                        {{ est.ins_cedula }}
                      </td>
                      <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                        {{ est.nombres }}
                        <div v-if="est.modificaciones > 0" class="text-[10px] text-amber-600 flex items-center gap-1 mt-0.5">
                          <History class="h-3 w-3" />
                          {{ est.modificaciones }} modificación(es)
                        </div>
                      </td>

                      <td
                        v-for="corte in cortes"
                        :key="corte.id"
                        class="text-center border-l border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-red-50/40 dark:hover:bg-red-950/20 transition-all"
                        :class="cortes.length >= 6 ? 'px-1 py-2 text-xs' : (cortes.length === 5 ? 'px-1.5 py-2.5 text-xs' : 'px-2 py-3 text-sm')"
                        @click="corteActivoId = corte.id"
                        :title="corte.id === corteSeleccionadoParaCarga ? `Ver y calificar ${corte.nombre} (Habilitado)` : `Ver desglose de ${corte.nombre} (Solo lectura)`"
                      >
                        <div
                          class="font-bold tabular-nums"
                          :class="[
                            cortes.length >= 6 ? 'text-xs' : 'text-sm',
                            tieneNotasRegistradas(est, corte.id) ? (calcularNotaCorte(est, corte.id) >= (corte.pesoCorte * 0.6) ? 'text-slate-900 dark:text-slate-100' : 'text-rose-600') : 'text-slate-400 dark:text-slate-500'
                          ]"
                        >
                          {{ tieneNotasRegistradas(est, corte.id) ? calcularNotaCorte(est, corte.id) : '—' }} <span class="font-semibold text-slate-500 dark:text-slate-400" :class="cortes.length >= 6 ? 'text-[9px]' : 'text-xs'">/ {{ corte.pesoCorte }}%</span>
                        </div>
                        <div v-if="corte.id === corteSeleccionadoParaCarga" class="text-[9px] text-emerald-600 font-bold flex items-center justify-center gap-0.5 mt-0.5">
                          <span>✏️</span> Modificable
                        </div>
                        <div v-else class="text-[9px] text-slate-400 flex items-center justify-center gap-0.5 mt-0.5">
                          <Lock class="h-2.5 w-2.5" /> Bloqueado
                        </div>
                      </td>

                      <td class="px-3 py-3 text-center bg-red-50/40 dark:bg-red-950/30 font-extrabold text-base tabular-nums" :class="calcularPromedioFinal(est) >= (maxNotaEscala * 0.6) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        {{ calcularPromedioFinal(est) }}
                      </td>

                      <td class="px-3 py-3 text-center font-semibold text-xs tabular-nums text-slate-700 dark:text-slate-300">
                        {{ calcularTotalAcumulado100(est) }} pts
                      </td>

                      <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border" :class="condicionEstudiante(calcularPromedioFinal(est), est).class">
                          {{ condicionEstudiante(calcularPromedioFinal(est), est).text }}
                        </span>
                      </td>

                      <td class="px-3 py-3 text-center">
                        <Button size="xs" variant="ghost" @click="abrirObservaciones(est)" class="h-8 w-8 p-0 text-slate-500 hover:text-slate-900">
                          <MessageSquare class="h-4 w-4" />
                        </Button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- MODAL: CONFIGURAR EVALUACIONES DEL CORTE                                   -->
      <!-- ========================================================================= -->
      <div v-if="modalConfiguracion && corteEnEdicion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
          
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <SlidersHorizontal class="h-5 w-5 text-red-600" />
                Configurar Evaluaciones — {{ corteEnEdicion.nombre }}
              </h3>
              <p class="text-xs text-slate-500">
                Ajuste de nombres, metodologías y porcentajes de las evaluaciones de este cohorte (deben sumar {{ corteEnEdicion.pesoCorte }}%).
              </p>
            </div>
            <button @click="modalConfiguracion = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
              Número de evaluaciones para este corte:
            </span>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="cambiarDivisionCorte(corteEnEdicion, 4)"
                class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                :class="corteEnEdicion.cantidadEvaluaciones === 4 ? 'bg-red-600 text-white' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
              >
                4 Evaluaciones
              </button>
              <button
                type="button"
                @click="cambiarDivisionCorte(corteEnEdicion, 5)"
                class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                :class="corteEnEdicion.cantidadEvaluaciones === 5 ? 'bg-red-600 text-white' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
              >
                5 Evaluaciones
              </button>
            </div>
          </div>

          <div class="space-y-3">
            <div
              v-for="(ev, idx) in corteEnEdicion.evaluaciones"
              :key="ev.id"
              class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 space-y-2 text-xs"
            >
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                  <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950 dark:text-red-400 text-[11px] font-bold">
                    {{ idx + 1 }}
                  </span>
                  Evaluación {{ idx + 1 }}
                </span>
                <div class="flex items-center gap-2">
                  <label class="text-[11px] font-medium text-slate-500">Ponderación (%):</label>
                  <input
                    v-model.number="ev.peso"
                    @keydown="validarTeclaNumeroYDecimal($event)"
                    type="number"
                    min="1"
                    :max="corteEnEdicion ? corteEnEdicion.pesoCorte : 25"
                    step="any"
                    inputmode="decimal"
                    class="input-base w-18 h-7 text-center font-bold text-xs"
                  />
                </div>
              </div>

              <div class="grid gap-2 sm:grid-cols-2">
                <div>
                  <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">
                    Descripción / Título:
                  </label>
                  <input
                    v-model="ev.nombre"
                    type="text"
                    placeholder="Título de la evaluación..."
                    class="input-base w-full h-8 text-xs"
                  />
                </div>

                <div>
                  <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400">
                      Metodología:
                    </label>
                    <button
                      type="button"
                      @click="abrirModalOtraMetodologia(ev)"
                      class="text-[10px] text-red-600 dark:text-red-400 font-bold hover:underline flex items-center gap-0.5"
                    >
                      <Sparkles class="h-3 w-3" />
                      Botón "Otro" (Personalizar)
                    </button>
                  </div>
                  <select
                    v-model="ev.metodologia"
                    class="input-base w-full h-8 text-xs cursor-pointer"
                  >
                    <option v-for="m in listaMetodologias" :key="m" :value="m">{{ m }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-between text-xs">
            <div>
              <span class="text-slate-500">Suma Total del Cohorte: </span>
              <strong :class="corteEnEdicion.evaluaciones.reduce((a, b) => a + Number(b.peso || 0), 0) === corteEnEdicion.pesoCorte ? 'text-emerald-600 text-sm' : 'text-amber-600 text-sm'">
                {{ corteEnEdicion.evaluaciones.reduce((a, b) => a + Number(b.peso || 0), 0) }}% / {{ corteEnEdicion.pesoCorte }}%
              </strong>
            </div>

            <Button
              type="button"
              variant="outline"
              size="sm"
              @click="distribuirPesosEquitativamente(corteEnEdicion)"
              class="h-8 text-xs gap-1"
            >
              <RefreshCw class="h-3 w-3" />
              Equilibrar Ponderaciones
            </Button>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalConfiguracion = false">
              Cancelar
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarConfiguracionCorte">
              Guardar Cambios
            </Button>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL: ESCRIBIR OTRA METODOLOGÍA (BOTÓN "OTRO")                           -->
      <!-- ========================================================================= -->
      <div v-if="mostrarInputOtraMetodologia" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Sparkles class="h-4 w-4 text-amber-500" />
                Otra Metodología de Evaluación
              </h3>
              <p class="text-xs text-slate-500">
                Escriba una metodología o técnica evaluativa personalizada para el docente.
              </p>
            </div>
            <button @click="mostrarInputOtraMetodologia = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Nombre de la Metodología:
              </label>
              <input
                v-model="nuevaMetodologiaInput"
                type="text"
                placeholder="Ej. Seminario Investigativo, Mesa Redonda, Simulación de Servidores, Debate..."
                class="input-base w-full text-xs"
                @keyup.enter="aplicarOtraMetodologia"
                autofocus
              />
            </div>

            <div class="space-y-1.5">
              <span class="text-[11px] text-slate-400 block font-medium">Sugerencias frecuentes:</span>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="sug in ['Seminario Investigativo', 'Mesa Redonda', 'Simulación Práctica', 'Glosario Colaborativo', 'Discusión Guiada', 'Ensayo Crítico']"
                  :key="sug"
                  type="button"
                  @click="nuevaMetodologiaInput = sug"
                  class="px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-300 hover:border-red-500 hover:text-red-600 transition-colors"
                >
                  + {{ sug }}
                </button>
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="mostrarInputOtraMetodologia = false">
              Cancelar
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="aplicarOtraMetodologia">
              <Check class="h-3.5 w-3.5 mr-1" />
              Aplicar Metodología
            </Button>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL DE OBSERVACIONES DEL ESTUDIANTE                                      -->
      <!-- ========================================================================= -->
      <div v-if="estudianteObservacion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 animate-in fade-in duration-200">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-950 space-y-4">
          <div class="border-b pb-3 border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
              <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">{{ estudianteObservacion.nombres }}</h3>
              <p class="text-xs text-slate-500">C.I. {{ estudianteObservacion.ins_cedula }} • Nota Hito y Observaciones</p>
            </div>
            <button @click="estudianteObservacion = null" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-sm">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nota Hito Referencial (Escala 0 a {{ maxNotaActiva }} pts)</label>
              <input
                v-model.number="estudianteObservacion.nota_hito"
                @keydown="validarTeclaNumeroYDecimal($event)"
                @paste="sanitizarPegado($event)"
                @input="sanitizarNotaHito(estudianteObservacion, $event)"
                @change="confirmarNotaHito(estudianteObservacion, $event)"
                @blur="confirmarNotaHito(estudianteObservacion, $event)"
                type="number"
                min="0"
                :max="maxNotaActiva"
                step="any"
                placeholder="0.00"
                inputmode="decimal"
                class="input-base w-full font-bold"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones Académicas</label>
              <textarea v-model="estudianteObservacion.observacion" rows="3" class="input-base w-full" placeholder="Justificación o anotación del rendimiento del estudiante..."></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <Button size="sm" class="bg-red-600 text-white" @click="estudianteObservacion = null">
              Guardar y Cerrar
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
.input-base { border: 1px solid #cbd5e1; border-radius: 0.375rem; background-color: #fff; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #0f172a; transition: border-color 0.2s ease; }
.input-base:focus { border-color: #94a3b8; outline: none; box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.2); }
@media (prefers-color-scheme: dark) { .input-base { border-color: #334155; background-color: #020617; color: #f8fafc; } .input-base:focus { border-color: #64748b; box-shadow: 0 0 0 2px rgba(100, 116, 139, 0.2); } }
</style>
