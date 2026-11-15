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
  Award,
  GraduationCap,
  Search,
  Filter,
  ArrowLeft,
  FileText,
  Printer,
  ShieldAlert,
  ShieldCheck,
  CheckCircle2,
  Calendar,
  Layers,
  Sparkles,
  Medal,
  BookMarked,
  Info,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Promociones Académicas', href: route('promociones.index') },
  { title: 'Padrón de Graduandos y Cuadro de Honor', href: route('promociones.graduandos') },
];

// Lista de promociones para filtrar
const promociones = [
  { id: 18, nombre: 'XVIII Promoción Bicentenaria (2026)' },
  { id: 17, nombre: 'XVII Promoción Simón Bolívar (2025)' },
  { id: 16, nombre: 'I Cohorte Salidas Intermedias TSU (2025)' },
];

const promocionSeleccionada = ref(promociones[0].id);

// Padrón de graduandos (tabla 'promocion_estudiante')
const graduandos = ref([
  {
    pes_codigo: 501,
    pes_cedula: '30123456',
    nombres: 'Alejandro José González Pérez',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    titulo: 'Ingeniero en Informática',
    pes_ira: 19.82,
    pes_numero_posicion_promocion: 1, // Primer lugar de toda la promoción
    pes_numero_posicion_programa: 1,   // Primer lugar en informática
    pes_tipo_graduando: 'S',          // S = Summa Cum Laude
    tipo_nombre: 'Summa Cum Laude',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0142',
    pes_salidas_folio: 'SF-089',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Acreditado con méritos académicos sobresalientes',
  },
  {
    pes_codigo: 502,
    pes_cedula: '28456789',
    nombres: 'Mariana Victoria Rivas Delgado',
    programa: 'PNF en Administración',
    sede: 'Sede Guanare',
    titulo: 'Licenciada en Administración',
    pes_ira: 19.45,
    pes_numero_posicion_promocion: 2,
    pes_numero_posicion_programa: 1,
    pes_tipo_graduando: 'M', // M = Magna Cum Laude
    tipo_nombre: 'Magna Cum Laude',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0143',
    pes_salidas_folio: 'SF-090',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Acreditación aprobada por Consejo Directivo',
  },
  {
    pes_codigo: 503,
    pes_cedula: '31234567',
    nombres: 'Valeria Sofía Colmenárez Silva',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    titulo: 'Ingeniero en Informática',
    pes_ira: 19.15,
    pes_numero_posicion_promocion: 3,
    pes_numero_posicion_programa: 2,
    pes_tipo_graduando: 'M',
    tipo_nombre: 'Magna Cum Laude',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0144',
    pes_salidas_folio: 'SF-091',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Récord sin observaciones',
  },
  {
    pes_codigo: 504,
    pes_cedula: '29112233',
    nombres: 'Daniel Eduardo Pineda Castillo',
    programa: 'PNF en Agroalimentación',
    sede: 'Sede Ospino',
    titulo: 'Ingeniero en Agroalimentación',
    pes_ira: 18.90,
    pes_numero_posicion_promocion: 4,
    pes_numero_posicion_programa: 1,
    pes_tipo_graduando: 'C', // C = Cum Laude
    tipo_nombre: 'Cum Laude',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0145',
    pes_salidas_folio: 'SF-092',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Aprobación unánime',
  },
  {
    pes_codigo: 505,
    pes_cedula: '27123987',
    nombres: 'José Leonardo Mendoza Morales',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    titulo: 'Ingeniero en Informática',
    pes_ira: 16.75,
    pes_numero_posicion_promocion: 12,
    pes_numero_posicion_programa: 7,
    pes_tipo_graduando: 'R', // R = Regular
    tipo_nombre: 'Regular',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0146',
    pes_salidas_folio: 'SF-093',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Requisitos completos',
  },
  {
    pes_codigo: 506,
    pes_cedula: '26987456',
    nombres: 'Carla Beatriz Escalona Torres',
    programa: 'PNF en Administración',
    sede: 'Sede Turén',
    titulo: 'Licenciada en Administración',
    pes_ira: 15.60,
    pes_numero_posicion_promocion: 24,
    pes_numero_posicion_programa: 14,
    pes_tipo_graduando: 'R',
    tipo_nombre: 'Regular',
    pes_tomo: 'TOMO-XXIV',
    pes_folio: 'FOL-0147',
    pes_salidas_folio: 'SF-094',
    pes_fecha_salida_folio: '2026-10-01',
    pes_estatus: 'A',
    pes_observacion_inactivar: '',
    pes_observacion_activar: 'Solvente en DACE y Biblioteca',
  },
]);

