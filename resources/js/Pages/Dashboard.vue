<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import {
  Users,
  GraduationCap,
  Briefcase,
  Landmark,
  BookOpen,
  Building2,
  UserPlus,
  ArrowUpRight,
  CheckCircle2,
  Clock,
  Award,
  Search,
  ChevronRight,
  TrendingUp,
  FileSpreadsheet,
  Calculator,
  Sparkles,
  BookMarked,
  Layers,
  FileCheck,
  HeartPulse,
  QrCode,
  FolderArchive,
  ShieldCheck,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard General',
    href: '/dashboard',
  },
];

const safeRoute = (name: string, params?: any, fallback = '#') => {
  try {
    return route(name, params);
  } catch (e) {
    return fallback;
  }
};

// Módulos Institucionales y de Gestión Integral (Salud, QR, Expediente, SUD)
const modulosInstitucionalesNuevos = [
  {
    title: 'Bienestar y Salud',
    subtitle: 'Ficha médica y accesibilidad',
    description: 'Historial médico CIE-10, adaptaciones de aula, tratamientos y registro CONAPDIS.',
    icon: HeartPulse,
    color: 'text-emerald-600 dark:text-emerald-400',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/40',
    borderColor: 'border-emerald-200 dark:border-emerald-900/40',
    badge: 'Salud CIE-10',
    route: safeRoute('bienestar.index'),
  },
  {
    title: 'Validación Digital QR',
    subtitle: 'Seguridad y autenticidad digital',
    description: 'Verificador público con hash SHA-256, emisión y revocación de constancias y títulos.',
    icon: QrCode,
    color: 'text-indigo-600 dark:text-indigo-400',
    bgColor: 'bg-indigo-50 dark:bg-indigo-950/40',
    borderColor: 'border-indigo-200 dark:border-indigo-900/40',
    badge: 'Seguridad SHA-256',
    route: safeRoute('certificaciones.qr'),
  },
  {
    title: 'Expediente Documental',
    subtitle: 'Recepción y solvencias DACE',
    description: 'Checklist de recaudos, solvencias institucionales (Adm, Aca, Bib) y títulos previos.',
    icon: FolderArchive,
    color: 'text-teal-600 dark:text-teal-400',
    bgColor: 'bg-teal-50 dark:bg-teal-950/40',
    borderColor: 'border-teal-200 dark:border-teal-900/40',
    badge: 'Solvencias DACE',
    route: safeRoute('expediente.documentos'),
  },
  {
    title: 'Secciones y Asignación Docente',
    subtitle: 'Planificación y aforo de aulas',
    description: 'Apertura de secciones por lapso académico, adscripción docente, control de cupos y aforo.',
    icon: Users,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/40',
    borderColor: 'border-red-200 dark:border-red-900/40',
    badge: 'Aforos y SUD',
    route: safeRoute('secciones.docente'),
  },
];

