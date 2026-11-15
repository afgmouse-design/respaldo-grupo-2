<script setup lang="ts">
import { ref, computed } from 'vue';
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
  Calculator,
  Search,
  BookOpen,
  Award,
  CheckCircle2,
  AlertCircle,
  FileSpreadsheet,
  Printer,
  FileCheck,
  TrendingUp,
  User,
  GraduationCap,
  Calendar,
  Layers,
  ArrowRight,
  ShieldCheck,
  ExternalLink,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Calificaciones', href: route('calificaciones.carga') },
  { title: 'Consolidación de Notas e Historial (IRA)', href: route('calificaciones.consolidacion') },
];

// Estudiantes disponibles para consulta y consolidación (anuales)
const estudiantesLista = [
  {
    cedula: '30123456',
    nombre: 'Alejandro José González Pérez',
    programa: 'PNF en Informática',
    malla: 'Malla Curricular 2018 (Trayectos)',
    sede: 'Sede Central Acarigua',
    trayectoActual: 'Trayecto IV',
    condicion: 'REGULAR',
    condicionRepitencia: 'NINGUNA',
  },
  {
    cedula: '28456789',
    nombre: 'Mariana Victoria Rivas Delgado',
    programa: 'PNF en Administración',
    malla: 'Malla Curricular 2019',
    sede: 'Sede Guanare',
    trayectoActual: 'Trayecto III',
    condicion: 'REGULAR',
    condicionRepitencia: 'NINGUNA',
  },
  {
    cedula: '29876543',
    nombre: 'Gabriel Antonio Herrera Castillo',
    programa: 'PNF en Informática',
    malla: 'Malla Curricular 2018 (Trayectos)',
    sede: 'Sede Central Acarigua',
    trayectoActual: 'Trayecto II',
    condicion: 'OBSERVACIÓN',
    condicionRepitencia: 'REPITENCIA NIVEL 1',
  },
];

const busquedaCedula = ref('30123456');
const estudianteSeleccionado = ref(estudiantesLista[0]);

