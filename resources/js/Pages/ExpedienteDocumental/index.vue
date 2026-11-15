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
  FolderArchive,
  FileCheck2,
  FileX,
  FileClock,
  GraduationCap,
  Award,
  Search,
  CheckCircle2,
  AlertCircle,
  Plus,
  Upload,
  User,
  ShieldCheck,
  Building,
  Calendar,
  Layers,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Trámites y Expediente', href: route('expediente.documentos') },
  { title: 'Consignación Documental y Títulos', href: route('expediente.documentos') },
];

// Estudiantes disponibles para consulta de expediente
const estudiantes = [
  {
    per_codigo: 101,
    cedula: '30123456',
    nombre: 'Alejandro José González Pérez',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    solvencia_adm: true,
    solvencia_aca: true,
    solvencia_bib: true,
  },
  {
    per_codigo: 102,
    cedula: '28456789',
    nombre: 'Mariana Victoria Rivas Delgado',
    programa: 'PNF en Administración',
    sede: 'Sede Guanare',
    solvencia_adm: true,
    solvencia_aca: true,
    solvencia_bib: false, // Debe biblioteca
  },
  {
    per_codigo: 103,
    cedula: '29876543',
    nombre: 'Gabriel Antonio Herrera Castillo',
    programa: 'PNF en Informática',
    sede: 'Sede Central Acarigua',
    solvencia_adm: false, // Pendiente arancel
    solvencia_aca: true,
    solvencia_bib: true,
  },
];

const estudianteSeleccionado = ref(estudiantes[0]);

// Documentos consignados del estudiante (documento y documentos_consignados)
const documentosConsignados = ref([
  {
    dco_codigo: 1,
    doc_nombre: 'Fondo Negro del Título de Bachiller Autenticado',
    doc_tipo: 'Admisión',
    dco_estatus: 'C', // C = Consignado / Validado
    dco_fecha_consignacion: '2023-01-15',
    dco_observaciones: 'Copia autenticada por Zona Educativa de Portuguesa.',
  },
  {
    dco_codigo: 2,
    doc_nombre: 'Certificación de Calificaciones de Educación Media',
    doc_tipo: 'Admisión',
    dco_estatus: 'C',
    dco_fecha_consignacion: '2023-01-15',
    dco_observaciones: 'Promedio de egreso de bachiller: 18.20 pts.',
  },
  {
    dco_codigo: 3,
    doc_nombre: 'Partida de Nacimiento Legalizada',
    doc_tipo: 'Identificación',
    dco_estatus: 'C',
    dco_fecha_consignacion: '2023-01-15',
    dco_observaciones: 'Original y copia legible.',
  },
  {
    dco_codigo: 4,
    doc_nombre: 'Fotocopia Ampliada de la Cédula de Identidad',
    doc_tipo: 'Identificación',
    dco_estatus: 'C',
    dco_fecha_consignacion: '2023-01-15',
    dco_observaciones: 'Vigente hasta 2030.',
  },
  {
    dco_codigo: 5,
    doc_nombre: 'Certificado de Salud Integral Vigente',
    doc_tipo: 'Salud',
    dco_estatus: 'P', // P = Pendiente
    dco_fecha_consignacion: null,
    dco_observaciones: 'Pendiente entrega de constancia médica actualizada del año en curso.',
  },
  {
    dco_codigo: 6,
    doc_nombre: 'Planilla de Asignación OPSU / CNU',
    doc_tipo: 'Admisión',
    dco_estatus: 'C',
    dco_fecha_consignacion: '2023-01-15',
    dco_observaciones: 'Código de asignación verificado en sistema central.',
  },
]);

// Títulos universitarios previos registrados (persona_titulo_academico y grado_academico)
const titulosPrevios = ref([
  {
    pta_codigo: 1,
    pta_titulo: 'Técnico Superior Universitario en Informática',
    grado_nombre: 'Técnico Superior Universitario (TSU)',
    gac_nivel: 1,
    universidad: 'Universidad Politécnica Territorial de Portuguesa JJM',
    ano_egreso: '2025',
    es_principal: true,
  },
]);

// Cálculo de completitud de expediente
const porcentajeCompletitud = computed(() => {
  const total = documentosConsignados.value.length;
  const consignados = documentosConsignados.value.filter(d => d.dco_estatus === 'C').length;
  return total > 0 ? Math.round((consignados / total) * 100) : 0;
});