// Nuevos Módulos Académicos (Calificaciones, Consolidación IRA y Promociones)
const modulosAcademicos = [
  {
    title: 'Plan de Evaluación',
    subtitle: 'Instrumentos y cortes del 100%',
    description: 'Definición de cortes evaluativos, tipos de ponderación porcentual (100%) y control de semanas de ejecución.',
    icon: FileSpreadsheet,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/40',
    borderColor: 'border-red-200 dark:border-red-900/40',
    badge: 'Docentes',
    route: safeRoute('calificaciones.planificacion'),
  },
  {
    title: 'Carga de Calificaciones',
    subtitle: 'Calificaciones dinámicas (4 a 6 cohortes)',
    description: 'Carga en línea por cohorte docente (esquema configurable de 4 a 6 cohortes), acumulación sobre 100 pts, hitos evaluativos y bitácora de modificaciones.',
    icon: FileCheck,
    color: 'text-indigo-600 dark:text-indigo-400',
    bgColor: 'bg-indigo-50 dark:bg-indigo-950/40',
    borderColor: 'border-indigo-200 dark:border-indigo-900/40',
    badge: 'Actas en Curso',
    route: safeRoute('calificaciones.carga'),
  },
  {
    title: 'Consolidación e Historial (IRA)',
    subtitle: 'Cálculo ponderado acumulado',
    description: 'Cálculo de índice acumulado Σ(Nota×UC)/ΣUC, récord de notas anuales por trayecto y validación de avance curricular.',
    icon: Calculator,
    color: 'text-emerald-600 dark:text-emerald-400',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/40',
    borderColor: 'border-emerald-200 dark:border-emerald-900/40',
    badge: 'Cálculo Ponderado',
    route: safeRoute('calificaciones.consolidacion'),
  },
  {
    title: 'Promociones de Grado',
    subtitle: 'Cohortes y libros de grado',
    description: 'Gestión de cohortes solemnes de egreso, asignación de Tomos de grado, índices máximos/mínimos y títulos otorgados.',
    icon: GraduationCap,
    color: 'text-amber-600 dark:text-amber-400',
    bgColor: 'bg-amber-50 dark:bg-amber-950/40',
    borderColor: 'border-amber-200 dark:border-amber-900/40',
    badge: 'Actos de Grado',
    route: safeRoute('promociones.index'),
  },
  {
    title: 'Padrón de Graduandos',
    subtitle: 'Orden de mérito y folios',
    description: 'Cuadro de honor, ranking general y por PNF, menciones honoríficas (Cum Laude) y asignación de Tomo y Folio de grado.',
    icon: Award,
    color: 'text-purple-600 dark:text-purple-400',
    bgColor: 'bg-purple-50 dark:bg-purple-950/40',
    borderColor: 'border-purple-200 dark:border-purple-900/40',
    badge: 'Cuadro de Honor',
    route: safeRoute('promociones.graduandos'),
  },
];

// Estadísticas de los 4 actores del Módulo de Personas
const stats = [
  {
    title: 'Estudiantes',
    subtitle: 'Matriculados en programas PNF',
    value: '1,248',
    activeText: '1,180 Regulares',
    badgeText: '+12% este lapso',
    badgeVariant: 'successCustom' as const,
    icon: GraduationCap,
    iconColor: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/30',
    borderColor: 'border-red-200/50 dark:border-red-900/40',
    href: safeRoute('personas.index'),
    createHref: safeRoute('personas.create'),
  },
  {
    title: 'Docentes',
    subtitle: 'Con asignación de carga académica',
    value: '142',
    activeText: '128 Activos',
    badgeText: '96% con sección',
    badgeVariant: 'outline' as const,
    icon: BookOpen,
    iconColor: 'text-indigo-600 dark:text-indigo-400',
    bgColor: 'bg-indigo-50 dark:bg-indigo-950/30',
    borderColor: 'border-indigo-200/50 dark:border-indigo-900/40',
    href: safeRoute('personas.docente'),
    createHref: safeRoute('personas.docente.create'),
  },
  {
    title: 'Administrativos',
    subtitle: 'Personal en dependencias y sedes',
    value: '86',
    activeText: '84 en funciones',
    badgeText: 'Todas las sedes',
    badgeVariant: 'outline' as const,
    icon: Briefcase,
    iconColor: 'text-teal-600 dark:text-teal-400',
    bgColor: 'bg-teal-50 dark:bg-teal-950/30',
    borderColor: 'border-teal-200/50 dark:border-teal-900/40',
    href: safeRoute('personas.administrativo'),
    createHref: safeRoute('personas.administrativo.create'),
  },
  {
    title: 'Autoridades',
    subtitle: 'Cargos directivos y de gobierno',
    value: '12',
    activeText: '100% en ejercicio',
    badgeText: 'Firmas activas',
    badgeVariant: 'successCustom' as const,
    icon: Landmark,
    iconColor: 'text-amber-600 dark:text-amber-400',
    bgColor: 'bg-amber-50 dark:bg-amber-950/30',
    borderColor: 'border-amber-200/50 dark:border-amber-900/40',
    href: safeRoute('personas.autoridad'),
    createHref: safeRoute('personas.autoridad.create'),
  },
];

