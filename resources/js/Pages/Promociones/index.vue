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
  GraduationCap,
  Award,
  Calendar,
  BookOpen,
  Users,
  Plus,
  Pencil,
  FileText,
  Search,
  ChevronRight,
  TrendingUp,
  Bookmark,
  CheckCircle2,
  Clock,
  ExternalLink,
  Layers,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Egreso y Certificaciones', href: route('promociones.index') },
  { title: 'Promociones Académicas', href: route('promociones.index') },
];

// Lista de promociones registradas (tabla 'promocion')
const promociones = ref([
  {
    prm_codigo: 18,
    prm_nombre: 'XVIII Promoción de Ingenieros y Licenciados Bicentenaria',
    prm_fecha_egreso: '2026-10-15',
    prm_tipo_promocion: 'Ordinaria',
    prm_mayor_indice: 19.82,
    prm_menor_indice: 13.50,
    prm_cantidad_egresado: 148,
    prm_tomo: 'TOMO-XXIV',
    prm_estatus: 'A', // A = Activa / En proceso
    programas: [
      {
        ppr_codigo: 101,
        programa: 'PNF en Informática',
        titulo: 'Ingeniero en Informática',
        ppr_cantidad_egresado: 52,
        ppr_mayor_indice: 19.82,
        ppr_menor_indice: 14.10,
        ppr_estatus: 'A',
      },
      {
        ppr_codigo: 102,
        programa: 'PNF en Administración',
        titulo: 'Licenciado en Administración',
        ppr_cantidad_egresado: 48,
        ppr_mayor_indice: 19.45,
        ppr_menor_indice: 13.50,
        ppr_estatus: 'A',
      },
      {
        ppr_codigo: 103,
        programa: 'PNF en Agroalimentación',
        titulo: 'Ingeniero en Agroalimentación',
        ppr_cantidad_egresado: 48,
        ppr_mayor_indice: 18.90,
        ppr_menor_indice: 14.00,
        ppr_estatus: 'A',
      },
    ],
  },
  {
    prm_codigo: 17,
    prm_nombre: 'XVII Promoción Magna Conmemorativa Simón Bolívar',
    prm_fecha_egreso: '2025-11-20',
    prm_tipo_promocion: 'Ordinaria',
    prm_mayor_indice: 19.95,
    prm_menor_indice: 12.80,
    prm_cantidad_egresado: 215,
    prm_tomo: 'TOMO-XXIII',
    prm_estatus: 'C', // C = Cerrada / Protocolizada
    programas: [
      {
        ppr_codigo: 91,
        programa: 'PNF en Informática',
        titulo: 'Ingeniero en Informática',
        ppr_cantidad_egresado: 75,
        ppr_mayor_indice: 19.95,
        ppr_menor_indice: 13.50,
        ppr_estatus: 'C',
      },
      {
        ppr_codigo: 92,
        programa: 'PNF en Electricidad',
        titulo: 'Ingeniero Electricista',
        ppr_cantidad_egresado: 60,
        ppr_mayor_indice: 18.75,
        ppr_menor_indice: 12.80,
        ppr_estatus: 'C',
      },
      {
        ppr_codigo: 93,
        programa: 'PNF en Mecánica',
        titulo: 'Ingeniero Mecánico',
        ppr_cantidad_egresado: 80,
        ppr_mayor_indice: 19.10,
        ppr_menor_indice: 13.00,
        ppr_estatus: 'C',
      },
    ],
  },
  {
    prm_codigo: 16,
    prm_nombre: 'I Cohorte Salidas Intermedias T.S.U.',
    prm_fecha_egreso: '2025-05-18',
    prm_tipo_promocion: 'Salida Intermedia',
    prm_mayor_indice: 19.20,
    prm_menor_indice: 12.50,
    prm_cantidad_egresado: 94,
    prm_tomo: 'TOMO-XXII-TSU',
    prm_estatus: 'C',
    programas: [
      {
        ppr_codigo: 81,
        programa: 'PNF en Informática',
        titulo: 'Técnico Superior Universitario en Informática',
        ppr_cantidad_egresado: 54,
        ppr_mayor_indice: 19.20,
        ppr_menor_indice: 12.50,
        ppr_estatus: 'C',
      },
      {
        ppr_codigo: 82,
        programa: 'PNF en Administración',
        titulo: 'Técnico Superior Universitario en Administración',
        ppr_cantidad_egresado: 40,
        ppr_mayor_indice: 18.60,
        ppr_menor_indice: 12.75,
        ppr_estatus: 'C',
      },
    ],
  },
]);

