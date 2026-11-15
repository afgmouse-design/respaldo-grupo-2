/**
 * Composable: usePlanificacionDocente
 * Propósito: Encapsula y desacopla la lógica de negocio y estado reactivo para la
 * planificación académica y matriz de evaluación docente (Módulo: Calificaciones / Planificación).
 *
 * Estándar SOGAC:
 * - Desacopla controladores y vistas SPA manteniendo componentes ligeros y declarativos.
 * - Validación de ponderaciones (suma hasta 100%).
 * - Gestión de plantillas estándar (4 y 5 cortes) y métodos de evaluación.
 */

import { ref, computed } from 'vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

export interface SeccionDocente {
  id: number;
  codigo: string;
  nombre: string;
  trayecto: string;
  sede: string;
  estudiantes: number;
}

export interface TipoEvaluacion {
  tev_codigo: number;
  tev_nombre: string;
}

export interface CorteEvaluacion {
  mev_codigo: number;
  mev_cod_tipo_evaluacion: number;
  tipo_nombre: string;
  descripcion: string;
  mev_porcentaje: number;
  mev_semana_planificada: number;
  mev_semana_ejecutada: number | null;
  estatus: 'EJECUTADA' | 'EN_CURSO' | 'PLANIFICADA';
}

export function usePlanificacionDocente() {
  const { confirmDialog } = useConfirmDialog();

  // Secciones asignadas al docente
  const seccionesDocente: SeccionDocente[] = [
    { id: 101, codigo: 'INF-401', nombre: 'Bases de Datos Avanzadas', trayecto: 'Trayecto IV', sede: 'Sede Central Acarigua', estudiantes: 28 },
    { id: 102, codigo: 'INF-302', nombre: 'Ingeniería del Software II', trayecto: 'Trayecto III', sede: 'Sede Central Acarigua', estudiantes: 32 },
    { id: 103, codigo: 'INF-201', nombre: 'Algoritmos y Programación', trayecto: 'Trayecto II', sede: 'Sede Guanare', estudiantes: 35 },
  ];

  const seccionSeleccionada = ref<SeccionDocente>(seccionesDocente[0]);

  // Catálogo de tipos de evaluación disponibles
  const tiposEvaluacion = ref<TipoEvaluacion[]>([
    { tev_codigo: 1, tev_nombre: 'Examen Parcial Escrito' },
    { tev_codigo: 2, tev_nombre: 'Proyecto Sociotecnológico / Práctico' },
    { tev_codigo: 3, tev_nombre: 'Taller / Laboratorio' },
    { tev_codigo: 4, tev_nombre: 'Defensa / Exposición Oral' },
    { tev_codigo: 5, tev_nombre: 'Prueba Corta (Quiz)' },
    { tev_codigo: 6, tev_nombre: 'Portafolio de Evidencias' },
    { tev_codigo: 7, tev_nombre: 'Estudio de Casos' },
    { tev_codigo: 8, tev_nombre: 'Resolución de Problemas / Algoritmos' },
  ]);

  // Metodología personalizada ("Otro")
  const mostrarInputOtraMetodologia = ref(false);
  const nuevaMetodologiaInput = ref('');

  const abrirOtraMetodologia = () => {
    mostrarInputOtraMetodologia.value = !mostrarInputOtraMetodologia.value;
    nuevaMetodologiaInput.value = '';
  };

  const agregarOtraMetodologia = () => {
    const nombre = nuevaMetodologiaInput.value.trim();
    if (!nombre) return;
    const nuevoCodigo = tiposEvaluacion.value.length + 1;
    tiposEvaluacion.value.push({ tev_codigo: nuevoCodigo, tev_nombre: nombre });
    formCorte.value.mev_cod_tipo_evaluacion = nuevoCodigo;
    mostrarInputOtraMetodologia.value = false;
    nuevaMetodologiaInput.value = '';
  };

  // Matriz de evaluación actual
  const matrizEvaluacion = ref<CorteEvaluacion[]>([
    {
      mev_codigo: 1,
      mev_cod_tipo_evaluacion: 1,
      tipo_nombre: 'Examen Parcial Escrito',
      descripcion: 'Unidad I: Arquitectura y Modelado Relacional Avanzado',
      mev_porcentaje: 25,
      mev_semana_planificada: 4,
      mev_semana_ejecutada: 4,
      estatus: 'EJECUTADA',
    },
    {
      mev_codigo: 2,
      mev_cod_tipo_evaluacion: 3,
      tipo_nombre: 'Taller / Laboratorio',
      descripcion: 'Unidad II: Procedimientos Almacenados y Triggers en PostgreSQL',
      mev_porcentaje: 25,
      mev_semana_planificada: 8,
      mev_semana_ejecutada: 8,
      estatus: 'EJECUTADA',
    },
    {
      mev_codigo: 3,
      mev_cod_tipo_evaluacion: 2,
      tipo_nombre: 'Proyecto Sociotecnológico / Práctico',
      descripcion: 'Unidad III: Optimización de Consultas e Indexación',
      mev_porcentaje: 30,
      mev_semana_planificada: 12,
      mev_semana_ejecutada: 13,
      estatus: 'EN_CURSO',
    },
    {
      mev_codigo: 4,
      mev_cod_tipo_evaluacion: 4,
      tipo_nombre: 'Defensa / Exposición Oral',
      descripcion: 'Entrega final y defensa de la base de datos empresarial',
      mev_porcentaje: 20,
      mev_semana_planificada: 16,
      mev_semana_ejecutada: null,
      estatus: 'PLANIFICADA',
    },
  ]);

  // Plantillas de distribución para 4 o 5 evaluaciones
  const aplicarPlantilla4Cortes = () => {
    matrizEvaluacion.value = [
      { mev_codigo: 1, mev_cod_tipo_evaluacion: 1, tipo_nombre: 'Examen Parcial Escrito', descripcion: 'Corte I: Arquitectura y Modelado Relacional (25%)', mev_porcentaje: 25, mev_semana_planificada: 4, mev_semana_ejecutada: 4, estatus: 'EJECUTADA' },
      { mev_codigo: 2, mev_cod_tipo_evaluacion: 3, tipo_nombre: 'Taller / Laboratorio', descripcion: 'Corte II: Procedimientos Almacenados y Triggers (25%)', mev_porcentaje: 25, mev_semana_planificada: 8, mev_semana_ejecutada: 8, estatus: 'EJECUTADA' },
      { mev_codigo: 3, mev_cod_tipo_evaluacion: 2, tipo_nombre: 'Proyecto Sociotecnológico / Práctico', descripcion: 'Corte III: Optimización de Consultas e Indexación (25%)', mev_porcentaje: 25, mev_semana_planificada: 12, mev_semana_ejecutada: null, estatus: 'EN_CURSO' },
      { mev_codigo: 4, mev_cod_tipo_evaluacion: 4, tipo_nombre: 'Defensa / Exposición Oral', descripcion: 'Corte IV: Sustentación y Auditoría del Sistema BD (25%)', mev_porcentaje: 25, mev_semana_planificada: 16, mev_semana_ejecutada: null, estatus: 'PLANIFICADA' },
    ];
  };

  const aplicarPlantilla5Cortes = () => {
    matrizEvaluacion.value = [
      { mev_codigo: 1, mev_cod_tipo_evaluacion: 5, tipo_nombre: 'Prueba Corta (Quiz)', descripcion: 'Evaluación 1: Diagnóstico y SQL DDL (20%)', mev_porcentaje: 20, mev_semana_planificada: 3, mev_semana_ejecutada: 3, estatus: 'EJECUTADA' },
      { mev_codigo: 2, mev_cod_tipo_evaluacion: 3, tipo_nombre: 'Taller / Laboratorio', descripcion: 'Evaluación 2: DML y Normalización Relacional (20%)', mev_porcentaje: 20, mev_semana_planificada: 6, mev_semana_ejecutada: 6, estatus: 'EJECUTADA' },
      { mev_codigo: 3, mev_cod_tipo_evaluacion: 1, tipo_nombre: 'Examen Parcial Escrito', descripcion: 'Evaluación 3: Parcial Teórico Transacciones (20%)', mev_porcentaje: 20, mev_semana_planificada: 10, mev_semana_ejecutada: null, estatus: 'EN_CURSO' },
      { mev_codigo: 4, mev_cod_tipo_evaluacion: 2, tipo_nombre: 'Proyecto Sociotecnológico / Práctico', descripcion: 'Evaluación 4: Desarrollo Práctico en PostgreSQL (20%)', mev_porcentaje: 20, mev_semana_planificada: 13, mev_semana_ejecutada: null, estatus: 'PLANIFICADA' },
      { mev_codigo: 5, mev_cod_tipo_evaluacion: 4, tipo_nombre: 'Defensa / Exposición Oral', descripcion: 'Evaluación 5: Defensa de Portafolio y Resultados (20%)', mev_porcentaje: 20, mev_semana_planificada: 16, mev_semana_ejecutada: null, estatus: 'PLANIFICADA' },
    ];
  };

  // Porcentaje acumulado
  const totalPorcentaje = computed(() => {
    return matrizEvaluacion.value.reduce((acc, item) => acc + Number(item.mev_porcentaje || 0), 0);
  });

  // Modal y formulario de corte
  const mostrarModal = ref(false);
  const modoEdicion = ref(false);
  const itemEditando = ref<CorteEvaluacion | null>(null);

  const formCorte = ref({
    mev_cod_tipo_evaluacion: 1,
    descripcion: '',
    mev_porcentaje: 20,
    mev_semana_planificada: 1,
    mev_semana_ejecutada: null as number | null,
  });

  const abrirNuevoCorte = () => {
    modoEdicion.value = false;
    itemEditando.value = null;
    const rem = Math.max(0, 100 - totalPorcentaje.value);
    formCorte.value = {
      mev_cod_tipo_evaluacion: 1,
      descripcion: '',
      mev_porcentaje: rem > 0 ? rem : 15,
      mev_semana_planificada: matrizEvaluacion.value.length * 4 + 2,
      mev_semana_ejecutada: null,
    };
    mostrarModal.value = true;
  };

  const editarCorte = (item: CorteEvaluacion) => {
    modoEdicion.value = true;
    itemEditando.value = item;
    formCorte.value = {
      mev_cod_tipo_evaluacion: item.mev_cod_tipo_evaluacion,
      descripcion: item.descripcion,
      mev_porcentaje: item.mev_porcentaje,
      mev_semana_planificada: item.mev_semana_planificada,
      mev_semana_ejecutada: item.mev_semana_ejecutada,
    };
    mostrarModal.value = true;
  };

  const guardarCorte = () => {
    const tipo = tiposEvaluacion.value.find(t => t.tev_codigo === Number(formCorte.value.mev_cod_tipo_evaluacion));
    if (modoEdicion.value && itemEditando.value) {
      const idx = matrizEvaluacion.value.findIndex(m => m.mev_codigo === itemEditando.value!.mev_codigo);
      if (idx !== -1) {
        matrizEvaluacion.value[idx] = {
          ...matrizEvaluacion.value[idx],
          mev_cod_tipo_evaluacion: formCorte.value.mev_cod_tipo_evaluacion,
          tipo_nombre: tipo?.tev_nombre || 'Evaluación',
          descripcion: formCorte.value.descripcion,
          mev_porcentaje: Number(formCorte.value.mev_porcentaje),
          mev_semana_planificada: Number(formCorte.value.mev_semana_planificada),
          mev_semana_ejecutada: formCorte.value.mev_semana_ejecutada ? Number(formCorte.value.mev_semana_ejecutada) : null,
        };
      }
    } else {
      const nextId = matrizEvaluacion.value.length > 0 ? Math.max(...matrizEvaluacion.value.map(m => m.mev_codigo)) + 1 : 1;
      matrizEvaluacion.value.push({
        mev_codigo: nextId,
        mev_cod_tipo_evaluacion: formCorte.value.mev_cod_tipo_evaluacion,
        tipo_nombre: tipo?.tev_nombre || 'Evaluación',
        descripcion: formCorte.value.descripcion,
        mev_porcentaje: Number(formCorte.value.mev_porcentaje),
        mev_semana_planificada: Number(formCorte.value.mev_semana_planificada),
        mev_semana_ejecutada: null,
        estatus: 'PLANIFICADA',
      });
    }
    mostrarModal.value = false;
  };

  const eliminarCorte = async (codigo: number) => {
    if (await confirmDialog('¿Está seguro de eliminar este corte de la matriz de evaluación?')) {
      matrizEvaluacion.value = matrizEvaluacion.value.filter(m => m.mev_codigo !== codigo);
    }
  };

  return {
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
    itemEditando,
    formCorte,
    abrirNuevoCorte,
    editarCorte,
    guardarCorte,
    eliminarCorte,
  };
}

