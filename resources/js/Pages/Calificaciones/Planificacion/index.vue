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
  BookOpen,
  Calendar,
  CheckCircle2,
  AlertCircle,
  Plus,
  Pencil,
  Trash2,
  FileSpreadsheet,
  ArrowRight,
  Sparkles,
  Layers,
} from 'lucide-vue-next';
import { usePlanificacionDocente } from '@/composables/usePlanificacionDocente';

const {
  seccionesDocente,
  seccionSeleccionada,
  tiposEvaluacion,
  mostrarInputOtraMetodologia,
  nuevaMetodologiaInput,
  abrirOtraMetodologia,
  agregarOtraMetodologia,
  matrizEvaluacion,
  aplicarPlantilla4Cortes,
  aplicarPlantilla5Cortes,
  totalPorcentaje,
  mostrarModal,
  modoEdicion,
  formCorte,
  abrirNuevoCorte,
  editarCorte,
  guardarCorte,
  eliminarCorte,
} = usePlanificacionDocente();
</script>

<template>
  <Head title="Plan de Evaluación y Matriz de Ponderación" />

  <AppLayout :breadcrumbs="[
    { title: 'Calificaciones', subtitle: 'Plan de Evaluación', href: route('calificaciones.planificacion') }
  ]">
    <div class="w-full p-4 sm:p-6 space-y-6">
      
      <!-- ENCABEZADO Y SELECTOR DE SECCIÓN -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300 mb-1.5">
            <Layers class="h-3.5 w-3.5" />
            Planificación y Matriz de Ponderaciones
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Planificación y Matriz de Evaluación Docente
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Definición de cortes evaluativos, tipos de instrumentos y ponderaciones porcentuales por sección.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <div class="hidden sm:flex items-center bg-slate-100 dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800 text-xs mr-1">
            <span class="text-[11px] text-slate-500 font-semibold px-2">Plantilla:</span>
            <button
              type="button"
              @click="aplicarPlantilla4Cortes"
              class="px-2.5 py-1 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 hover:shadow-xs transition-all font-medium"
            >
              4 Cortes (25%)
            </button>
            <button
              type="button"
              @click="aplicarPlantilla5Cortes"
              class="px-2.5 py-1 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 hover:shadow-xs transition-all font-medium"
            >
              5 Cortes (20%)
            </button>
          </div>

          <Button variant="outline" size="sm" as-child>
            <Link :href="route('calificaciones.carga')">
              <FileSpreadsheet class="h-4 w-4 mr-1.5 text-emerald-600" />
              Ir a Cargar Notas
            </Link>
          </Button>
          <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" @click="abrirNuevoCorte">
            <Plus class="h-4 w-4 mr-1.5" />
            Agregar Corte Evaluativo
          </Button>
        </div>
      </div>

      <!-- SELECTOR DE SECCIONES ASIGNADAS AL DOCENTE -->
      <div class="grid gap-3 sm:grid-cols-3">
        <div
          v-for="sec in seccionesDocente"
          :key="sec.id"
          @click="seccionSeleccionada = sec"
          class="cursor-pointer rounded-xl border p-4 transition-all duration-200"
          :class="seccionSeleccionada.id === sec.id
            ? 'border-red-600 bg-red-50/50 shadow-sm ring-1 ring-red-600/30 dark:bg-red-950/20 dark:border-red-500'
            : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950'"
        >
          <div class="flex items-center justify-between">
            <span class="font-mono text-xs font-bold text-red-600 dark:text-red-400 px-2 py-0.5 rounded bg-red-100/70 dark:bg-red-950">
              {{ sec.codigo }}
            </span>
            <span class="text-xs text-slate-500">{{ sec.estudiantes }} estudiantes</span>
          </div>
          <h3 class="font-semibold text-sm text-slate-900 dark:text-slate-100 mt-2">{{ sec.nombre }}</h3>
          <p class="text-xs text-slate-500 mt-0.5">{{ sec.trayecto }} • {{ sec.sede }}</p>
        </div>
      </div>

      <!-- ESTADO DE PONDERACIÓN (BARRA 100%) -->
      <Card class="border-slate-200 dark:border-slate-800 shadow-sm">
        <CardContent class="p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ponderación Total Planificada</span>
              <div class="flex items-baseline gap-2 mt-0.5">
                <span class="text-3xl font-extrabold" :class="totalPorcentaje === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                  {{ totalPorcentaje }}%
                </span>
                <span class="text-xs text-slate-500">de 100% reglamentario</span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Badge v-if="totalPorcentaje === 100" variant="successCustom" class="gap-1 py-1 px-3">
                <CheckCircle2 class="h-3.5 w-3.5" />
                Matriz Completa y Balanceada (100%)
              </Badge>
              <Badge v-else variant="outline" class="gap-1 py-1 px-3 text-amber-700 border-amber-300 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-300">
                <AlertCircle class="h-3.5 w-3.5" />
                Falta {{ 100 - totalPorcentaje }}% para completar
              </Badge>
            </div>
          </div>

          <!-- Barra de progreso -->
          <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div
              class="h-full transition-all duration-500 rounded-full"
              :class="totalPorcentaje === 100 ? 'bg-emerald-500' : 'bg-amber-500'"
              :style="{ width: Math.min(100, totalPorcentaje) + '%' }"
            ></div>
          </div>
        </CardContent>
      </Card>

      <!-- TABLA DE LA MATRIZ DE EVALUACIÓN (matriz_evaluacion) -->
      <Card class="border-slate-200 dark:border-slate-800 shadow-sm">
        <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-between">
            <div>
              <CardTitle class="text-base font-bold">Cortes y Estrategias Evaluativas (mev_codigo)</CardTitle>
              <CardDescription class="text-xs">
                Sección: {{ seccionSeleccionada.codigo }} — {{ seccionSeleccionada.nombre }} ({{ matrizEvaluacion.length }} evaluaciones registradas)
              </CardDescription>
            </div>
            <Button size="xs" variant="outline" class="text-xs" as-child>
              <Link :href="route('calificaciones.carga')">
                <span>Cargar Notas</span>
                <ArrowRight class="h-3.5 w-3.5 ml-1" />
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-900/60 dark:text-slate-300">
                <tr>
                  <th class="px-4 py-3 font-semibold text-center w-16">Corte</th>
                  <th class="px-4 py-3 font-semibold">Tipo de Evaluación (tipo_evaluacion)</th>
                  <th class="px-4 py-3 font-semibold">Descripción / Contenido</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Peso (%)</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Sem. Plan.</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Sem. Ejec.</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Estatus</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="(item, idx) in matrizEvaluacion" :key="item.mev_codigo" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                  <td class="px-4 py-3 text-center font-mono font-bold text-slate-500">
                    #{{ idx + 1 }}
                  </td>
                  <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                    {{ item.tipo_nombre }}
                  </td>
                  <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate">
                    {{ item.descripcion || 'Sin descripción detallada' }}
                  </td>
                  <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-slate-100 tabular-nums">
                    {{ item.mev_porcentaje }}%
                  </td>
                  <td class="px-4 py-3 text-center text-xs text-slate-600 dark:text-slate-300 tabular-nums">
                    Semana {{ item.mev_semana_planificada }}
                  </td>
                  <td class="px-4 py-3 text-center text-xs tabular-nums">
                    <span v-if="item.mev_semana_ejecutada" class="text-emerald-600 dark:text-emerald-400 font-semibold">
                      Semana {{ item.mev_semana_ejecutada }}
                    </span>
                    <span v-else class="text-slate-400 italic">Pendiente</span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                      :class="{
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400': item.estatus === 'EJECUTADA',
                        'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400': item.estatus === 'EN_CURSO',
                        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300': item.estatus === 'PLANIFICADA',
                      }"
                    >
                      {{ item.estatus }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <div class="flex justify-center gap-1">
                      <Button size="xs" variant="ghost" @click="editarCorte(item)" class="h-7 w-7 p-0 text-amber-600 hover:bg-amber-50">
                        <Pencil class="h-3.5 w-3.5" />
                      </Button>
                      <Button size="xs" variant="ghost" @click="eliminarCorte(item.mev_codigo)" class="h-7 w-7 p-0 text-red-600 hover:bg-red-50">
                        <Trash2 class="h-3.5 w-3.5" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- MODAL PARA AGREGAR O EDITAR CORTE -->
      <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 animate-in fade-in duration-200">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-950 space-y-4">
          <div class="border-b pb-3 border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">
              {{ modoEdicion ? 'Modificar Corte Evaluativo' : 'Agregar Corte a la Matriz' }}
            </h3>
            <span class="text-xs font-mono text-slate-500">{{ seccionSeleccionada.codigo }}</span>
          </div>

          <form @submit.prevent="guardarCorte" class="space-y-4 text-sm">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                  Tipo de Evaluación / Metodología<span class="text-red-500">*</span>
                </label>
                <button
                  type="button"
                  @click="abrirOtraMetodologia"
                  class="text-[11px] font-bold text-red-600 dark:text-red-400 hover:underline flex items-center gap-1"
                >
                  <Sparkles class="h-3 w-3" />
                  Botón "Otro" (Personalizar)
                </button>
              </div>

              <!-- Input desplegable para escribir otra metodología -->
              <div v-if="mostrarInputOtraMetodologia" class="p-2.5 mb-2 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 space-y-2">
                <span class="text-[11px] font-medium text-amber-800 dark:text-amber-300 block">
                  Escriba la nueva metodología de evaluación:
                </span>
                <div class="flex gap-2">
                  <input
                    v-model="nuevaMetodologiaInput"
                    type="text"
                    placeholder="Ej. Seminario Investigativo, Mesa Redonda..."
                    class="input-base flex-1 h-8 text-xs"
                    @keyup.enter="agregarOtraMetodologia"
                  />
                  <Button type="button" size="sm" class="h-8 text-xs bg-amber-600 hover:bg-amber-700 text-white" @click="agregarOtraMetodologia">
                    Agregar
                  </Button>
                </div>
              </div>

              <select v-model="formCorte.mev_cod_tipo_evaluacion" class="input-base w-full cursor-pointer" required>
                <option v-for="t in tiposEvaluacion" :key="t.tev_codigo" :value="t.tev_codigo">{{ t.tev_nombre }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Descripción / Contenidos a Evaluar<span class="text-red-500">*</span>
              </label>
              <input v-model="formCorte.descripcion" type="text" placeholder="Ej. Prueba Parcial Teórico-Práctica Unidad II" class="input-base w-full" required />
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Porcentaje (%)<span class="text-red-500">*</span>
                </label>
                <input v-model="formCorte.mev_porcentaje" type="number" min="1" max="100" class="input-base w-full font-bold" required />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Sem. Planificada<span class="text-red-500">*</span>
                </label>
                <input v-model="formCorte.mev_semana_planificada" type="number" min="1" max="18" class="input-base w-full" required />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Sem. Ejecutada
                </label>
                <input v-model="formCorte.mev_semana_ejecutada" type="number" min="1" max="18" placeholder="Opcional" class="input-base w-full" />
              </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
              <Button type="button" variant="outline" size="sm" @click="mostrarModal = false">
                Cancelar
              </Button>
              <Button type="submit" size="sm" class="bg-red-600 hover:bg-red-700 text-white">
                {{ modoEdicion ? 'Actualizar Matriz' : 'Guardar Corte' }}
              </Button>
            </div>
          </form>
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
