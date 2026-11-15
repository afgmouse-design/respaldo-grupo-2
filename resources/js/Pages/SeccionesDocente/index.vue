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
  Users,
  BookOpen,
  Calendar,
  Building2,
  Plus,
  Search,
  Pencil,
  ArrowRight,
  CheckCircle2,
  Layers,
  Sparkles,
  UserCheck,
  AlertCircle,
  FileSpreadsheet,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Lapsos y Secciones', href: route('secciones.docente') },
  { title: 'Secciones y Asignación Docente', href: route('secciones.docente') },
];

// Lapsos académicos
const lapsos = [
  { id: 20261, nombre: 'Lapso 2026-I (Activo)' },
  { id: 20252, nombre: 'Lapso 2025-II' },
];
const lapsoSeleccionado = ref(lapsos[0].id);

// Sedes
const sedes = [
  { id: 1, nombre: 'Sede Central Acarigua' },
  { id: 2, nombre: 'Sede Guanare' },
  { id: 3, nombre: 'Sede Ospino' },
];
const sedeSeleccionada = ref('TODAS');

// Docentes disponibles para asignación
const docentesDisponibles = [
  { id: 201, nombre: 'Prof. Carlos Mendoza Silva', cedula: '15987654', dedicacion: 'Tiempo Completo (40h)' },
  { id: 202, nombre: 'Prof. Roberto González Ramos', cedula: '14235890', dedicacion: 'Dedicación Exclusiva' },
  { id: 203, nombre: 'Prof. Elena Torres Castillo', cedula: '18456123', dedicacion: 'Medio Tiempo (20h)' },
  { id: 204, nombre: 'Prof. Marcos Valero Ruiz', cedula: '16789456', dedicacion: 'Tiempo Completo (40h)' },
];

// Secciones con unidad y docente asignado (seccion y seccion_unidad_docente)
const secciones = ref([
  {
    sud_codigo: 1001,
    sec_codigo: 101,
    sec_nombre: 'INF-401A',
    unidad_codigo: 'INF-401',
    unidad_nombre: 'Bases de Datos Avanzadas y Big Data',
    uc: 5,
    programa: 'PNF en Informática',
    trayecto: 'Trayecto IV',
    sede: 'Sede Central Acarigua',
    docente_id: 201,
    docente_nombre: 'Prof. Carlos Mendoza Silva',
    docente_cedula: '15987654',
    sud_capacidad: 35,
    sud_nro_inscritos: 28,
    sud_condicion: 'R', // R = Regular
    sud_nombre_plan: 'Plan Semestral 2026-I',
    sud_estatus: 'A',
  },
  {
    sud_codigo: 1002,
    sec_codigo: 102,
    sec_nombre: 'INF-302B',
    unidad_codigo: 'INF-302',
    unidad_nombre: 'Ingeniería del Software II',
    uc: 6,
    programa: 'PNF en Informática',
    trayecto: 'Trayecto III',
    sede: 'Sede Central Acarigua',
    docente_id: 202,
    docente_nombre: 'Prof. Roberto González Ramos',
    docente_cedula: '14235890',
    sud_capacidad: 32,
    sud_nro_inscritos: 32,
    sud_condicion: 'R',
    sud_nombre_plan: 'Plan Semestral 2026-I',
    sud_estatus: 'A',
  },
  {
    sud_codigo: 1003,
    sec_codigo: 103,
    sec_nombre: 'ADM-201A',
    unidad_codigo: 'ADM-201',
    unidad_nombre: 'Contabilidad de Costos y Presupuesto',
    uc: 5,
    programa: 'PNF en Administración',
    trayecto: 'Trayecto II',
    sede: 'Sede Guanare',
    docente_id: 203,
    docente_nombre: 'Prof. Elena Torres Castillo',
    docente_cedula: '18456123',
    sud_capacidad: 40,
    sud_nro_inscritos: 36,
    sud_condicion: 'R',
    sud_nombre_plan: 'Plan Semestral 2026-I',
    sud_estatus: 'A',
  },
  {
    sud_codigo: 1004,
    sec_codigo: 104,
    sec_nombre: 'AGR-101A',
    unidad_codigo: 'AGR-101',
    unidad_nombre: 'Sistemas Agroecológicos y Suelos',
    uc: 6,
    programa: 'PNF en Agroalimentación',
    trayecto: 'Trayecto I',
    sede: 'Sede Ospino',
    docente_id: 204,
    docente_nombre: 'Prof. Marcos Valero Ruiz',
    docente_cedula: '16789456',
    sud_capacidad: 30,
    sud_nro_inscritos: 19,
    sud_condicion: 'R',
    sud_nombre_plan: 'Plan Semestral 2026-I',
    sud_estatus: 'A',
  },
]);

