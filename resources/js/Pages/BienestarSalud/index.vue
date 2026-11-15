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
  HeartPulse,
  Activity,
  Accessibility,
  Plus,
  Search,
  Pencil,
  Trash2,
  AlertCircle,
  CheckCircle2,
  FileText,
  User,
  ShieldAlert,
  Stethoscope,
  Clock,
  Sparkles,
  Info,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Bienestar y Salud', href: route('bienestar.index') },
  { title: 'Salud Integral y Discapacidad', href: route('bienestar.index') },
];

// Estudiantes disponibles para consulta de salud
const estudiantesLista = [
  {
    per_codigo: 101,
    cedula: '30123456',
    nombre: 'Alejandro José González Pérez',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    telefono: '0414-5551234',
    tipoSangre: 'O+',
  },
  {
    per_codigo: 102,
    cedula: '28456789',
    nombre: 'Mariana Victoria Rivas Delgado',
    programa: 'PNF en Administración',
    sede: 'Sede Guanare',
    telefono: '0424-7778899',
    tipoSangre: 'A+',
  },
  {
    per_codigo: 103,
    cedula: '29876543',
    nombre: 'Gabriel Antonio Herrera Castillo',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    telefono: '0412-9993322',
    tipoSangre: 'B+',
  },
];

const estudianteSeleccionado = ref(estudiantesLista[0]);
const tabActiva = ref<'afecciones' | 'discapacidades'>('afecciones');

// Catálogo de afecciones médicas (afeccion_medica y tipo_afeccion)
const catalogoAfecciones = [
  { ame_codigo: 1, ame_nombre: 'Asma Bronquial Extrínseca', ame_codigo_cie10: 'J45.0', taf_nombre: 'Respiratoria' },
  { ame_codigo: 2, ame_nombre: 'Diabetes Mellitus Tipo 1', ame_codigo_cie10: 'E10.9', taf_nombre: 'Endocrina' },
  { ame_codigo: 3, ame_nombre: 'Hipertensión Arterial Primaria', ame_codigo_cie10: 'I10', taf_nombre: 'Cardiovascular' },
  { ame_codigo: 4, ame_nombre: 'Epilepsia Idiopática Generalizada', ame_codigo_cie10: 'G40.0', taf_nombre: 'Neurológica' },
  { ame_codigo: 5, ame_nombre: 'Miopía Severa / Astigmatismo', ame_codigo_cie10: 'H52.1', taf_nombre: 'Oftalmológica' },
  { ame_codigo: 6, ame_nombre: 'Alergia Severa a Medicamentos (Penicilina)', ame_codigo_cie10: 'T88.7', taf_nombre: 'Inmunológica' },
];

// Catálogo de discapacidades (discapacidad)
const catalogoDiscapacidades = [
  { dis_codigo: 1, dis_nombre: 'Discapacidad Visual (Baja Visión)', dis_tipo: 'Sensorial' },
  { dis_codigo: 2, dis_nombre: 'Discapacidad Auditiva (Hipoacusia)', dis_tipo: 'Sensorial' },
  { dis_codigo: 3, dis_nombre: 'Discapacidad Motriz Miembro Inferior', dis_tipo: 'Física' },
  { dis_codigo: 4, dis_nombre: 'Trastorno del Espectro Autista (TEA Leve)', dis_tipo: 'Neurodesarrollo' },
  { dis_codigo: 5, dis_nombre: 'Movilidad Reducida Permanente', dis_tipo: 'Física' },
];