// Distribución por programas académicos (asignacion_programa)
const programas = [
  { nombre: 'PNF en Informática', estudiantes: 412, porcentaje: 33, color: 'bg-red-600' },
  { nombre: 'PNF en Administración', estudiantes: 320, porcentaje: 26, color: 'bg-blue-600' },
  { nombre: 'PNF en Agroalimentación', estudiantes: 215, porcentaje: 17, color: 'bg-emerald-600' },
  { nombre: 'PNF en Electricidad', estudiantes: 160, porcentaje: 13, color: 'bg-amber-500' },
  { nombre: 'PNF en Mecánica', estudiantes: 141, porcentaje: 11, color: 'bg-purple-600' },
];

// Distribución por sedes institucionales
const sedes = [
  { nombre: 'Sede Central Acarigua', estudiantes: 680, docentes: 78, administrativos: 45 },
  { nombre: 'Sede Guanare', estudiantes: 310, docentes: 34, administrativos: 22 },
  { nombre: 'Sede Turén', estudiantes: 148, docentes: 18, administrativos: 11 },
  { nombre: 'Sede Ospino', estudiantes: 110, docentes: 12, administrativos: 8 },
];

// Últimos registros procesados
const ultimosRegistros = [
  {
    cedula: '30123456',
    nombre: 'Alejandro González Pérez',
    rol: 'Estudiante',
    rolVariant: 'default' as const,
    detalle: 'PNF Informática - Sede Central',
    fecha: 'Hoy, 10:30 AM',
    estatus: 'ACTIVO',
    viewRoute: safeRoute('personas.show', { per_cedula: '30123456' }),
  },
  {
    cedula: '15987654',
    nombre: 'Prof. Carlos Mendoza Silva',
    rol: 'Docente',
    rolVariant: 'secondary' as const,
    detalle: 'Bases de Datos - 2 Secciones',
    fecha: 'Ayer, 04:15 PM',
    estatus: 'ACTIVO',
    viewRoute: safeRoute('personas.docente.show', { per_cedula: '15987654' }),
  },
  {
    cedula: '19456123',
    nombre: 'Lic. Ana Gómez Hernández',
    rol: 'Administrativo',
    rolVariant: 'outline' as const,
    detalle: 'DACE - Jefe de Control de Estudios',
    fecha: '22/09/2026',
    estatus: 'ACTIVO',
    viewRoute: safeRoute('personas.administrativo.show', { per_cedula: '19456123' }),
  },
  {
    cedula: '12890456',
    nombre: 'Dra. María Torres Colmenárez',
    rol: 'Autoridad',
    rolVariant: 'destructive' as const,
    detalle: 'Rectorado - Nivel Organigrama 1',
    fecha: '20/09/2026',
    estatus: 'ACTIVO',
    viewRoute: safeRoute('personas.autoridad.show', { per_cedula: '12890456' }),
  },
];
</script>