// Filtros de búsqueda
const busqueda = ref('');
const filtroPrograma = ref('TODOS');
const filtroMencion = ref('TODOS');

const graduandosFiltrados = computed(() => {
  return graduandos.value.filter(g => {
    const coincideTexto = g.nombres.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                          g.pes_cedula.includes(busqueda.value) ||
                          g.pes_folio.toLowerCase().includes(busqueda.value.toLowerCase());
    const coincideProg = filtroPrograma.value === 'TODOS' || g.programa === filtroPrograma.value;
    const coincideMencion = filtroMencion.value === 'TODOS' || g.pes_tipo_graduando === filtroMencion.value;
    return coincideTexto && coincideProg && coincideMencion;
  });
});

// Podio Cuadro de Honor (Top 3 graduandos con mayor índice)
const cuadroHonor = computed(() => {
  return [...graduandos.value]
    .filter(g => g.pes_estatus === 'A')
    .sort((a, b) => b.pes_ira - a.pes_ira)
    .slice(0, 3);
});

// Modales
const modalProtocolo = ref(false);
const modalInactivar = ref(false);
const graduandoSeleccionado = ref<any>(null);

const formProtocolo = ref({
  pes_tomo: '',
  pes_folio: '',
  pes_salidas_folio: '',
  pes_fecha_salida_folio: '',
});

const formInactivar = ref({
  observacion: '',
});

const abrirProtocolo = (item: any) => {
  graduandoSeleccionado.value = item;
  formProtocolo.value = {
    pes_tomo: item.pes_tomo,
    pes_folio: item.pes_folio,
    pes_salidas_folio: item.pes_salidas_folio,
    pes_fecha_salida_folio: item.pes_fecha_salida_folio,
  };
  modalProtocolo.value = true;
};

const guardarProtocolo = () => {
  if (graduandoSeleccionado.value) {
    graduandoSeleccionado.value.pes_tomo = formProtocolo.value.pes_tomo;
    graduandoSeleccionado.value.pes_folio = formProtocolo.value.pes_folio;
    graduandoSeleccionado.value.pes_salidas_folio = formProtocolo.value.pes_salidas_folio;
    graduandoSeleccionado.value.pes_fecha_salida_folio = formProtocolo.value.pes_fecha_salida_folio;
  }
  modalProtocolo.value = false;
};

const abrirInactivar = (item: any) => {
  graduandoSeleccionado.value = item;
  formInactivar.value.observacion = item.pes_observacion_inactivar || '';
  modalInactivar.value = true;
};

const toggleEstatus = () => {
  if (graduandoSeleccionado.value) {
    if (graduandoSeleccionado.value.pes_estatus === 'A') {
      graduandoSeleccionado.value.pes_estatus = 'I';
      graduandoSeleccionado.value.pes_observacion_inactivar = formInactivar.value.observacion;
    } else {
      graduandoSeleccionado.value.pes_estatus = 'A';
      graduandoSeleccionado.value.pes_observacion_activar = formInactivar.value.observacion;
    }
  }
  modalInactivar.value = false;
};
</script>