// Registros de afecciones de la persona (persona_afeccion_medica)
const afeccionesPersona = ref([
  {
    pam_codigo: 1,
    pam_per_codigo: 101,
    ame_nombre: 'Asma Bronquial Extrínseca',
    ame_codigo_cie10: 'J45.0',
    taf_nombre: 'Respiratoria',
    pam_fecha_diagnostico: '2020-04-12',
    pam_fecha_registro: '2023-02-15',
    pam_severidad: 'Moderada',
    pam_es_cronica: true,
    pam_tratamiento_permanente: 'Inhalador Salbutamol SOS + Budesonida 200mcg',
    pam_adaptaciones_requeridas: 'Evitar espacios con polvillo de tiza o humedad; permiso para portar inhalador en evaluaciones.',
    pam_observaciones: 'Evaluado por neumonología en consulta médica universitaria.',
  },
  {
    pam_codigo: 2,
    pam_per_codigo: 101,
    ame_nombre: 'Miopía Severa / Astigmatismo',
    ame_codigo_cie10: 'H52.1',
    taf_nombre: 'Oftalmológica',
    pam_fecha_diagnostico: '2019-08-20',
    pam_fecha_registro: '2023-02-15',
    pam_severidad: 'Leve',
    pam_es_cronica: true,
    pam_tratamiento_permanente: 'Lentes correctivos formulados de uso diario',
    pam_adaptaciones_requeridas: 'Ubicación preferencial en primeras filas del aula y monitores de alto contraste.',
    pam_observaciones: 'Control oftalmológico anual regular.',
  },
]);

// Registros de discapacidad de la persona (persona_discapacidad)
const discapacidadesPersona = ref([
  {
    pdi_codigo: 1,
    pdi_per_codigo: 101,
    dis_nombre: 'Discapacidad Visual (Baja Visión)',
    dis_tipo: 'Sensorial',
    pdi_fecha_diagnostico: '2019-08-20',
    pdi_fecha_registro: '2023-02-15',
    pdi_grado_afectacion: 'Leve (30%)',
    pdi_observaciones: 'Presenta carnet CONAPDIS Nº V-094821.',
  },
]);

// Modales
const modalAfeccion = ref(false);
const modalDiscapacidad = ref(false);

const formAfeccion = ref({
  ame_codigo: 1,
  pam_fecha_diagnostico: '',
  pam_severidad: 'Leve',
  pam_es_cronica: true,
  pam_tratamiento_permanente: '',
  pam_adaptaciones_requeridas: '',
  pam_observaciones: '',
});

const formDiscapacidad = ref({
  dis_codigo: 1,
  pdi_fecha_diagnostico: '',
  pdi_grado_afectacion: 'Leve',
  pdi_observaciones: '',
});

const abrirNuevaAfeccion = () => {
  formAfeccion.value = {
    ame_codigo: catalogoAfecciones[0].ame_codigo,
    pam_fecha_diagnostico: new Date().toISOString().split('T')[0],
    pam_severidad: 'Leve',
    pam_es_cronica: true,
    pam_tratamiento_permanente: '',
    pam_adaptaciones_requeridas: '',
    pam_observaciones: '',
  };
  modalAfeccion.value = true;
};

const guardarAfeccion = () => {
  const cat = catalogoAfecciones.find(c => c.ame_codigo === Number(formAfeccion.value.ame_codigo));
  afeccionesPersona.value.push({
    pam_codigo: Date.now(),
    pam_per_codigo: estudianteSeleccionado.value.per_codigo,
    ame_nombre: cat?.ame_nombre || 'Afección Médica',
    ame_codigo_cie10: cat?.ame_codigo_cie10 || 'N/A',
    taf_nombre: cat?.taf_nombre || 'Clínica',
    pam_fecha_diagnostico: formAfeccion.value.pam_fecha_diagnostico,
    pam_fecha_registro: new Date().toISOString().split('T')[0],
    pam_severidad: formAfeccion.value.pam_severidad,
    pam_es_cronica: formAfeccion.value.pam_es_cronica,
    pam_tratamiento_permanente: formAfeccion.value.pam_tratamiento_permanente,
    pam_adaptaciones_requeridas: formAfeccion.value.pam_adaptaciones_requeridas,
    pam_observaciones: formAfeccion.value.pam_observaciones,
  });
  modalAfeccion.value = false;
};

