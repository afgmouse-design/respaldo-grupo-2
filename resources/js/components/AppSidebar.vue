<script setup lang="ts">
/**
 * Componente: AppSidebar
 * Propósito: Barra lateral de navegación principal con soporte colapsable e íconos interactivos.
 * 
 * Actualización (Eliminación de Botón Duplicado):
 * Se removió el componente `SidebarTrigger` interno del encabezado para dejar un único botón
 * disparador en la barra superior (`AppSidebarHeader`), evitando la redundancia visual de dos flechas juntas.
 *
 * Actualización 2026-10-21 (Inclusión de Sede PNF en Sidebar):
 * Se agregó la opción de navegación hacia 'Sede PNF' (ruta sede_pnf.index) dentro del grupo 'Lapsos'
 * en el módulo 'Lapsos, Secciones y Horarios', permitiendo acceder a la oferta académica por sede.
 *
 * Actualización 2026-10-21 (Inclusión de Maestro de Sección):
 * Se agregó la opción 'Secciones' (ruta seccion.index) dentro del grupo 'Secciones'
 * en el módulo 'Lapsos, Secciones y Horarios', enlazando al nuevo maestro administrativo CRUD.
 *
 * Actualización 2026-10-21 (Depuración de Opciones en Currículo):
 * Se removieron las opciones obsoletas 'Tipo De Evaluación', 'Requerimiento' y 'Modalidad De Estudio'
 * del grupo 'Currículo' en 'Estructura Académica' a solicitud del usuario para limpiar el menú lateral.
 *
 * Actualización 2026-10-21 (Reorganización de Sedes y Espacios):
 * Se eliminó la opción 'Ambiente' de 'Espacios y tiempo', se priorizaron las opciones activas ('Aulas' y
 * 'Tipos de Aula'), y en 'Institución' se ordenaron las opciones activas incorporando 'Sede PNF' junto a 'Sede'
 * y removiendo las opciones inactivas 'Condición Sede' y 'Tipos de Condición'.
 *
 * Actualización 2026-10-21 (Depuración en Configuración Institucional):
 * Se eliminaron las opciones 'Dependencia' y 'Dependencia Administrativa' del grupo 'Institución' a solicitud del usuario.
 *
 * Actualización 2026-10-21 (Etiquetado '(sin Tabla)' en Lapsos, Secciones y Espacios):
 * Se añadió la etiqueta informativa '(sin Tabla)' a 'Tipo De Lapso', 'Tipo De Sección' y 'Turno'
 * indicando que no cuentan con tabla física en base de datos por los momentos.
 *
 * Actualización 2026-10-21 (Etiquetado '(pronto)' en Intensivos y Fusionar Secciones):
 * Se añadió la etiqueta informativa '(pronto)' a 'Intensivo', 'Cerrar Intensivo' y 'Fusionar Secciones'
 * indicando que son funcionalidades planificadas a futuro.
 *
 * Actualización 2026-10-21 (Eliminación de 'Cambiar Capacidad De Secciones'):
 * Se removió la opción redundante 'Cambiar Capacidad De Secciones' del grupo 'Secciones',
 * manteniendo la opción individual 'Cambiar Capacidad Por Sección' según las instrucciones del usuario.
 *
 * Actualización 2026-10-21 (Corrección de Ruta Ziggy en Lapso Académico):
 * Se actualizó la referencia de `lapso_academico.index` a `lapso.index` en el menú lateral
 * para coincidir con la convención estándar del controlador y enrutador del sistema.
 *
 * Actualización 2026-10-21 (Eliminación del Módulo Inventario):
 * Motivo: Se retiró el módulo de prueba 'Inventario' (Productos y Kanban) del menú lateral
 * por solicitud del usuario, desacoplándolo del catálogo institucional SOGAC.
 */
import { Link } from '@inertiajs/vue3';
import {
  LayoutGrid,
  Building2,
  BookOpen,
  Users,
  Calendar,
  Clock,
  FileText,
  MapPin,
} from 'lucide-vue-next';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { useMediaQuery } from '@vueuse/core';
import { ref } from 'vue';
import Sheet from './ui/sheet/Sheet.vue';
import SheetTrigger from './ui/sheet/SheetTrigger.vue';
import HoverCard from './ui/hover-card/HoverCard.vue';
import HoverCardTrigger from './ui/hover-card/HoverCardTrigger.vue';
import HoverCardContent from './ui/hover-card/HoverCardContent.vue';
import SheetContent from './ui/sheet/SheetContent.vue';
import SidebarGroupLabel from './ui/sidebar/SidebarGroupLabel.vue';
import NavMain from './NavMain.vue';

// ---------- Tipos mejorados ----------
interface NavSubItem {
  title: string;
  url: string;
  onClick?: () => void; // por si necesitas acciones
}

interface NavGroup {
  title: string;
  icon: any; // componente de Lucide
  items: NavSubItem[];
}

interface NavModule {
  title: string;
  url: string;
  icon: any;
  isActive: boolean;
  groups?: NavGroup[];       // si existe, se usará popover
  urlDirect?: string;        // url cuando no hay grupos (enlace directo)
}