// Filtros
const busqueda = ref('');
const filtroEstatus = ref('TODOS');

const promocionesFiltradas = computed(() => {
  return promociones.value.filter(p => {
    const coincideTexto = p.prm_nombre.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                          p.prm_tomo.toLowerCase().includes(busqueda.value.toLowerCase());
    const coincideEstatus = filtroEstatus.value === 'TODOS' || p.prm_estatus === filtroEstatus.value;
    return coincideTexto && coincideEstatus;
  });
});

// Resumen estadístico
const totalEgresadosHistorico = computed(() => {
  return promociones.value.reduce((acc, p) => acc + p.prm_cantidad_egresado, 0);
});

// Modal de Creación / Edición
const mostrarModal = ref(false);
const modoEdicion = ref(false);
const form = ref({
  prm_codigo: null as number | null,
  prm_nombre: '',
  prm_fecha_egreso: '',
  prm_tipo_promocion: 'Ordinaria',
  prm_tomo: '',
  prm_estatus: 'A',
});

const abrirCrear = () => {
  modoEdicion.value = false;
  form.value = {
    prm_codigo: promociones.value.length + 1,
    prm_nombre: '',
    prm_fecha_egreso: '',
    prm_tipo_promocion: 'Ordinaria',
    prm_tomo: `TOMO-XXV`,
    prm_estatus: 'A',
  };
  mostrarModal.value = true;
};

const abrirEditar = (prm: any) => {
  modoEdicion.value = true;
  form.value = {
    prm_codigo: prm.prm_codigo,
    prm_nombre: prm.prm_nombre,
    prm_fecha_egreso: prm.prm_fecha_egreso,
    prm_tipo_promocion: prm.prm_tipo_promocion,
    prm_tomo: prm.prm_tomo,
    prm_estatus: prm.prm_estatus,
  };
  mostrarModal.value = true;
};

const guardarPromocion = () => {
  if (modoEdicion.value) {
    const idx = promociones.value.findIndex(p => p.prm_codigo === form.value.prm_codigo);
    if (idx !== -1) {
      promociones.value[idx].prm_nombre = form.value.prm_nombre;
      promociones.value[idx].prm_fecha_egreso = form.value.prm_fecha_egreso;
      promociones.value[idx].prm_tipo_promocion = form.value.prm_tipo_promocion;
      promociones.value[idx].prm_tomo = form.value.prm_tomo;
      promociones.value[idx].prm_estatus = form.value.prm_estatus;
    }
  } else {
    promociones.value.unshift({
      prm_codigo: form.value.prm_codigo || promociones.value.length + 1,
      prm_nombre: form.value.prm_nombre,
      prm_fecha_egreso: form.value.prm_fecha_egreso,
      prm_tipo_promocion: form.value.prm_tipo_promocion,
      prm_mayor_indice: 0.0,
      prm_menor_indice: 0.0,
      prm_cantidad_egresado: 0,
      prm_tomo: form.value.prm_tomo,
      prm_estatus: form.value.prm_estatus,
      programas: [],
    });
  }
  mostrarModal.value = false;
};
</script>