const abrirNuevaDiscapacidad = () => {
  formDiscapacidad.value = {
    dis_codigo: catalogoDiscapacidades[0].dis_codigo,
    pdi_fecha_diagnostico: new Date().toISOString().split('T')[0],
    pdi_grado_afectacion: 'Leve',
    pdi_observaciones: '',
  };
  modalDiscapacidad.value = true;
};

const guardarDiscapacidad = () => {
  const cat = catalogoDiscapacidades.find(c => c.dis_codigo === Number(formDiscapacidad.value.dis_codigo));
  discapacidadesPersona.value.push({
    pdi_codigo: Date.now(),
    pdi_per_codigo: estudianteSeleccionado.value.per_codigo,
    dis_nombre: cat?.dis_nombre || 'Discapacidad',
    dis_tipo: cat?.dis_tipo || 'General',
    pdi_fecha_diagnostico: formDiscapacidad.value.pdi_fecha_diagnostico,
    pdi_fecha_registro: new Date().toISOString().split('T')[0],
    pdi_grado_afectacion: formDiscapacidad.value.pdi_grado_afectacion,
    pdi_observaciones: formDiscapacidad.value.pdi_observaciones,
  });
  modalDiscapacidad.value = false;
};
</script>

<template>
  <Head title="Bienestar Estudiantil y Salud Integral - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- HEADER DE LA PÁGINA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50 mb-2">
            <HeartPulse class="h-3.5 w-3.5" />
            Expediente de Salud y Accesibilidad Integral
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Bienestar Estudiantil: Salud y Discapacidad
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Expediente de salud integral, control de condiciones médicas crónicas bajo estándares CIE-10, adaptaciones curriculares en aula y seguimiento de discapacidades (CONAPDIS).
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" variant="outline" class="gap-1.5" @click="abrirNuevaDiscapacidad">
            <Accessibility class="h-4 w-4 text-emerald-600" />
            + Registrar Discapacidad
          </Button>

          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" @click="abrirNuevaAfeccion">
            <Plus class="h-4 w-4" />
            + Nueva Afección Médica
          </Button>
        </div>
      </div>

      <!-- SELECTOR DE ESTUDIANTE / PERSONA -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardContent class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-xl bg-red-100 dark:bg-red-950/50 text-red-600 flex items-center justify-center font-bold text-lg">
              <User class="h-6 w-6" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ estudianteSeleccionado.nombre }}</h3>
                <Badge variant="outline" class="font-mono text-xs">C.I. {{ estudianteSeleccionado.cedula }}</Badge>
              </div>
              <p class="text-xs text-slate-500">{{ estudianteSeleccionado.programa }} • {{ estudianteSeleccionado.sede }} • Grupo Sanguíneo: <strong class="text-red-600">{{ estudianteSeleccionado.tipoSangre }}</strong></p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-medium">Cambiar Estudiante:</span>
            <select
              :value="estudianteSeleccionado.cedula"
              @change="(e: any) => {
                const found = estudiantesLista.find(est => est.cedula === e.target.value);
                if (found) estudianteSeleccionado = found;
              }"
              class="text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1.5 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
            >
              <option v-for="est in estudiantesLista" :key="est.cedula" :value="est.cedula">
                {{ est.nombre }} ({{ est.cedula }})
              </option>
            </select>
          </div>
        </CardContent>
      </Card>

      <!-- KPI METRICS -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Condiciones Médicas</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ afeccionesPersona.length }}</div>
              <span class="text-[11px] text-emerald-600 font-medium">Registros validados CIE-10</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 flex items-center justify-center">
              <Activity class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Discapacidades Registradas</span>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ discapacidadesPersona.length }}</div>
              <span class="text-[11px] text-slate-400">Certificación CONAPDIS</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
              <Accessibility class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Adaptaciones Curriculares</span>
              <div class="text-2xl font-bold text-amber-600 mt-1">2 Activas</div>
              <span class="text-[11px] text-slate-400">Notificadas a docentes de aula</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
              <Sparkles class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-slate-200 dark:border-slate-800">
          <CardContent class="p-4 flex items-center justify-between">
            <div>
              <span class="text-xs text-slate-500 font-medium">Tratamientos Permanentes</span>
              <div class="text-2xl font-bold text-indigo-600 mt-1">1 En curso</div>
              <span class="text-[11px] text-slate-400">Seguimiento por Bienestar</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center">
              <Stethoscope class="h-5 w-5" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TABS DE NAVEGACIÓN ENTRE AFECCIONES Y DISCAPACIDADES -->
      <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800">
        <button
          @click="tabActiva = 'afecciones'"
          class="pb-3 text-xs sm:text-sm font-semibold border-b-2 transition-all flex items-center gap-2"
          :class="tabActiva === 'afecciones' 
            ? 'border-red-600 text-red-600 dark:text-red-400' 
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Activity class="h-4 w-4" />
          Afecciones Médicas y Patologías Clínicas
        </button>

        <button
          @click="tabActiva = 'discapacidades'"
          class="pb-3 text-xs sm:text-sm font-semibold border-b-2 transition-all flex items-center gap-2"
          :class="tabActiva === 'discapacidades' 
            ? 'border-red-600 text-red-600 dark:text-red-400' 
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
        >
          <Accessibility class="h-4 w-4" />
          Registro y Certificación de Discapacidades
        </button>
      </div>

      <!-- SECCIÓN TAB 1: AFECCIONES MÉDICAS -->
      <div v-if="tabActiva === 'afecciones'" class="space-y-4">
        <div
          v-for="item in afeccionesPersona"
          :key="item.pam_codigo"
          class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-5 shadow-sm space-y-3"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
            <div class="flex items-center gap-3">
              <span class="px-2.5 py-1 rounded-md bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 font-mono text-xs font-bold">
                CIE-10: {{ item.ame_codigo_cie10 }}
              </span>
              <div>
                <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ item.ame_nombre }}</h4>
                <span class="text-xs text-slate-400">Área: {{ item.taf_nombre }} • Diagnóstico: {{ item.pam_fecha_diagnostico }}</span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Badge :variant="item.pam_severidad === 'Severa' ? 'destructiveCustom' : 'outline'">
                Severidad: {{ item.pam_severidad }}
              </Badge>
              <Badge :variant="item.pam_es_cronica ? 'secondary' : 'outline'">
                {{ item.pam_es_cronica ? 'Condición Crónica' : 'Aguda' }}
              </Badge>
            </div>
          </div>

          <!-- DETALLES DE TRATAMIENTO Y ADAPTACIONES -->
          <div class="grid gap-3 sm:grid-cols-2 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
              <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-1">
                <Stethoscope class="h-3.5 w-3.5 text-red-600" />
                Tratamiento Permanente:
              </span>
              <p class="text-slate-600 dark:text-slate-400">{{ item.pam_tratamiento_permanente }}</p>
            </div>

            <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/40">
              <span class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5 mb-1">
                <Sparkles class="h-3.5 w-3.5 text-amber-600" />
                Adaptaciones Curriculares / Aula:
              </span>
              <p class="text-slate-700 dark:text-slate-300">{{ item.pam_adaptaciones_requeridas }}</p>
            </div>
          </div>

          <div class="text-xs text-slate-500 pt-1">
            <strong>Observaciones DACE / Bienestar:</strong> {{ item.pam_observaciones }}
          </div>
        </div>
      </div>

      <!-- SECCIÓN TAB 2: DISCAPACIDADES -->
      <div v-if="tabActiva === 'discapacidades'" class="space-y-4">
        <div
          v-for="item in discapacidadesPersona"
          :key="item.pdi_codigo"
          class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-5 shadow-sm space-y-3"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-3">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center justify-center font-bold">
                <Accessibility class="h-5 w-5" />
              </div>
              <div>
                <h4 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ item.dis_nombre }}</h4>
                <span class="text-xs text-slate-400">Tipo: {{ item.dis_tipo }} • Fecha de Diagnóstico: {{ item.pdi_fecha_diagnostico }}</span>
              </div>
            </div>

            <Badge variant="successCustom" class="text-xs">
              Grado: {{ item.pdi_grado_afectacion }}
            </Badge>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-xs">
            <span class="font-bold text-slate-700 dark:text-slate-300 block mb-1">
              Observaciones de Afectación Funcional:
            </span>
            <p class="text-slate-600 dark:text-slate-400">{{ item.pdi_observaciones }}</p>
          </div>
        </div>
      </div>

      <!-- MODAL NUEVA AFECCIÓN MÉDICA -->
      <div v-if="modalAfeccion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Registrar Afección Médica</h3>
              <p class="text-xs text-slate-500">Para: {{ estudianteSeleccionado.nombre }}</p>
            </div>
            <button @click="modalAfeccion = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Patología Médica / Clasificación CIE-10:
              </label>
              <select
                v-model="formAfeccion.ame_codigo"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option v-for="cat in catalogoAfecciones" :key="cat.ame_codigo" :value="cat.ame_codigo">
                  {{ cat.ame_nombre }} (CIE-10: {{ cat.ame_codigo_cie10 }})
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Nivel de Severidad:
                </label>
                <select
                  v-model="formAfeccion.pam_severidad"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="Leve">Leve</option>
                  <option value="Moderada">Moderada</option>
                  <option value="Severa">Severa</option>
                </select>
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Fecha de Diagnóstico Clínico:
                </label>
                <input
                  v-model="formAfeccion.pam_fecha_diagnostico"
                  type="date"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Tratamiento y Medicación Permanente:
              </label>
              <input
                v-model="formAfeccion.pam_tratamiento_permanente"
                type="text"
                placeholder="Ej: Fármacos, dosis, horarios requeridos..."
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Adaptaciones Curriculares en Aula:
              </label>
              <textarea
                v-model="formAfeccion.pam_adaptaciones_requeridas"
                rows="2"
                placeholder="Ej: Acceso por rampa, tolerancia en hidratación, evaluaciones en primera fila..."
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              ></textarea>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalAfeccion = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarAfeccion">
              Guardar Afección
            </Button>
          </div>
        </div>
      </div>

      <!-- MODAL NUEVA DISCAPACIDAD -->
      <div v-if="modalDiscapacidad" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Registrar Discapacidad</h3>
              <p class="text-xs text-slate-500">Para: {{ estudianteSeleccionado.nombre }}</p>
            </div>
            <button @click="modalDiscapacidad = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Tipo de Discapacidad:
              </label>
              <select
                v-model="formDiscapacidad.dis_codigo"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option v-for="cat in catalogoDiscapacidades" :key="cat.dis_codigo" :value="cat.dis_codigo">
                  {{ cat.dis_nombre }} ({{ cat.dis_tipo }})
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Grado de Afectación:
                </label>
                <select
                  v-model="formDiscapacidad.pdi_grado_afectacion"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="Leve">Leve</option>
                  <option value="Moderado">Moderado</option>
                  <option value="Severo">Severo</option>
                </select>
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Fecha de Diagnóstico Clínico:
                </label>
                <input
                  v-model="formDiscapacidad.pdi_fecha_diagnostico"
                  type="date"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Observaciones y Certificado CONAPDIS:
              </label>
              <textarea
                v-model="formDiscapacidad.pdi_observaciones"
                rows="3"
                placeholder="Número de carnet de discapacidad, requerimientos de accesibilidad..."
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              ></textarea>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalDiscapacidad = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarDiscapacidad">
              Guardar Discapacidad
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