// ---------- Definición manual de módulos ----------
const navMain: NavModule[] = [
  {
    title: 'Dashboard',
    url: dashboard(),
    icon: LayoutGrid,
    isActive: false,
    urlDirect: dashboard(),   // enlace directo
  },
  {
    title: 'Config. Institucional y Geográfica',
    url: '#',
    icon: Building2,
    isActive: false,
    groups: [
      {
        title: 'Institución',
        icon: Building2,
        items: [
          { title: 'Universidad', url: route('organizacion.universidad.index') },
          { title: 'Sede', url: route('organizacion.sede.index') },
          { title: 'Sede PNF', url: route('sede_pnf.index') },
          { title: 'Configuración Del Sistema', url: '#' },
        ],
      },
      {
        title: 'Ubicación Geográfica',
        icon: MapPin,
        items: [
          { title: 'País', url: route('geografia.pais.index') },
          { title: 'Estado', url: route('geografia.estado.index') },
          { title: 'Municipio', url: route('geografia.municipio.index') },
          { title: 'Parroquia', url: route('geografia.parroquia.index') },
          { title: 'Localidad', url: route('geografia.localidad.index') },
          { title: 'Condición', url: route('condicion.index') },
        ],
      },
    ],
  },
  {
    title: 'Estructura Académica',
    url: '#',
    icon: BookOpen,
    isActive: false,
    groups: [
      {
        title: 'Currículo',
        icon: BookOpen,
        items: [
          { title: 'Programa (PNF)', url: route('academico.pnf.index') },
          { title: 'Malla', url: route('academico.malla-curricular.index') },
          { title: 'Unidad Curricular', url: route('academico.unidad-curricular.index') },
          { title: 'Tipo De Unidad Curricular', url: route('academico.tipo-unidad-curricular.index') },
          { title: 'Eje Curricular', url: route('academico.eje-curricular.index') },
          { title: 'Escala De Medición', url: route().has('academico.escala-medicion.index') ? route('academico.escala-medicion.index') : route('estructura_academica.escalas_medicion.index') },
          { title: 'Régimen De Estudio', url: '#' },
          { title: 'Tipo de Período', url: route('academico.tipo-periodo.index') },
          { title: 'Trayecto', url: route('academico.trayecto.index') },
        ],
      },
      {
        title: 'Reportes',
        icon: FileText,
        items: [
          { title: 'Listado De Malla', url: route().has('academico.reportes.listado-malla') ? route('academico.reportes.listado-malla') : '/academico/reportes/listado-malla' },
          { title: 'Listado De Malla Con Prelación', url: route().has('academico.reportes.listado-malla-prelacion') ? route('academico.reportes.listado-malla-prelacion') : '/academico/reportes/listado-malla-prelacion' },
          { title: 'Relación De Unidades', url: route().has('academico.reportes.relacion-unidades') ? route('academico.reportes.relacion-unidades') : '/academico/reportes/relacion-unidades' },
          { title: 'Relación De Unidades General', url: route().has('academico.reportes.relacion-unidades-general') ? route('academico.reportes.relacion-unidades-general') : '/academico/reportes/relacion-unidades-general' },
        ],
      },
    ],
  },
  {
    title: 'Lapsos, Secciones y Horarios',
    url: '#',
    icon: Calendar,
    isActive: false,
    groups: [
      {
        title: 'Lapsos',
        icon: Calendar,
        items: [
          { title: 'Año Académico', url: route('academico.ano-academico.index') },
          { title: 'Lapso Académico', url: route('lapso.index') },
          { title: 'Tipo De Lapso (sin Tabla)', url: route('tipo_lapso.index') },
          { title: 'Abrir Lapso', url: '#' },
          { title: 'Cerrar Lapso', url: '#' },
          { title: 'Cerrar Lapso Especial', url: '#' },
          { title: 'Cursos Intensivos', url: route('informes.chequeo_intensivo') },
          { title: 'Cerrar Intensivo', url: route('informes.chequeo_intensivo') },
          { title: 'Cohortes', url: route('cohorte.index') },
          { title: 'Sede PNF', url: route('sede_pnf.index') },
          { title: 'Cohortes por Sede', url: route('cohorte_sede.index') },
        ],
      },
      {
        title: 'Secciones',
        icon: Users,
        items: [
          { title: 'Secciones', url: route('seccion.index') },
          { title: 'Sección (SUD)', url: route('seccion_unidad_docente.index') },
          { title: 'Tipos de Sección', url: route('tipo_seccion.index') },
          { title: 'Abrir Sección', url: route('seccion.abrir') },
          { title: 'Cerrar Sección', url: route('seccion.cerrar') },
          { title: 'Fusionar Secciones (pronto)', url: route('seccion.fusionar') },
          { title: 'Cambiar Capacidad Por Sección', url: route('seccion.capacidad') },
        ],
      },
      {
        title: 'Espacios y tiempo',
        icon: Clock,
        items: [
          { title: 'Aulas', url: route('aula.index') },
          { title: 'Tipos de Aula', url: route('tipo_aula.index') },
          { title: 'Turnos', url: route('turno.index') },
          { title: 'Horarios', url: route('horario.index') },
          { title: 'Programación Semanal', url: route('horarios.index') },
        ],
      },
      {
        title: 'INFORMES',
        icon: FileText,
        items: [
          { title: 'Horario Estudiante', url: route('seccion.horario_estudiante') },
          { title: 'Listado De Chequeo Intensivo', url: route('informes.chequeo_intensivo') },
          { title: 'Listado De Clases', url: route('informes.clases') },
          { title: 'Listado De Secciones', url: route('informes.secciones') },
          { title: 'Secciones Abiertas', url: route('informes.secciones_abiertas') },
          { title: 'Horario De Aula', url: route('informes.horario_aula') },
          { title: 'Horario De Docente', url: route('informes.horario_docente') },
          { title: 'Horario De Sección', url: route('informes.horario_seccion') },
        ],
      },
    ],
  },
];

const footerNavItems: any[] = []; // tu configuración de footer

// Detecta si es desktop (>=1024px) para elegir Popover vs Sheet
const isDesktop = useMediaQuery('(min-width: 1024px)')

// Controla qué módulo está abierto en el Sheet (móvil)
const activeModule = ref<string | null>(null)
</script>

<template>

  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="dashboard()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :navMain="navMain" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>