<template>
  <Head title="Dashboard - Control Central de Personas" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">

      <!-- BANNER DE BIENVENIDA -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-6 text-white shadow-md dark:border dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-600/30 text-red-200 border border-red-500/30 mb-2">
              <span class="h-2 w-2 rounded-full bg-red-400 animate-pulse"></span>
              SOGAC • UPTP JJM
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Módulo 4: Registro y Control de Personas</h1>
            <p class="mt-1 text-sm text-slate-300 max-w-2xl">
              Plataforma centralizada de registro civil, perfiles institucionales, adscripción académica y trazabilidad de autoridades.
            </p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <Button size="sm" variant="outline" class="bg-white/10 hover:bg-white/20 text-white border-white/20" as-child>
              <Link :href="safeRoute('personas.modificarDatosPersonal')">
                <Search class="h-4 w-4 mr-1.5" />
                Buscar por Cédula
              </Link>
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white" as-child>
              <Link :href="safeRoute('personas.create')">
                <UserPlus class="h-4 w-4 mr-1.5" />
                Nuevo Estudiante
              </Link>
            </Button>
          </div>
        </div>
      </div>

      <!-- 1. TARJETAS KPI DE LOS 4 ACTORES -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="stat in stats"
          :key="stat.title"
          class="rounded-2xl border p-5 bg-white shadow-sm transition-all hover:shadow-md dark:bg-slate-950"
          :class="stat.borderColor"
        >
          <div class="flex items-center justify-between">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl" :class="stat.bgColor">
              <component :is="stat.icon" class="h-6 w-6" :class="stat.iconColor" />
            </div>
            <Badge :variant="stat.badgeVariant">{{ stat.badgeText }}</Badge>
          </div>

          <div class="mt-4">
            <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ stat.title }}</h3>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ stat.value }}</span>
              <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ stat.activeText }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">{{ stat.subtitle }}</p>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <Link :href="stat.href" class="font-semibold text-slate-700 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 flex items-center gap-1">
              <span>Ver listado</span>
              <ChevronRight class="h-3.5 w-3.5" />
            </Link>
            <Link :href="stat.createHref" class="font-medium text-red-600 dark:text-red-400 hover:underline">
              + Registrar
            </Link>
          </div>
        </div>
      </div>

      <!-- SECCIÓN: NUEVOS MÓDULOS ACADÉMICOS (CALIFICACIONES, CONSOLIDACIÓN Y PROMOCIONES) -->
      <div class="rounded-2xl border border-red-200 dark:border-red-950/60 bg-gradient-to-br from-white via-red-50/20 to-red-100/20 dark:from-slate-950 dark:via-red-950/10 dark:to-slate-900 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-red-100 dark:border-red-900/30 pb-4">
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-600 text-white mb-1.5">
              <Sparkles class="h-3.5 w-3.5" />
              Módulos Académicos Integrados
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
              Carga Docente, Consolidación de Notas e Historial Académico
            </h2>
            <p class="text-xs text-slate-500 max-w-2xl">
              Acceso directo a la planificación de cortes, carga en línea de actas docentes, motor de promedios ponderados (IRA) y padrón de graduandos.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Button size="sm" variant="outline" class="text-xs gap-1.5" as-child>
              <Link :href="safeRoute('calificaciones.planificacion')">
                <FileSpreadsheet class="h-3.5 w-3.5 text-red-600" />
                Plan de Evaluación
              </Link>
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white text-xs gap-1.5" as-child>
              <Link :href="safeRoute('promociones.index')">
                <GraduationCap class="h-3.5 w-3.5" />
                Ver Promociones
              </Link>
            </Button>
          </div>
        </div>

        <!-- GRID DE LOS 5 MÓDULOS ACADÉMICOS -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <Link
            v-for="mod in modulosAcademicos"
            :key="mod.title"
            :href="mod.route"
            class="group rounded-xl border p-4 bg-white dark:bg-slate-950 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between"
            :class="mod.borderColor"
          >
            <div>
              <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-lg flex items-center justify-center" :class="mod.bgColor">
                  <component :is="mod.icon" class="h-5 w-5" :class="mod.color" />
                </div>
                <Badge variant="outline" class="text-[10px] font-semibold">{{ mod.badge }}</Badge>
              </div>

              <h3 class="font-bold text-sm text-slate-900 dark:text-slate-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                {{ mod.title }}
              </h3>
              <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ mod.subtitle }}</div>
              <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                {{ mod.description }}
              </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-red-600 dark:text-red-400">
              <span>Ingresar</span>
              <ChevronRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
            </div>
          </Link>
        </div>

        <!-- TRACKER INFORMATIVO: PROGRESO DE CALIFICACIONES Y PRÓXIMO ACTO DE GRADO -->
        <div class="grid gap-4 md:grid-cols-2 pt-2">
          <!-- Widget 1: Monitoreo de Carga de Calificaciones -->
          <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                  <FileSpreadsheet class="h-4 w-4 text-red-600" />
                  Progreso Carga de Calificaciones Docente
                </span>
                <span class="font-mono font-bold text-emerald-600">86.2% Consolidado</span>
              </div>
              <p class="text-[11px] text-slate-400">Lapso Académico Activo 2026-I • Cierre de Actas de Examen</p>
              
              <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mt-3">
                <div class="h-full bg-emerald-600 rounded-full" style="width: 86.2%;"></div>
              </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
              <span>112 de 130 secciones cargadas</span>
              <Link :href="safeRoute('calificaciones.carga')" class="font-semibold text-red-600 hover:underline">
                Revisar Actas Pendientes →
              </Link>
            </div>
          </div>

          <!-- Widget 2: Próxima Promoción de Grado -->
          <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                  <GraduationCap class="h-4 w-4 text-amber-600" />
                  Próxima Promoción • XVIII Cohorte Bicentenaria
                </span>
                <Badge variant="successCustom" class="text-[10px]">15 Octubre 2026</Badge>
              </div>
              <p class="text-[11px] text-slate-400">Acto Solemne • Tomo Asignado: <strong class="text-slate-700 dark:text-slate-300 font-mono">TOMO-XXIV</strong></p>

              <div class="grid grid-cols-3 gap-2 mt-3 text-center text-xs">
                <div class="p-1.5 rounded bg-slate-50 dark:bg-slate-800/60">
                  <span class="text-[10px] text-slate-400 block">Graduandos</span>
                  <span class="font-bold text-slate-900 dark:text-slate-100">148</span>
                </div>
                <div class="p-1.5 rounded bg-slate-50 dark:bg-slate-800/60">
                  <span class="text-[10px] text-slate-400 block">Mayor IRA</span>
                  <span class="font-bold text-emerald-600 font-mono">19.82</span>
                </div>
                <div class="p-1.5 rounded bg-slate-50 dark:bg-slate-800/60">
                  <span class="text-[10px] text-slate-400 block">Menciones</span>
                  <span class="font-bold text-amber-600">3 Summa</span>
                </div>
              </div>
            </div>

            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
              <span>Folios protocolizados: 148/148</span>
              <Link :href="safeRoute('promociones.graduandos')" class="font-semibold text-red-600 hover:underline">
                Ver Cuadro de Honor →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN: MÓDULOS DE GESTIÓN INTEGRAL (BIENESTAR, VALIDACIÓN QR, EXPEDIENTE Y SECCIONES) -->
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50 mb-1.5">
              <ShieldCheck class="h-3.5 w-3.5" />
              Gestión Integral, Bienestar y Seguridad Digital
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
              Salud Estudiantil, Acreditación QR, Recaudos y Carga Docente
            </h2>
            <p class="text-xs text-slate-500 max-w-2xl">
              Plataforma institucional para el seguimiento de salud integral y adaptaciones curriculares, verificación criptográfica de documentos, control de expedientes de grado y asignación de secciones.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <Button size="sm" variant="outline" class="text-xs gap-1.5" as-child>
              <Link :href="safeRoute('certificaciones.qr')">
                <QrCode class="h-3.5 w-3.5 text-indigo-600" />
                Validar QR
              </Link>
            </Button>
            <Button size="sm" class="bg-red-600 hover:bg-red-700 text-white text-xs gap-1.5" as-child>
              <Link :href="safeRoute('secciones.docente')">
                <Users class="h-3.5 w-3.5" />
                Ver Secciones
              </Link>
            </Button>
          </div>
        </div>

        <!-- GRID DE LOS 4 MÓDULOS INSTITUCIONALES -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Link
            v-for="mod in modulosInstitucionalesNuevos"
            :key="mod.title"
            :href="mod.route"
            class="group rounded-xl border p-4 bg-slate-50/50 dark:bg-slate-900/40 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between"
            :class="mod.borderColor"
          >
            <div>
              <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-lg flex items-center justify-center" :class="mod.bgColor">
                  <component :is="mod.icon" class="h-5 w-5" :class="mod.color" />
                </div>
                <Badge variant="outline" class="text-[10px] font-semibold">{{ mod.badge }}</Badge>
              </div>

              <h3 class="font-bold text-sm text-slate-900 dark:text-slate-100 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                {{ mod.title }}
              </h3>
              <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ mod.subtitle }}</div>
              <p class="text-xs text-slate-500 mt-2 line-clamp-3 leading-relaxed">
                {{ mod.description }}
              </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-red-600 dark:text-red-400">
              <span>Gestionar Módulo</span>
              <ChevronRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
            </div>
          </Link>
        </div>
      </div>

      <!-- 2. MÓDULOS DE VISTA PRINCIPALES (GRID 2 COLUMNAS) -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- COLUMNA IZQUIERDA (2/3): ÚLTIMOS REGISTROS REALIZADOS -->
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Registros Recientes del Sistema</h2>
              <p class="text-xs text-slate-500">Últimas personas dadas de alta en el maestro centralizado</p>
            </div>
            <Button size="xs" variant="ghost" as-child>
              <Link :href="safeRoute('personas.index')">
                Ver todos
                <ArrowUpRight class="h-3.5 w-3.5 ml-1" />
              </Link>
            </Button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                <tr>
                  <th class="px-3 py-2.5 font-semibold">Persona</th>
                  <th class="px-3 py-2.5 font-semibold">Perfil / Rol</th>
                  <th class="px-3 py-2.5 font-semibold">Adscripción / Detalle</th>
                  <th class="px-3 py-2.5 font-semibold text-center">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="item in ultimosRegistros" :key="item.cedula" class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30">
                  <td class="px-3 py-3">
                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ item.nombre }}</div>
                    <div class="text-xs text-slate-500 font-mono">C.I. {{ item.cedula }}</div>
                  </td>
                  <td class="px-3 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                      :class="{
                        'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300': item.rol === 'Estudiante',
                        'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300': item.rol === 'Docente',
                        'bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300': item.rol === 'Administrativo',
                        'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300': item.rol === 'Autoridad',
                      }"
                    >
                      {{ item.rol }}
                    </span>
                  </td>
                  <td class="px-3 py-3 text-xs text-slate-600 dark:text-slate-300">
                    {{ item.detalle }}
                  </td>
                  <td class="px-3 py-3 text-center">
                    <Button size="xs" variant="outline" as-child class="h-7 px-2">
                      <Link :href="item.viewRoute">Ficha</Link>
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- COLUMNA DERECHA (1/3): DISTRIBUCIÓN POR PROGRAMAS (PNF) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 space-y-5">
          <div class="border-b pb-3 border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Programas Académicos</h2>
              <p class="text-xs text-slate-500">Adscripción de estudiantes (PNF)</p>
            </div>
            <BookOpen class="h-5 w-5 text-red-600 dark:text-red-400" />
          </div>

          <div class="space-y-4">
            <div v-for="prog in programas" :key="prog.nombre" class="space-y-1.5">
              <div class="flex justify-between text-xs">
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ prog.nombre }}</span>
                <span class="text-slate-500 font-mono">{{ prog.estudiantes }} est. ({{ prog.porcentaje }}%)</span>
              </div>
              <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full transition-all" :class="prog.color" :style="{ width: prog.porcentaje + '%' }"></div>
              </div>
            </div>
          </div>

          <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <Button size="sm" variant="ghost" class="w-full text-xs text-slate-600 hover:text-red-600 dark:text-slate-400" as-child>
              <Link :href="safeRoute('personas.tipoAutoridad')">
                Administrar Catálogos y Cargos
                <ChevronRight class="h-3.5 w-3.5 ml-1" />
              </Link>
            </Button>
          </div>
        </div>

      </div>

      <!-- 3. MÓDULO DE DISTRIBUCIÓN TERRITORIAL POR SEDES -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Distribución Territorial por Sedes Universitaria</h2>
            <p class="text-xs text-slate-500">Desglose de actores matriculados por campus (Sedes)</p>
          </div>
          <Building2 class="h-5 w-5 text-slate-500" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="sede in sedes"
            :key="sede.nombre"
            class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/20"
          >
            <h3 class="font-semibold text-sm text-slate-900 dark:text-slate-100 mb-3">{{ sede.nombre }}</h3>
            <div class="space-y-2 text-xs">
              <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Estudiantes:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ sede.estudiantes }}</span>
              </div>
              <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Docentes:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ sede.docentes }}</span>
              </div>
              <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Administrativos:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ sede.administrativos }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