// Modal de Consignación
const modalConsignar = ref(false);
const docEditando = ref<any>(null);
const formConsignar = ref({
  estatus: 'C',
  fecha: new Date().toISOString().split('T')[0],
  observaciones: '',
});

const abrirConsignar = (doc: any) => {
  docEditando.value = doc;
  formConsignar.value = {
    estatus: doc.dco_estatus,
    fecha: doc.dco_fecha_consignacion || new Date().toISOString().split('T')[0],
    observaciones: doc.dco_observaciones || '',
  };
  modalConsignar.value = true;
};

const guardarConsignacion = () => {
  if (docEditando.value) {
    docEditando.value.dco_estatus = formConsignar.value.estatus;
    docEditando.value.dco_fecha_consignacion = formConsignar.value.fecha;
    docEditando.value.dco_observaciones = formConsignar.value.observaciones;
  }
  modalConsignar.value = false;
};

// Modal de Nuevo Título Previo
const modalTitulo = ref(false);
const formTitulo = ref({
  titulo: '',
  grado: 'Licenciatura / Ingeniería',
  universidad: '',
  ano_egreso: '2024',
  es_principal: false,
});

const guardarTitulo = () => {
  titulosPrevios.value.push({
    pta_codigo: Date.now(),
    pta_titulo: formTitulo.value.titulo,
    grado_nombre: formTitulo.value.grado,
    gac_nivel: 2,
    universidad: formTitulo.value.universidad,
    ano_egreso: formTitulo.value.ano_egreso,
    es_principal: formTitulo.value.es_principal,
  });
  modalTitulo.value = false;
};
</script>