// Datos de la tabla 'anuales' para el estudiante seleccionado
const registroNotasAnuales = ref([
  // TRAYECTO I
  {
    anu_codigo: 101,
    anu_cod_trayecto: 1,
    nombre_trayecto: 'Trayecto I',
    anu_cod_lapso: 2023,
    anu_nombre_lapso: '2023-I',
    anu_siglas_ucu: 'MAT-101',
    nombre_unidad: 'Matemática I',
    anu_nuc: 4, // Unidades de Crédito
    anu_nota: 22.0,
    anu_cod_escala_medicion: 1, // Escala 25
    anu_observacion: 'AP',
    anu_condicion: 'A', // A = Aprobado, R = Reprobado
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 102,
    anu_cod_trayecto: 1,
    nombre_trayecto: 'Trayecto I',
    anu_cod_lapso: 2023,
    anu_nombre_lapso: '2023-I',
    anu_siglas_ucu: 'INF-101',
    nombre_unidad: 'Algoritmos y Lógica de Programación',
    anu_nuc: 6,
    anu_nota: 24.0,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 103,
    anu_cod_trayecto: 1,
    nombre_trayecto: 'Trayecto I',
    anu_cod_lapso: 2023,
    anu_nombre_lapso: '2023-I',
    anu_siglas_ucu: 'SOC-101',
    nombre_unidad: 'Formación Sociocrítica I',
    anu_nuc: 3,
    anu_nota: 23.0,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 104,
    anu_cod_trayecto: 1,
    nombre_trayecto: 'Trayecto I',
    anu_cod_lapso: 2023,
    anu_nombre_lapso: '2023-II',
    anu_siglas_ucu: 'PRY-101',
    nombre_unidad: 'Proyecto Sociotecnológico I',
    anu_nuc: 8,
    anu_nota: 24.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },

  // TRAYECTO II
  {
    anu_codigo: 201,
    anu_cod_trayecto: 2,
    nombre_trayecto: 'Trayecto II',
    anu_cod_lapso: 2024,
    anu_nombre_lapso: '2024-I',
    anu_siglas_ucu: 'INF-201',
    nombre_unidad: 'Estructuras de Datos y POO',
    anu_nuc: 6,
    anu_nota: 22.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 202,
    anu_cod_trayecto: 2,
    nombre_trayecto: 'Trayecto II',
    anu_cod_lapso: 2024,
    anu_nombre_lapso: '2024-I',
    anu_siglas_ucu: 'BD-201',
    nombre_unidad: 'Modelado y Bases de Datos Relacionales',
    anu_nuc: 5,
    anu_nota: 24.0,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 203,
    anu_cod_trayecto: 2,
    nombre_trayecto: 'Trayecto II',
    anu_cod_lapso: 2024,
    anu_nombre_lapso: '2024-II',
    anu_siglas_ucu: 'PRY-201',
    nombre_unidad: 'Proyecto Sociotecnológico II (Salida TSU)',
    anu_nuc: 8,
    anu_nota: 23.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },

  // TRAYECTO III
  {
    anu_codigo: 301,
    anu_cod_trayecto: 3,
    nombre_trayecto: 'Trayecto III',
    anu_cod_lapso: 2025,
    anu_nombre_lapso: '2025-I',
    anu_siglas_ucu: 'INF-301',
    nombre_unidad: 'Sistemas Operativos y Redes',
    anu_nuc: 5,
    anu_nota: 21.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 302,
    anu_cod_trayecto: 3,
    nombre_trayecto: 'Trayecto III',
    anu_cod_lapso: 2025,
    anu_nombre_lapso: '2025-I',
    anu_siglas_ucu: 'INF-302',
    nombre_unidad: 'Ingeniería del Software II',
    anu_nuc: 6,
    anu_nota: 18.0,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 303,
    anu_cod_trayecto: 3,
    nombre_trayecto: 'Trayecto III',
    anu_cod_lapso: 2025,
    anu_nombre_lapso: '2025-II',
    anu_siglas_ucu: 'PRY-301',
    nombre_unidad: 'Proyecto Sociotecnológico III',
    anu_nuc: 8,
    anu_nota: 19.0,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },

  // TRAYECTO IV (ACTUAL EN CURSO/CONSOLIDADO)
  {
    anu_codigo: 401,
    anu_cod_trayecto: 4,
    nombre_trayecto: 'Trayecto IV',
    anu_cod_lapso: 2026,
    anu_nombre_lapso: '2026-I',
    anu_siglas_ucu: 'INF-401',
    nombre_unidad: 'Bases de Datos Avanzadas y Big Data',
    anu_nuc: 5,
    anu_nota: 18.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 402,
    anu_cod_trayecto: 4,
    nombre_trayecto: 'Trayecto IV',
    anu_cod_lapso: 2026,
    anu_nombre_lapso: '2026-I',
    anu_siglas_ucu: 'INF-402',
    nombre_unidad: 'Auditoría y Seguridad de Sistemas',
    anu_nuc: 4,
    anu_nota: 17.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
  {
    anu_codigo: 403,
    anu_cod_trayecto: 4,
    nombre_trayecto: 'Trayecto IV',
    anu_cod_lapso: 2026,
    anu_nombre_lapso: '2026-II',
    anu_siglas_ucu: 'PRY-401',
    nombre_unidad: 'Proyecto Sociotecnológico IV (Grado Ingeniería)',
    anu_nuc: 10,
    anu_nota: 19.5,
    anu_cod_escala_medicion: 1,
    anu_observacion: 'AP',
    anu_condicion: 'A',
    anu_condicion_repitencia: 'N',
    anu_50: 0.0,
    anu_estatus: 'A',
  },
]);

// Filtro por Trayecto
const filtroTrayecto = ref('TODOS');

const notasFiltradas = computed(() => {
  if (filtroTrayecto.value === 'TODOS') {
    return registroNotasAnuales.value;
  }
  return registroNotasAnuales.value.filter(n => n.anu_cod_trayecto === Number(filtroTrayecto.value));
});

// CÁLCULO PONDERADO DE INDICE DE RENDIMIENTO ACADÉMICO (IRA)
// Fórmula matemática institucional: IRA = SUM(Nota * NUC) / SUM(NUC)
const calculosIRA = computed(() => {
  let sumaPonderada = 0;
  let sumaUC = 0;
  let ucAprobadas = 0;
  let materiasAprobadas = 0;
  let materiasReprobadas = 0;

  for (const n of registroNotasAnuales.value) {
    if (n.anu_estatus === 'A') {
      const nota = Number(n.anu_nota);
      const uc = Number(n.anu_nuc);
      sumaPonderada += (nota * uc);
      sumaUC += uc;

      if (n.anu_condicion === 'A' && nota >= 10) {
        ucAprobadas += uc;
        materiasAprobadas++;
      } else {
        materiasReprobadas++;
      }
    }
  }

  const ira = sumaUC > 0 ? (sumaPonderada / sumaUC) : 0;
  const porcentajeAvance = Math.min(100, Math.round((ucAprobadas / 80) * 100)); // Total 80 UC para carrera

  return {
    ira: ira.toFixed(2),
    iraEscala100: ((ira / 25) * 100).toFixed(2),
    sumaUC,
    ucAprobadas,
    materiasAprobadas,
    materiasReprobadas,
    porcentajeAvance,
    totalMaterias: registroNotasAnuales.value.length,
    honorMention: ira >= 23.5 ? 'Summa Cum Laude' : (ira >= 21.5 ? 'Magna Cum Laude' : (ira >= 19.5 ? 'Cum Laude' : 'Regular')),
  };
});

// Selección de estudiante en la lista
const seleccionarEstudiante = (est: any) => {
  estudianteSeleccionado.value = est;
  busquedaCedula.value = est.cedula;
};

// Modal de auditoría de nota anual
const modalAuditoria = ref(false);
const notaAuditoria = ref<any>(null);

const abrirAuditoria = (item: any) => {
  notaAuditoria.value = item;
  modalAuditoria.value = true;
};
</script>

<template>
  <Head title="Cálculos y Consolidación de Promedios Ponderados (IRA) - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- ENCABEZADO PRINCIPAL -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
            <Calculator class="h-3.5 w-3.5" />
            Récord Histórico de Calificaciones • Índices de Rendimiento (IRA)
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Consolidación y Récord de Notas
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Cálculo automatizado de promedios ponderados por unidades de crédito (UC), validación de historial académico y verificación de requisitos para pase de trayecto o egreso.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" variant="outline" class="gap-1.5" as-child>
            <Link :href="route('calificaciones.carga')">
              <BookOpen class="h-4 w-4" />
              Carga Docente
            </Link>
          </Button>

          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" as-child>
            <Link :href="route('promociones.index')">
              <GraduationCap class="h-4 w-4" />
              Ir a Promociones
            </Link>
          </Button>
        </div>
      </div>

      <!-- BUSCADOR DE ESTUDIANTE Y SELECTOR RÁPIDO -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardContent class="p-4 sm:p-5">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex-1 max-w-md">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Consultar Estudiante por Cédula de Identidad
              </label>
              <div class="relative">
                <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                <input
                  v-model="busquedaCedula"
                  type="text"
                  placeholder="Ej: 30123456"
                  class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <!-- Accesos rápidos de estudiantes -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
              <span class="text-xs text-slate-500 font-medium">Estudiantes de prueba:</span>
              <div class="flex items-center gap-1.5 flex-wrap">
                <button
                  v-for="est in estudiantesLista"
                  :key="est.cedula"
                  @click="seleccionarEstudiante(est)"
                  class="px-2.5 py-1 text-xs rounded-md border transition-all"
                  :class="estudianteSeleccionado.cedula === est.cedula 
                    ? 'bg-red-50 text-red-700 border-red-300 font-bold dark:bg-red-950/50 dark:text-red-300 dark:border-red-800' 
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-700'"
                >
                  {{ est.nombre.split(' ')[0] }} ({{ est.cedula }})
                </button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- TARJETA DE INFORMACIÓN DEL ESTUDIANTE + RESUMEN DE IRA -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- DATOS DEL EXPEDIENTE (1 COL) -->
        <Card class="border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
          <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <User class="h-4 w-4 text-red-600" />
                Ficha Académica
              </CardTitle>
              <Badge variant="outline" class="font-mono text-xs">C.I. {{ estudianteSeleccionado.cedula }}</Badge>
            </div>
            <CardDescription class="text-xs">Datos consolidados del expediente del alumno</CardDescription>
          </CardHeader>
          <CardContent class="pt-4 space-y-3 text-xs">
            <div>
              <span class="text-slate-400 block">Nombres y Apellidos:</span>
              <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ estudianteSeleccionado.nombre }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <div>
                <span class="text-slate-400 block">Programa (PNF):</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ estudianteSeleccionado.programa }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Malla:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ estudianteSeleccionado.malla }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <div>
                <span class="text-slate-400 block">Sede:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ estudianteSeleccionado.sede }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Nivel Actual:</span>
                <span class="font-bold text-red-600 dark:text-red-400">{{ estudianteSeleccionado.trayectoActual }}</span>
              </div>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <span class="text-slate-400">Condición:</span>
              <Badge variant="successCustom" class="text-xs">{{ estudianteSeleccionado.condicion }}</Badge>
            </div>
          </CardContent>
        </Card>

        <!-- KPI MATEMÁTICO: ÍNDICE DE RENDIMIENTO ACADÉMICO (2 COL) -->
        <Card class="lg:col-span-2 border-red-200 dark:border-red-950/60 bg-gradient-to-br from-white via-red-50/20 to-red-100/30 dark:from-slate-950 dark:via-red-950/10 dark:to-slate-900">
          <CardHeader class="pb-3 border-b border-red-100 dark:border-red-900/30 flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Calculator class="h-5 w-5 text-red-600" />
                Cálculo de Índice Ponderado Acumulado (IRA)
              </CardTitle>
              <CardDescription class="text-xs">
                Fórmula de acreditación: <code class="bg-red-100 dark:bg-red-950/80 px-1 py-0.5 rounded text-red-700 dark:text-red-300 font-mono">IRA = Σ (Nota × UC) / Σ UC</code>
              </CardDescription>
            </div>
            <Badge variant="outline" class="border-red-300 text-red-700 dark:border-red-800 dark:text-red-300 font-semibold">
              {{ calculosIRA.honorMention }}
            </Badge>
          </CardHeader>
          <CardContent class="pt-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <!-- IRA Escala 25 -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-xs text-slate-500 font-medium block">IRA (Escala 25)</span>
                <span class="text-3xl font-extrabold text-red-600 dark:text-red-400 font-mono">{{ calculosIRA.ira }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Mínimo aprobatorio: 15.00</span>
              </div>

              <!-- IRA Escala 100 -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-xs text-slate-500 font-medium block">Escala 100 Puntos</span>
                <span class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 font-mono">{{ calculosIRA.iraEscala100 }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Equivalente porcentual</span>
              </div>

              <!-- Unidades de Crédito -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-xs text-slate-500 font-medium block">UC Aprobadas</span>
                <div class="flex items-baseline justify-center gap-1 mt-1">
                  <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ calculosIRA.ucAprobadas }}</span>
                  <span class="text-xs text-slate-400">/ {{ calculosIRA.sumaUC }} cursadas</span>
                </div>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block mt-0.5">100% de efectividad</span>
              </div>

              <!-- Materias Aprobadas -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center">
                <span class="text-xs text-slate-500 font-medium block">Unidades Aprobadas</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1 block">
                  {{ calculosIRA.materiasAprobadas }} / {{ calculosIRA.totalMaterias }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">0 reprobadas</span>
              </div>
            </div>

            <!-- Barra de Avance de Carrera -->
            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800">
              <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="font-semibold text-slate-700 dark:text-slate-300">Progreso Curricular para Grado de Ingeniería</span>
                <span class="font-bold text-red-600 dark:text-red-400">{{ calculosIRA.porcentajeAvance }}% Cumplido</span>
              </div>
              <div class="h-2 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-red-600 rounded-full transition-all duration-500" :style="{ width: `${calculosIRA.porcentajeAvance}%` }"></div>
              </div>
            </div>
          </CardContent>
        </Card>

      </div>

      <!-- TABLA DETALLADA DE NOTAS ANUALES (HISTORIAL ACADÉMICO / KARDEX) -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <FileSpreadsheet class="h-5 w-5 text-red-600" />
                Historial de Unidades Curriculares Consolidadas
              </CardTitle>
              <CardDescription class="text-xs">
                Registro histórico ordenado por Trayectos y Lapsos con unidades de crédito, notas finales y condición de aprobación.
              </CardDescription>
            </div>

            <!-- Filtros por trayecto -->
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500 font-medium">Filtrar:</span>
              <select
                v-model="filtroTrayecto"
                class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
              >
                <option value="TODOS">Todos los Trayectos</option>
                <option value="1">Trayecto I</option>
                <option value="2">Trayecto II</option>
                <option value="3">Trayecto III</option>
                <option value="4">Trayecto IV</option>
              </select>
            </div>
          </div>
        </CardHeader>

        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3 font-semibold">Código / Siglas</th>
                  <th class="px-4 py-3 font-semibold">Unidad Curricular</th>
                  <th class="px-4 py-3 font-semibold text-center">Trayecto</th>
                  <th class="px-4 py-3 font-semibold text-center">Lapso</th>
                  <th class="px-4 py-3 font-semibold text-center">U.C.</th>
                  <th class="px-4 py-3 font-semibold text-center">Nota (25)</th>
                  <th class="px-4 py-3 font-semibold text-center">Equiv. (100)</th>
                  <th class="px-4 py-3 font-semibold text-center">Condición</th>
                  <th class="px-4 py-3 font-semibold text-center">Estatus</th>
                  <th class="px-4 py-3 font-semibold text-center">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="item in notasFiltradas"
                  :key="item.anu_codigo"
                  class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30 transition-colors"
                >
                  <td class="px-4 py-3 font-mono font-semibold text-xs text-red-600 dark:text-red-400">
                    {{ item.anu_siglas_ucu }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ item.nombre_unidad }}</div>
                    <div class="text-[11px] text-slate-400">Reg: #{{ item.anu_codigo }} • Escala: {{ item.anu_cod_escala_medicion === 1 ? 'Escala 25 Puntos' : 'Centesimal (100)' }}</div>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-medium">
                      {{ item.nombre_trayecto }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-400">
                    {{ item.anu_nombre_lapso }}
                  </td>
                  <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-slate-200">
                    {{ item.anu_nuc }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-block px-2.5 py-0.5 rounded font-mono font-bold text-sm"
                      :class="item.anu_nota >= 10 
                        ? 'text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300' 
                        : 'text-red-700 bg-red-50 dark:bg-red-950/40 dark:text-red-300'"
                    >
                      {{ item.anu_nota.toFixed(1) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center font-mono text-xs text-slate-500">
                    {{ ((item.anu_nota / 25) * 100).toFixed(0) }} pts
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Badge :variant="item.anu_condicion === 'A' ? 'successCustom' : 'destructiveCustom'">
                      {{ item.anu_condicion === 'A' ? 'APROBADO' : 'REPROBADO' }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                      {{ item.anu_estatus === 'A' ? 'ACTIVA' : 'INACTIVA' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Button size="xs" variant="outline" class="h-7 text-xs" @click="abrirAuditoria(item)">
                      Auditoría
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- MODAL DE AUDITORÍA DE REGISTRO EN 'anuales' -->
      <div v-if="modalAuditoria && notaAuditoria" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <ShieldCheck class="h-5 w-5 text-red-600" />
                Auditoría del Registro Anual
              </h3>
              <p class="text-xs text-slate-500">Detalles oficiales del expediente y acta de notas</p>
            </div>
            <button @click="modalAuditoria = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div class="grid grid-cols-2 gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
              <div>
                <span class="text-slate-400 block">Código de Registro:</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">#{{ notaAuditoria.anu_codigo }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Cédula del Estudiante:</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ estudianteSeleccionado.cedula }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <span class="text-slate-400 block">Siglas de la Asignatura:</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ notaAuditoria.anu_siglas_ucu }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Unidades de Crédito (UC):</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ notaAuditoria.anu_nuc }} UC</span>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div>
                <span class="text-slate-400 block">Nota Definitiva:</span>
                <span class="font-mono font-bold text-emerald-600">{{ notaAuditoria.anu_nota }} / 25</span>
              </div>
              <div>
                <span class="text-slate-400 block">Condición Final:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ notaAuditoria.anu_condicion === 'A' ? 'A (Aprobado)' : 'R (Reprobado)' }}</span>
              </div>
              <div>
                <span class="text-slate-400 block">Condición de Repitencia:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ notaAuditoria.anu_condicion_repitencia }} (Normal)</span>
              </div>
            </div>

            <div>
              <span class="text-slate-400 block">Observaciones de Control de Estudios (DACE):</span>
              <p class="mt-1 p-2 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                Calificación consolidada del acta docente final. Sin discrepancias en auditoría de actas físicas.
              </p>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <Button size="sm" variant="outline" @click="modalAuditoria = false">
              Cerrar Detalle
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