// Filtros
const busqueda = ref('');
const filtroPrograma = ref('TODOS');

const seccionesFiltradas = computed(() => {
  return secciones.value.filter(s => {
    const coincideTexto = s.sec_nombre.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                          s.unidad_nombre.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                          s.docente_nombre.toLowerCase().includes(busqueda.value.toLowerCase());
    const coincideProg = filtroPrograma.value === 'TODOS' || s.programa === filtroPrograma.value;
    const coincideSede = sedeSeleccionada.value === 'TODAS' || s.sede === sedeSeleccionada.value;
    return coincideTexto && coincideProg && coincideSede;
  });
});

// Resumen estadístico
const totalCapacidad = computed(() => secciones.value.reduce((acc, s) => acc + s.sud_capacidad, 0));
const totalInscritos = computed(() => secciones.value.reduce((acc, s) => acc + s.sud_nro_inscritos, 0));
const porcentajeOcupacion = computed(() => totalCapacidad.value > 0 ? Math.round((totalInscritos.value / totalCapacidad.value) * 100) : 0);

// Modal Apertura / Asignación
const modalSeccion = ref(false);
const formSeccion = ref({
  sec_nombre: '',
  unidad_nombre: 'Bases de Datos Avanzadas y Big Data',
  programa: 'PNF en Informática',
  trayecto: 'Trayecto IV',
  sede: 'Sede Central Acarigua',
  docente_id: docentesDisponibles[0].id,
  sud_capacidad: 35,
  sud_condicion: 'R',
});

const abrirCrearSeccion = () => {
  formSeccion.value = {
    sec_nombre: `INF-${Math.floor(100 + Math.random() * 900)}`,
    unidad_nombre: 'Bases de Datos Avanzadas y Big Data',
    programa: 'PNF en Informática',
    trayecto: 'Trayecto IV',
    sede: 'Sede Central Acarigua',
    docente_id: docentesDisponibles[0].id,
    sud_capacidad: 35,
    sud_condicion: 'R',
  };
  modalSeccion.value = true;
};

const guardarSeccion = () => {
  const doc = docentesDisponibles.find(d => d.id === Number(formSeccion.value.docente_id));
  secciones.value.unshift({
    sud_codigo: Date.now(),
    sec_codigo: secciones.value.length + 101,
    sec_nombre: formSeccion.value.sec_nombre,
    unidad_codigo: 'INF-401',
    unidad_nombre: formSeccion.value.unidad_nombre,
    uc: 5,
    programa: formSeccion.value.programa,
    trayecto: formSeccion.value.trayecto,
    sede: formSeccion.value.sede,
    docente_id: doc?.id || 201,
    docente_nombre: doc?.nombre || 'Docente Asignado',
    docente_cedula: doc?.cedula || 'V-0000000',
    sud_capacidad: formSeccion.value.sud_capacidad,
    sud_nro_inscritos: 0,
    sud_condicion: formSeccion.value.sud_condicion,
    sud_nombre_plan: 'Plan Semestral 2026-I',
    sud_estatus: 'A',
  });
  modalSeccion.value = false;
};
</script>