<template>
  <Head title="Expediente Documental y Títulos Académicos - SOGAC" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-900/50 mb-2">
            <FolderArchive class="h-3.5 w-3.5" />
            Control Central de Recaudos y Expediente Académico
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Control de Expediente y Consignación Documental
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
            Verificación de recaudos físicos y digitales para admisión y grado, auditoría de solvencias institucionales y registro de títulos universitarios previos.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white gap-1.5" @click="modalTitulo = true">
            <Plus class="h-4 w-4" />
            Registrar Título Previo
          </Button>
        </div>
      </div>

      <!-- SELECTOR DE ALUMNO Y TARJETA DE SOLVENCIAS -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Ficha de la Persona -->
        <Card class="border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
          <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <User class="h-4 w-4 text-red-600" />
                Titular del Expediente
              </CardTitle>
              <Badge variant="outline" class="font-mono text-xs">C.I. {{ estudianteSeleccionado.cedula }}</Badge>
            </div>
          </CardHeader>
          <CardContent class="pt-4 space-y-3 text-xs">
            <div>
              <span class="text-slate-400 block">Estudiante:</span>
              <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ estudianteSeleccionado.nombre }}</span>
            </div>

            <div>
              <span class="text-slate-400 block">Programa:</span>
              <span class="font-medium text-slate-700 dark:text-slate-300">{{ estudianteSeleccionado.programa }} • {{ estudianteSeleccionado.sede }}</span>
            </div>

            <!-- Selector rápido -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
              <label class="block text-slate-400 mb-1">Consultar otro estudiante:</label>
              <select
                :value="estudianteSeleccionado.cedula"
                @change="(e: any) => {
                  const f = estudiantes.find(est => est.cedula === e.target.value);
                  if (f) estudianteSeleccionado = f;
                }"
                class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-red-500 focus:outline-none"
              >
                <option v-for="est in estudiantes" :key="est.cedula" :value="est.cedula">
                  {{ est.nombre }} ({{ est.cedula }})
                </option>
              </select>
            </div>
          </CardContent>
        </Card>

        <!-- Solvencias y Avance de Recaudos (2 col) -->
        <Card class="lg:col-span-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 flex flex-col justify-between">
          <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800 flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <ShieldCheck class="h-4 w-4 text-emerald-600" />
                Solvencias Institucionales
              </CardTitle>
              <CardDescription class="text-xs">Requisitos indispensables para emisión de títulos y actas</CardDescription>
            </div>
            <Badge :variant="porcentajeCompletitud === 100 ? 'successCustom' : 'outline'">
              {{ porcentajeCompletitud }}% Recaudos Completos
            </Badge>
          </CardHeader>

          <CardContent class="pt-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <!-- Solvencia Administrativa -->
              <div class="p-3 rounded-xl border flex items-center justify-between" :class="estudianteSeleccionado.solvencia_adm ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-red-50/50 border-red-200 dark:bg-red-950/20 dark:border-red-800'">
                <div>
                  <span class="text-xs font-bold block" :class="estudianteSeleccionado.solvencia_adm ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">
                    Solvencia Administrativa
                  </span>
                  <span class="text-[11px] text-slate-400">Aranceles y Caja DACE</span>
                </div>
                <Badge :variant="estudianteSeleccionado.solvencia_adm ? 'successCustom' : 'destructiveCustom'" class="text-[10px]">
                  {{ estudianteSeleccionado.solvencia_adm ? 'SOLVENTE' : 'PENDIENTE' }}
                </Badge>
              </div>

              <!-- Solvencia Académica -->
              <div class="p-3 rounded-xl border flex items-center justify-between" :class="estudianteSeleccionado.solvencia_aca ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-red-50/50 border-red-200 dark:bg-red-950/20 dark:border-red-800'">
                <div>
                  <span class="text-xs font-bold block" :class="estudianteSeleccionado.solvencia_aca ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">
                    Solvencia Académica
                  </span>
                  <span class="text-[11px] text-slate-400">Actas y Calificaciones</span>
                </div>
                <Badge :variant="estudianteSeleccionado.solvencia_aca ? 'successCustom' : 'destructiveCustom'" class="text-[10px]">
                  {{ estudianteSeleccionado.solvencia_aca ? 'SOLVENTE' : 'PENDIENTE' }}
                </Badge>
              </div>

              <!-- Solvencia Biblioteca -->
              <div class="p-3 rounded-xl border flex items-center justify-between" :class="estudianteSeleccionado.solvencia_bib ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800' : 'bg-red-50/50 border-red-200 dark:bg-red-950/20 dark:border-red-800'">
                <div>
                  <span class="text-xs font-bold block" :class="estudianteSeleccionado.solvencia_bib ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">
                    Solvencia Biblioteca
                  </span>
                  <span class="text-[11px] text-slate-400">Sin Préstamos Activos</span>
                </div>
                <Badge :variant="estudianteSeleccionado.solvencia_bib ? 'successCustom' : 'destructiveCustom'" class="text-[10px]">
                  {{ estudianteSeleccionado.solvencia_bib ? 'SOLVENTE' : 'MOROSO' }}
                </Badge>
              </div>
            </div>

            <!-- Barra de Avance de Expediente -->
            <div class="pt-2">
              <div class="flex justify-between text-xs mb-1">
                <span class="text-slate-500 font-medium">Completitud de Recaudos Físicos</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ documentosConsignados.filter(d => d.dco_estatus === 'C').length }} de {{ documentosConsignados.length }} aprobados</span>
              </div>
              <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-teal-600 rounded-full transition-all" :style="{ width: `${porcentajeCompletitud}%` }"></div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- CHECKLIST DE DOCUMENTOS CONSIGNADOS (TABLA) -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-between">
            <div>
              <CardTitle class="text-base font-bold flex items-center gap-2">
                <FileCheck2 class="h-5 w-5 text-teal-600" />
                Checklist de Documentos y Recaudos Consignados
              </CardTitle>
              <CardDescription class="text-xs">Estado de recepción, fechas de entrega y observaciones del archivo central</CardDescription>
            </div>
          </div>
        </CardHeader>

        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 dark:bg-slate-900/50 uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3 font-semibold">Documento / Recaudo</th>
                  <th class="px-4 py-3 font-semibold text-center">Tipo</th>
                  <th class="px-4 py-3 font-semibold text-center">Estado</th>
                  <th class="px-4 py-3 font-semibold text-center">Fecha Consignación</th>
                  <th class="px-4 py-3 font-semibold">Observaciones DACE</th>
                  <th class="px-4 py-3 font-semibold text-center">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="doc in documentosConsignados" :key="doc.dco_codigo" class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-3 font-bold text-slate-900 dark:text-slate-100">
                    {{ doc.doc_nombre }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-medium">
                      {{ doc.doc_tipo }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Badge :variant="doc.dco_estatus === 'C' ? 'successCustom' : (doc.dco_estatus === 'P' ? 'outline' : 'destructiveCustom')">
                      {{ doc.dco_estatus === 'C' ? 'CONSIGNADO' : (doc.dco_estatus === 'P' ? 'PENDIENTE' : 'RECHAZADO') }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-center font-mono text-slate-600 dark:text-slate-400">
                    {{ doc.dco_fecha_consignacion || '---' }}
                  </td>
                  <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                    {{ doc.dco_observaciones }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Button size="xs" variant="outline" class="h-7 text-xs" @click="abrirConsignar(doc)">
                      Actualizar
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- REGISTRO DE TÍTULOS ACADÉMICOS PREVIOS -->
      <Card class="border-slate-200 dark:border-slate-800">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800 flex flex-row items-center justify-between">
          <div>
            <CardTitle class="text-base font-bold flex items-center gap-2">
              <GraduationCap class="h-5 w-5 text-indigo-600" />
              Títulos Universitarios Previos Obtenidos
            </CardTitle>
            <CardDescription class="text-xs">Registro de grados académicos previos (TSU, Licenciatura, Postgrados)</CardDescription>
          </div>
        </CardHeader>

        <CardContent class="p-4 space-y-3">
          <div v-if="titulosPrevios.length === 0" class="text-center py-6 text-xs text-slate-400">
            No registra títulos universitarios previos.
          </div>

          <div
            v-for="tit in titulosPrevios"
            :key="tit.pta_codigo"
            class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
          >
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ tit.pta_titulo }}</span>
                <Badge variant="outline" class="text-[10px]">{{ tit.grado_nombre }}</Badge>
                <Badge v-if="tit.es_principal" variant="successCustom" class="text-[10px]">Título Principal</Badge>
              </div>
              <p class="text-slate-500 mt-1">Institución: <strong>{{ tit.universidad }}</strong> • Año de Egreso: <strong class="font-mono text-red-600">{{ tit.ano_egreso }}</strong></p>
            </div>

            <span class="text-emerald-600 font-semibold flex items-center gap-1">
              <CheckCircle2 class="h-4 w-4" />
              Acreditado en DACE
            </span>
          </div>
        </CardContent>
      </Card>

      <!-- MODAL CONSIGNAR / REVISAR DOCUMENTO -->
      <div v-if="modalConsignar && docEditando" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Validar Recaudo Documental</h3>
              <p class="text-xs text-slate-500">{{ docEditando.doc_nombre }}</p>
            </div>
            <button @click="modalConsignar = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Estatus de Consignación:
              </label>
              <select
                v-model="formConsignar.estatus"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option value="C">Consignado y Validado</option>
                <option value="P">Pendiente por Entrega</option>
                <option value="R">Rechazado / Observado</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Fecha de Recepción:
              </label>
              <input
                v-model="formConsignar.fecha"
                type="date"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Observaciones del Archivo DACE:
              </label>
              <textarea
                v-model="formConsignar.observaciones"
                rows="3"
                placeholder="Número de folio físico, condiciones de la copia o causa de rechazo..."
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              ></textarea>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalConsignar = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarConsignacion">
              Guardar Estado
            </Button>
          </div>
        </div>
      </div>

      <!-- MODAL NUEVO TÍTULO UNIVERSITARIO PREVIO -->
      <div v-if="modalTitulo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-in fade-in">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Registrar Título Universitario Previo</h3>
              <p class="text-xs text-slate-500">Registro de títulos de educación superior previos</p>
            </div>
            <button @click="modalTitulo = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Nombre del Título Obtenido:
              </label>
              <input
                v-model="formTitulo.titulo"
                type="text"
                placeholder="Ej: Licenciado en Educación Mención Matemáticas"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Nivel Académico:
                </label>
                <select
                  v-model="formTitulo.grado"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                >
                  <option value="T.S.U.">Técnico Superior Universitario</option>
                  <option value="Licenciatura / Ingeniería">Licenciatura / Ingeniería</option>
                  <option value="Especialización">Especialización</option>
                  <option value="Maestría">Maestría</option>
                  <option value="Doctorado">Doctorado</option>
                </select>
              </div>

              <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Año de Egreso:
                </label>
                <input
                  v-model="formTitulo.ano_egreso"
                  type="text"
                  placeholder="Ej: 2023"
                  class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Universidad o Instituto Universitario:
              </label>
              <input
                v-model="formTitulo.universidad"
                type="text"
                placeholder="Ej: Universidad Central de Venezuela"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <Button size="sm" variant="outline" @click="modalTitulo = false">Cancelar</Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="guardarTitulo">
              Guardar Título
            </Button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