<template>
  <Head title="Promociones de Grado y Egresados - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- HEADER DE LA PÁGINA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
            <GraduationCap class="h-3.5 w-3.5" />
            Módulo de Grado • Cohortes y Programas Académicos
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Promociones Académicas
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Gestión centralizada de cohortes de egreso, asignación de tomos de grado, consolidación de índices mayores y menores, y vinculación con los programas formativos.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" variant="outline" class="gap-1.5" as-child>
            <Link :href="route('promociones.graduandos')">
              <Award class="h-4 w-4" />
              Padrón de Graduandos
            </Link>
          </Button>

          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" @click="abrirCrear">
            <Plus class="h-4 w-4" />
            Nueva Promoción
          </Button>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Promociones Registradas -->
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Cohortes de Grado</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ promociones.length }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">1 activa en proceso</span>
            </div>
            <div class="h-11 w-11 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600">
              <GraduationCap class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <!-- Total Egresados -->
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Total Egresados</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ totalEgresadosHistorico }}</div>
              <span class="text-[11px] text-slate-400">Ingenieros, Lics y TSUs</span>
            </div>
            <div class="h-11 w-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-600">
              <Users class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <!-- Mayor Índice General -->
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Mayor Índice (IRA Máximo)</span>
              <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">19.95</div>
              <span class="text-[11px] text-slate-400">Mención Summa Cum Laude</span>
            </div>
            <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600">
              <Award class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <!-- Próximo Acto -->
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Próximo Acto Solemne</span>
              <div class="text-base font-bold text-slate-900 dark:text-slate-100 mt-1">15 Octubre 2026</div>
              <span class="text-[11px] text-red-600 font-medium">XVIII Promoción</span>
            </div>
            <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-600">
              <Calendar class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- BARRA DE FILTROS Y BÚSQUEDA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-sm">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar promoción o tomo..."
            class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-500 focus:outline-none"
          />
        </div>

        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-500">Estado:</span>
          <select
            v-model="filtroEstatus"
            class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
          >
            <option value="TODOS">Todos los Estados</option>
            <option value="A">Activa / En Proceso</option>
            <option value="C">Cerrada / Protocolizada</option>
          </select>
        </div>
      </div>

      <!-- LISTADO DE PROMOCIONES (CARDS CON DESGLOSE DE PROGRAMAS) -->
      <div class="space-y-4">
        <div
          v-for="prm in promocionesFiltradas"
          :key="prm.prm_codigo"
          class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-sm overflow-hidden transition-all hover:border-slate-300 dark:hover:border-slate-700"
        >
          <!-- Encabezado de la promoción -->
          <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/30 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-start gap-3">
              <div class="mt-1 h-10 w-10 rounded-xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 font-bold text-sm shrink-0">
                #{{ prm.prm_codigo }}
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">
                    {{ prm.prm_nombre }}
                  </h3>
                  <Badge :variant="prm.prm_estatus === 'A' ? 'successCustom' : 'outline'">
                    {{ prm.prm_estatus === 'A' ? 'ACTIVA / EN PROCESO' : 'PROTOCOLIZADA' }}
                  </Badge>
                  <span class="text-xs px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ prm.prm_tomo }}
                  </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500 mt-1 flex-wrap">
                  <span class="flex items-center gap-1">
                    <Calendar class="h-3.5 w-3.5 text-slate-400" />
                    Fecha Egreso: <strong class="text-slate-700 dark:text-slate-300">{{ prm.prm_fecha_egreso }}</strong>
                  </span>
                  <span>•</span>
                  <span>Tipo: <strong class="text-slate-700 dark:text-slate-300">{{ prm.prm_tipo_promocion }}</strong></span>
                  <span>•</span>
                  <span>Graduandos: <strong class="text-red-600">{{ prm.prm_cantidad_egresado }} egresados</strong></span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Button size="xs" variant="outline" class="gap-1" @click="abrirEditar(prm)">
                <Pencil class="h-3.5 w-3.5" />
                Editar
              </Button>
              <Button size="xs" class="bg-red-600 hover:bg-red-700 text-white gap-1" as-child>
                <Link :href="route('promociones.graduandos')">
                  Ver Graduandos
                  <ChevronRight class="h-3.5 w-3.5" />
                </Link>
              </Button>
            </div>
          </div>

          <!-- Métricas del cuadro general de la promoción -->
          <div class="p-5 grid gap-4 sm:grid-cols-3 border-b border-slate-100 dark:border-slate-800 text-xs">
            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div>
                <span class="text-slate-400 block">Mayor Índice Académico:</span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ prm.prm_mayor_indice.toFixed(2) }} pts</span>
              </div>
              <Award class="h-6 w-6 text-emerald-500/30" />
            </div>

            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div>
                <span class="text-slate-400 block">Menor Índice Académico:</span>
                <span class="text-lg font-bold text-slate-700 dark:text-slate-300 font-mono">{{ prm.prm_menor_indice.toFixed(2) }} pts</span>
              </div>
              <TrendingUp class="h-6 w-6 text-slate-400/30" />
            </div>

            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <div>
                <span class="text-slate-400 block">Libro de Grados (Tomo):</span>
                <span class="text-base font-bold text-slate-900 dark:text-slate-100 font-mono">{{ prm.prm_tomo }}</span>
              </div>
              <Bookmark class="h-6 w-6 text-amber-500/30" />
            </div>
          </div>

          <!-- Desglose por Programas (promocion_programa) -->
          <div class="p-5">
            <h4 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
              <Layers class="h-3.5 w-3.5 text-red-600" />
              Desglose de Programas y Títulos Otorgados
            </h4>

            <div class="grid gap-3 sm:grid-cols-3">
              <div
                v-for="prog in prm.programas"
                :key="prog.ppr_codigo"
                class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-2 text-xs"
              >
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <span class="font-bold text-slate-900 dark:text-slate-100 block">{{ prog.programa }}</span>
                    <span class="text-[11px] text-red-600 dark:text-red-400 font-medium">{{ prog.titulo }}</span>
                  </div>
                  <Badge variant="outline" class="font-mono text-[10px]">{{ prog.ppr_cantidad_egresado }} est.</Badge>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                  <span class="text-slate-400">Rango de IRA:</span>
                  <span class="font-mono font-medium text-slate-700 dark:text-slate-300">
                    {{ prog.ppr_menor_indice.toFixed(2) }} - <strong class="text-emerald-600">{{ prog.ppr_mayor_indice.toFixed(2) }}</strong>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL CREACIÓN / EDICIÓN DE PROMOCIÓN -->
      <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                {{ modoEdicion ? 'Editar Promoción Académica' : 'Nueva Promoción de Grado' }}
              </h3>
              <p class="text-xs text-slate-500">Información general de la cohorte</p>
            </div>
            <button @click="mostrarModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Nombre de la Promoción:
              </label>
              <input
                v-model="form.prm_nombre"
                type="text"
                placeholder="Ej: XIX Promoción de Ingenieros y Licenciados"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Fecha de Egreso / Acto de Grado:
                </label>
                <input
                  v-model="form.prm_fecha_egreso"
                  type="date"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Tipo de Promoción:
                </label>
                <select
                  v-model="form.prm_tipo_promocion"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="Ordinaria">Ordinaria (Secretaría)</option>
                  <option value="Extraordinaria">Extraordinaria</option>
                  <option value="Salida Intermedia">Salida Intermedia T.S.U.</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Identificador de Tomo / Libro:
                </label>
                <input
                  v-model="form.prm_tomo"
                  type="text"
                  placeholder="Ej: TOMO-XXIV"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Estatus de la Promoción:
                </label>
                <select
                  v-model="form.prm_estatus"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="A">A - Activa / En Proceso</option>
                  <option value="C">C - Cerrada / Protocolizada</option>
                </select>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="mostrarModal = false">
              Cancelar
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarPromocion">
              Guardar Promoción
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