<template>
  <Head title="Secciones y Asignación Docente - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
            <Users class="h-3.5 w-3.5" />
            Planificación Académica y Oferta de Secciones
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Secciones y Asignación Docente
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Gestión de oferta de secciones por lapso académico, adscripción de unidades curriculares al cuerpo docente titular, aforos y monitoreo de matrícula.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" variant="outline" class="gap-1.5" as-child>
            <Link :href="route('calificaciones.planificacion')">
              <FileSpreadsheet class="h-4 w-4" />
              Plan de Evaluación
            </Link>
          </Button>

          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" @click="abrirCrearSeccion">
            <Plus class="h-4 w-4" />
            Abrir Nueva Sección
          </Button>
        </div>
      </div>

      <!-- FILTROS SUPERIORES DE LAPSO Y SEDE -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardContent class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Lapso Académico:</span>
              <select
                v-model="lapsoSeleccionado"
                class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
              >
                <option v-for="l in lapsos" :key="l.id" :value="l.id">{{ l.nombre }}</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Sede:</span>
              <select
                v-model="sedeSeleccionada"
                class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
              >
                <option value="TODAS">Todas las Sedes</option>
                <option v-for="s in sedes" :key="s.id" :value="s.nombre">{{ s.nombre }}</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            Proceso de Inscripción Regular y Asignación Docente Activo
          </div>
        </CardContent>
      </Card>

      <!-- KPI CARDS -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Secciones Aperturadas</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ secciones.length }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">100% con docente titular</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 flex items-center justify-center">
              <Layers class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Capacidad Ofertada</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ totalCapacidad }}</div>
              <span class="text-[11px] text-slate-400">Cupos totales de aula</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center">
              <Building2 class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Estudiantes Matriculados</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ totalInscritos }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">{{ totalCapacidad - totalInscritos }} cupos disponibles</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
              <Users class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Tasa de Ocupación</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ porcentajeOcupacion }}%</div>
              <span class="text-[11px] text-slate-400">Rango óptimo institucional</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
              <Sparkles class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TABLA DETALLADA DE SECCIONES (SUD) -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <BookOpen class="h-5 w-5 text-red-600" />
                Nómina de Secciones y Carga Docente
              </CardTitle>
              <CardDescription class="text-xs">Distribución de secciones con cupos, inscritos y profesores titulares adscritos</CardDescription>
            </div>

            <div class="relative w-full sm:w-64">
              <Search class="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" />
              <input
                v-model="busqueda"
                type="text"
                placeholder="Buscar sección o docente..."
                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-1 focus:ring-red-500 focus:outline-none"
              />
            </div>
          </div>
        </CardHeader>

        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-slate-900/50 uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3 font-semibold">Código de Sección</th>
                  <th class="px-4 py-3 font-semibold">Unidad Curricular</th>
                  <th class="px-4 py-3 font-semibold">Docente Titular</th>
                  <th class="px-4 py-3 font-semibold text-center">Sede</th>
                  <th class="px-4 py-3 font-semibold text-center">Aforo / Ocupación</th>
                  <th class="px-4 py-3 font-semibold text-center">Condición</th>
                  <th class="px-4 py-3 font-semibold text-center">Estatus</th>
                  <th class="px-4 py-3 font-semibold text-center">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="sec in seccionesFiltradas" :key="sec.sud_codigo" class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-3 font-mono font-bold text-red-600 dark:text-red-400">
                    {{ sec.sec_nombre }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ sec.unidad_nombre }}</div>
                    <div class="text-[11px] text-slate-400">{{ sec.programa }} • {{ sec.trayecto }} ({{ sec.uc }} UC)</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ sec.docente_nombre }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">C.I. {{ sec.docente_cedula }}</div>
                  </td>
                  <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-400">
                    {{ sec.sede }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1 font-bold text-slate-800 dark:text-slate-200">
                      <span>{{ sec.sud_nro_inscritos }}</span>
                      <span class="text-slate-400">/ {{ sec.sud_capacidad }}</span>
                    </div>
                    <div class="h-1.5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto mt-1 overflow-hidden">
                      <div
                        class="h-full rounded-full"
                        :class="sec.sud_nro_inscritos >= sec.sud_capacidad ? 'bg-red-600' : 'bg-emerald-600'"
                        :style="{ width: `${Math.min(100, Math.round((sec.sud_nro_inscritos / sec.sud_capacidad) * 100))}%` }"
                      ></div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Badge variant="outline" class="text-[10px]">
                      {{ sec.sud_condicion === 'R' ? 'REGULAR' : 'INTENSIVO' }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Badge variant="successCustom" class="text-[10px]">
                      ABIERTA
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Button size="xs" variant="outline" class="h-7 text-xs" as-child>
                      <Link :href="route('calificaciones.carga')">
                        Ver Acta
                      </Link>
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- MODAL ABRIR SECCIÓN / SUD -->
      <div v-if="modalSeccion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Abrir Nueva Sección y Asignación Docente</h3>
              <p class="text-xs text-slate-500">Apertura y asignación de carga académica</p>
            </div>
            <button @click="modalSeccion = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Código de Sección:
                </label>
                <input
                  v-model="formSeccion.sec_nombre"
                  type="text"
                  placeholder="Ej: INF-401C"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Capacidad de Estudiantes:
                </label>
                <input
                  v-model.number="formSeccion.sud_capacidad"
                  type="number"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Programa PNF y Sede:
              </label>
              <div class="grid grid-cols-2 gap-3">
                <select
                  v-model="formSeccion.programa"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="PNF en Informática">PNF en Informática</option>
                  <option value="PNF en Administración">PNF en Administración</option>
                  <option value="PNF en Agroalimentación">PNF en Agroalimentación</option>
                </select>

                <select
                  v-model="formSeccion.sede"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option v-for="s in sedes" :key="s.id" :value="s.nombre">{{ s.nombre }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Docente Titular Asignado:
              </label>
              <select
                v-model="formSeccion.docente_id"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option v-for="d in docentesDisponibles" :key="d.id" :value="d.id">
                  {{ d.nombre }} (C.I. {{ d.cedula }}) - {{ d.dedicacion }}
                </option>
              </select>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalSeccion = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarSeccion">
              Aperturar Sección
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