<template>
  <Head title="Padrón de Graduandos y Cuadro de Honor - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- ENCABEZADO -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-900/50 mb-2">
            <Award class="h-3.5 w-3.5" />
            Cuadro de Honor y Orden de Mérito Académico
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Padrón de Graduandos y Cuadro de Honor
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Asignación de posición general de promoción y por programa, menciones honoríficas, registro de Tomo y Folio de grado, y emisión de títulos universitarios.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" variant="outline" class="gap-1.5" as-child>
            <Link :href="route('promociones.index')">
              <ArrowLeft class="h-4 w-4" />
              Ver Promociones
            </Link>
          </Button>

          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" as-child>
            <Link :href="route('calificaciones.consolidacion')">
              <BookMarked class="h-4 w-4" />
              Consolidación IRA
            </Link>
          </Button>
        </div>
      </div>

      <!-- PODIO / CUADRO DE HONOR (TOP 3 MEJORES ÍNDICES) -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Sparkles class="h-4 w-4 text-amber-500" />
            Cuadro de Honor • Primeros Lugares de la Promoción
          </h3>
          <span class="text-xs text-slate-400">XVIII Promoción Bicentenaria</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <!-- 1er Lugar -->
          <Card
            v-if="cuadroHonor[0]"
            class="relative overflow-hidden border-amber-300/80 dark:border-amber-700/60 bg-gradient-to-br from-amber-50/60 via-white to-amber-100/40 dark:from-slate-950 dark:via-amber-950/20 dark:to-slate-900 shadow-sm"
          >
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-amber-400/20 flex items-center justify-center text-amber-600 text-xl font-black">
              1°
            </div>
            <CardContent class="p-5 space-y-2">
              <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wider">
                <Medal class="h-4 w-4" />
                Puesto 1 • Promoción General
              </div>
              <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">{{ cuadroHonor[0].nombres }}</h4>
              <p class="text-xs text-slate-500 font-mono">C.I. {{ cuadroHonor[0].pes_cedula }} • {{ cuadroHonor[0].programa }}</p>
              
              <div class="pt-2 flex items-center justify-between">
                <div>
                  <span class="text-[11px] text-slate-400 block">IRA General:</span>
                  <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ cuadroHonor[0].pes_ira.toFixed(2) }}</span>
                </div>
                <Badge class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px]">
                  {{ cuadroHonor[0].tipo_nombre }}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <!-- 2do Lugar -->
          <Card
            v-if="cuadroHonor[1]"
            class="relative overflow-hidden border-slate-300 dark:border-slate-700 bg-gradient-to-br from-slate-50 via-white to-slate-100/50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-900 shadow-sm"
          >
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-slate-400/20 flex items-center justify-center text-slate-600 text-xl font-black">
              2°
            </div>
            <CardContent class="p-5 space-y-2">
              <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                <Medal class="h-4 w-4" />
                Puesto 2 • Promoción General
              </div>
              <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">{{ cuadroHonor[1].nombres }}</h4>
              <p class="text-xs text-slate-500 font-mono">C.I. {{ cuadroHonor[1].pes_cedula }} • {{ cuadroHonor[1].programa }}</p>

              <div class="pt-2 flex items-center justify-between">
                <div>
                  <span class="text-[11px] text-slate-400 block">IRA General:</span>
                  <span class="text-2xl font-black text-slate-700 dark:text-slate-300 font-mono">{{ cuadroHonor[1].pes_ira.toFixed(2) }}</span>
                </div>
                <Badge variant="outline" class="border-slate-400 font-semibold text-[11px]">
                  {{ cuadroHonor[1].tipo_nombre }}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <!-- 3er Lugar -->
          <Card
            v-if="cuadroHonor[2]"
            class="relative overflow-hidden border-orange-200 dark:border-orange-900/50 bg-gradient-to-br from-orange-50/40 via-white to-orange-100/30 dark:from-slate-950 dark:via-orange-950/20 dark:to-slate-900 shadow-sm"
          >
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-orange-400/20 flex items-center justify-center text-orange-700 text-xl font-black">
              3°
            </div>
            <CardContent class="p-5 space-y-2">
              <div class="flex items-center gap-2 text-orange-700 dark:text-orange-400 text-xs font-bold uppercase tracking-wider">
                <Medal class="h-4 w-4" />
                Puesto 3 • Promoción General
              </div>
              <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">{{ cuadroHonor[2].nombres }}</h4>
              <p class="text-xs text-slate-500 font-mono">C.I. {{ cuadroHonor[2].pes_cedula }} • {{ cuadroHonor[2].programa }}</p>

              <div class="pt-2 flex items-center justify-between">
                <div>
                  <span class="text-[11px] text-slate-400 block">IRA General:</span>
                  <span class="text-2xl font-black text-orange-600 dark:text-orange-400 font-mono">{{ cuadroHonor[2].pes_ira.toFixed(2) }}</span>
                </div>
                <Badge variant="outline" class="border-orange-400 text-orange-700 dark:text-orange-300 font-semibold text-[11px]">
                  {{ cuadroHonor[2].tipo_nombre }}
                </Badge>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- FILTROS Y CONTROLES -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-sm">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por cédula, nombre o folio..."
            class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-500 focus:outline-none"
          />
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <select
            v-model="filtroPrograma"
            class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
          >
            <option value="TODOS">Todos los Programas</option>
            <option value="PNF en Informática">PNF Informática</option>
            <option value="PNF en Administración">PNF Administración</option>
            <option value="PNF en Agroalimentación">PNF Agroalimentación</option>
          </select>

          <select
            v-model="filtroMencion"
            class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
          >
            <option value="TODOS">Todas las Menciones</option>
            <option value="S">Summa Cum Laude</option>
            <option value="M">Magna Cum Laude</option>
            <option value="C">Cum Laude</option>
            <option value="R">Regular</option>
          </select>
        </div>
      </div>

      <!-- TABLA DETALLADA DEL PADRÓN DE GRADUANDOS -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base font-bold flex items-center gap-2">
              <GraduationCap class="h-5 w-5 text-red-600" />
              Nómina de Graduandos Protocolizados
            </CardTitle>
            <Badge variant="outline" class="font-mono text-xs">
              {{ graduandosFiltrados.length }} Graduandos listados
            </Badge>
          </div>
        </CardHeader>

        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3 font-semibold text-center">Pos. Gral</th>
                  <th class="px-4 py-3 font-semibold text-center">Pos. PNF</th>
                  <th class="px-4 py-3 font-semibold">Graduando / Cédula</th>
                  <th class="px-4 py-3 font-semibold">Programa & Título</th>
                  <th class="px-4 py-3 font-semibold text-center">IRA Final</th>
                  <th class="px-4 py-3 font-semibold text-center">Mención Honorífica</th>
                  <th class="px-4 py-3 font-semibold text-center">Tomo / Folio</th>
                  <th class="px-4 py-3 font-semibold text-center">Estatus</th>
                  <th class="px-4 py-3 font-semibold text-center">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="g in graduandosFiltrados"
                  :key="g.pes_codigo"
                  class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30 transition-colors"
                >
                  <!-- Posición general en la promoción -->
                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex h-7 w-7 items-center justify-center rounded-full font-mono font-bold text-xs"
                      :class="{
                        'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': g.pes_numero_posicion_promocion === 1,
                        'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200': g.pes_numero_posicion_promocion === 2,
                        'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300': g.pes_numero_posicion_promocion === 3,
                        'bg-slate-100 text-slate-600 dark:bg-slate-900 dark:text-slate-400': g.pes_numero_posicion_promocion > 3,
                      }"
                    >
                      #{{ g.pes_numero_posicion_promocion }}
                    </span>
                  </td>

                  <!-- Posición en el programa -->
                  <td class="px-4 py-3 text-center font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                    {{ g.pes_numero_posicion_programa }}° PNF
                  </td>

                  <!-- Graduando -->
                  <td class="px-4 py-3">
                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ g.nombres }}</div>
                    <div class="text-xs text-slate-400 font-mono">C.I. {{ g.pes_cedula }} • {{ g.sede }}</div>
                  </td>

                  <!-- Programa y Título -->
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800 dark:text-slate-200 text-xs">{{ g.programa }}</div>
                    <div class="text-[11px] font-bold text-red-600 dark:text-red-400">{{ g.titulo }}</div>
                  </td>

                  <!-- IRA Final -->
                  <td class="px-4 py-3 text-center">
                    <span class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400">
                      {{ g.pes_ira.toFixed(2) }}
                    </span>
                  </td>

                  <!-- Mención Honorífica -->
                  <td class="px-4 py-3 text-center">
                    <Badge
                      v-if="g.pes_tipo_graduando === 'S'"
                      class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px]"
                    >
                      Summa Cum Laude
                    </Badge>
                    <Badge
                      v-else-if="g.pes_tipo_graduando === 'M'"
                      class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px]"
                    >
                      Magna Cum Laude
                    </Badge>
                    <Badge
                      v-else-if="g.pes_tipo_graduando === 'C'"
                      variant="outline"
                      class="border-indigo-400 text-indigo-700 dark:text-indigo-300 font-bold text-[10px]"
                    >
                      Cum Laude
                    </Badge>
                    <span v-else class="text-xs text-slate-400">Regular</span>
                  </td>

                  <!-- Tomo / Folio -->
                  <td class="px-4 py-3 text-center">
                    <div class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                      {{ g.pes_tomo }}
                    </div>
                    <div class="font-mono text-[11px] text-slate-400">
                      {{ g.pes_folio }}
                    </div>
                  </td>

                  <!-- Estatus -->
                  <td class="px-4 py-3 text-center">
                    <Badge :variant="g.pes_estatus === 'A' ? 'successCustom' : 'destructiveCustom'">
                      {{ g.pes_estatus === 'A' ? 'ACTIVO' : 'INACTIVO' }}
                    </Badge>
                  </td>

                  <!-- Acciones -->
                  <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <Button size="xs" variant="outline" class="h-7 text-xs" @click="abrirProtocolo(g)">
                        Tomo/Folio
                      </Button>
                      <Button
                        size="xs"
                        :variant="g.pes_estatus === 'A' ? 'outline' : 'outline'"
                        class="h-7 text-xs"
                        :class="g.pes_estatus === 'A' ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50'"
                        @click="abrirInactivar(g)"
                      >
                        {{ g.pes_estatus === 'A' ? 'Inactivar' : 'Activar' }}
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- MODAL ASIGNAR TOMO Y FOLIO -->
      <div v-if="modalProtocolo && graduandoSeleccionado" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                Protocolización de Grado
              </h3>
              <p class="text-xs text-slate-500">Asignar Tomo y Folio a {{ graduandoSeleccionado.nombres }}</p>
            </div>
            <button @click="modalProtocolo = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Tomo / Libro:
              </label>
              <input
                v-model="formProtocolo.pes_tomo"
                type="text"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Número de Folio:
                </label>
                <input
                  v-model="formProtocolo.pes_folio"
                  type="text"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Nº de Salida de Folio:
                </label>
                <input
                  v-model="formProtocolo.pes_salidas_folio"
                  type="text"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Fecha de Salida de Folio:
              </label>
              <input
                v-model="formProtocolo.pes_fecha_salida_folio"
                type="date"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalProtocolo = false">
              Cancelar
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarProtocolo">
              Guardar Protocolo
            </Button>
          </div>
        </div>
      </div>

      <!-- MODAL ACTIVAR / INACTIVAR CON MOTIVO -->
      <div v-if="modalInactivar && graduandoSeleccionado" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                {{ graduandoSeleccionado.pes_estatus === 'A' ? 'Inactivar Graduando' : 'Reactivar Graduando' }}
              </h3>
              <p class="text-xs text-slate-500">{{ graduandoSeleccionado.nombres }} (C.I. {{ graduandoSeleccionado.pes_cedula }})</p>
            </div>
            <button @click="modalInactivar = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <label class="block font-semibold text-slate-700 dark:text-slate-300">
              Motivo u Observación Institucional (requerido para auditoría DACE):
            </label>
            <textarea
              v-model="formInactivar.observacion"
              rows="3"
              placeholder="Indique la causa administrativa o académica..."
              class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
            ></textarea>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalInactivar = false">
              Cancelar
            </Button>
            <Button
              size="sm"
              :class="graduandoSeleccionado.pes_estatus === 'A' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
              @click="toggleEstatus"
            >
              {{ graduandoSeleccionado.pes_estatus === 'A' ? 'Confirmar Inactivación' : 'Confirmar Reactivación' }}
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
