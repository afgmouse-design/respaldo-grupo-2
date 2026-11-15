<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import {
  Building2,
  Truck,
  Monitor,
  Salad,
  Wrench,
  Settings,
  Zap,
  PawPrint,
  ChevronRight,
  Sparkles,
  ChevronLeft,
  ChevronRight as ChevronRightIcon,
  Pause,
  Play,
  Clock,
  Users,
  Award,
  BookOpen,
  GraduationCap,
  Calendar,
  BarChart3,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { onMounted, onUnmounted } from 'vue';
import { computed } from 'vue';
import AppearanceTabsSwitch from '@/components/AppearanceTabsSwitch.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card,  CardDescription,  CardTitle } from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

// Detectar el tema actual
const page = usePage();
const currentTheme = computed(() => {
  // Puedes obtener el tema de diferentes formas:
  // 1. Desde las props de Inertia
  // 2. Desde localStorage
  // 3. Desde una clase en el HTML
  return page.props.theme || localStorage.getItem('theme') || 'light';
});

// Gradiente dinámico usando las variables CSS del tema
const logoTemplateGradient = computed(() => {
  if (currentTheme.value === 'dark') {
    return 'from-white to-primary'; // Usando la variable primary
  }
  return 'from-primary to-primary';
});

// Función para obtener el gradiente según el color primario
const getPrimaryGradient = (darkMode: boolean = false) => {
  if (darkMode) {
    return 'from-primary to-primary/60';
  }
  return 'from-primary to-primary/80';
};

const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};


// PNF Programs Data
const pnfPrograms = [
  {
    id: 1,
    icon: Building2,
    title: 'Administración de Empresas',
    description: 'Forma profesionales capaces de gestionar, planificar y dirigir organizaciones empresariales con visión estratégica y liderazgo.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Gerente de Empresas',
      'Consultor Administrativo',
      'Director de Proyectos',
      'Administrador de Recursos'
    ],
    color: 'text-blue-600',
    bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'from-blue-500 to-blue-700',
    studentsCount: '1,200+',
    graduatesCount: '800+',
    backgroundImage: '/img/pnf/administracion.jpeg',
    overlayColor: 'from-black/80 to-blue-900/60'
  },
  {
    id: 2,
    icon: Truck,
    title: 'Distribución y Logística',
    description: 'Prepara especialistas en la gestión eficiente de cadenas de suministro, transporte y almacenamiento de mercancías.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Gerente de Logística',
      'Coordinador de Distribución',
      'Analista de Cadena de Suministro',
      'Director de Operaciones'
    ],
    color: 'text-green-600',
    bgColor: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'from-green-500 to-green-700',
    studentsCount: '950+',
    graduatesCount: '600+',
    backgroundImage: '/img/pnf/logistica.jpeg',
    overlayColor: 'from-black/80 to-green-900/60'
  },
  {
    id: 3,
    icon: Monitor,
    title: 'Informática',
    description: 'Forma profesionales en desarrollo de software, seguridad informática y tecnologías de la información.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Desarrollador Full Stack',
      'Ingeniero de Software',
      'Especialista en Ciberseguridad',
      'Arquitecto de TI'
    ],
    color: 'text-purple-600',
    bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    iconColor: 'from-purple-500 to-purple-700',
    studentsCount: '2,500+',
    graduatesCount: '1,800+',
    backgroundImage: '/img/pnf/inf.jpeg',
    overlayColor: 'from-black/80 to-purple-900/60'
  },
  {
    id: 4,
    icon: Salad,
    title: 'Ingeniería en Agroalimentación',
    description: 'Prepara profesionales en procesos agroindustriales, seguridad alimentaria y desarrollo sostenible.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Ingeniero Agroindustrial',
      'Especialista en Calidad Alimentaria',
      'Gestor de Proyectos Agroalimentarios',
      'Investigador en Alimentos'
    ],
    color: 'text-lime-600',
    bgColor: 'bg-lime-100 dark:bg-lime-900/30',
    iconColor: 'from-lime-500 to-lime-700',
    studentsCount: '680+',
    graduatesCount: '450+',
    backgroundImage: '/img/pnf/agro.jpeg',
    overlayColor: 'from-black/80 to-lime-900/60'
  },
  {
    id: 5,
    icon: Wrench,
    title: 'Ingeniería en Mantenimiento',
    description: 'Forma especialistas en mantenimiento industrial, gestión de activos y confiabilidad de equipos.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Ingeniero de Mantenimiento',
      'Gerente de Activos',
      'Supervisor de Planta',
      'Consultor en Confiabilidad'
    ],
    color: 'text-orange-600',
    bgColor: 'bg-orange-100 dark:bg-orange-900/30',
    iconColor: 'from-orange-500 to-orange-700',
    studentsCount: '750+',
    graduatesCount: '520+',
    backgroundImage: '/img/pnf/mantenimiento.jpeg',
    overlayColor: 'from-black/80 to-orange-900/60'
  },
  {
    id: 6,
    icon: Settings,
    title: 'Ingeniería Mecánica',
    description: 'Prepara profesionales en diseño, fabricación y mantenimiento de sistemas mecánicos y maquinaria industrial.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Ingeniero Mecánico',
      'Diseñador de Maquinaria',
      'Supervisor de Producción',
      'Especialista en HVAC'
    ],
    color: 'text-red-600',
    bgColor: 'bg-red-100 dark:bg-red-900/30',
    iconColor: 'from-red-500 to-red-700',
    studentsCount: '890+',
    graduatesCount: '620+',
    backgroundImage: '/img/pnf/mecanica.jpeg',
    overlayColor: 'from-black/80 to-red-900/60'
  },
  {
    id: 7,
    icon: Zap,
    title: 'Ingeniería en Electricidad',
    description: 'Forma especialistas en sistemas eléctricos, automatización, energías renovables y redes inteligentes.',
    duration: '4 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Ingeniero Eléctrico',
      'Especialista en Energías Renovables',
      'Automatizador Industrial',
      'Supervisor de Redes Eléctricas'
    ],
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-100 dark:bg-yellow-900/30',
    iconColor: 'from-yellow-500 to-yellow-700',
    studentsCount: '1,050+',
    graduatesCount: '720+',
    backgroundImage: '/img/pnf/electricidad.jpeg',
    overlayColor: 'from-black/80 to-yellow-800/60'
  },
  {
    id: 8,
    icon: PawPrint,
    title: 'Veterinaria',
    description: 'Forma profesionales en salud animal, producción pecuaria y medicina veterinaria preventiva.',
    duration: '5 años',
    modality: 'Presencial',
    careerOpportunities: [
      'Médico Veterinario',
      'Especialista en Producción Animal',
      'Investigador en Salud Animal',
      'Consultor en Zoonosis'
    ],
    color: 'text-pink-600',
    bgColor: 'bg-pink-100 dark:bg-pink-900/30',
    iconColor: 'from-pink-500 to-pink-700',
    studentsCount: '1,100+',
    graduatesCount: '780+',
    backgroundImage: '/img/pnf/veterinaria.jpeg',
    overlayColor: 'from-black/80 to-pink-900/60'
  }
];


const currentIndex = ref(0);
const isAutoPlaying = ref(true);
let autoPlayInterval: number | null = null;

const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % pnfPrograms.length;
};

const prevSlide = () => {
  currentIndex.value = (currentIndex.value - 1 + pnfPrograms.length) % pnfPrograms.length;
};

const goToSlide = (index: number) => {
  currentIndex.value = index;
};

const toggleAutoPlay = () => {
  isAutoPlaying.value = !isAutoPlaying.value;
  if (isAutoPlaying.value) {
    startAutoPlay();
  } else {
    stopAutoPlay();
  }
};

const startAutoPlay = () => {
  if (autoPlayInterval) clearInterval(autoPlayInterval);
  autoPlayInterval = window.setInterval(() => {
    if (isAutoPlaying.value) {
      nextSlide();
    }
  }, 6000);
};

const stopAutoPlay = () => {
  if (autoPlayInterval) {
    clearInterval(autoPlayInterval);
    autoPlayInterval = null;
  }
};

onMounted(() => {
  startAutoPlay();
});

onUnmounted(() => {
  stopAutoPlay();
});

</script>

<template>
  <Head title="Bienvenido - Sistema de Gestión Universitaria">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet" />
  </Head>

<header class="fixed top-0 z-50 w-full border-b bg-background/80 backdrop-blur-lg border-border">
    <nav class="container mx-auto flex h-auto min-h-16 items-center justify-between gap-3 px-4 py-2 md:px-6 md:py-0">

      <!-- Logo y título - Versión responsive -->
      <div class="flex items-center space-x-2 shrink-0">
        <a href="/" class="shrink-0">
          <img src="/img/logouptp1.png" alt="Logo" class="h-8 w-auto object-contain md:h-8" />
        </a>
        <div class="relative pl-2 md:pl-3 before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-4 md:before:h-6 before:w-px before:bg-linear-to-b before:from-transparent before:via-border before:to-transparent">
          <div class="flex flex-col">
            <span class="text-sm sm:text-base md:text-xl font-bold text-foreground bg-clip-text whitespace-nowrap">
              UPTP J.J. MONTILLA
            </span>
            <p class="hidden sm:block text-[8px] sm:text-[10px] md:text-xs text-muted-foreground font-medium whitespace-nowrap">
              Universidad Politécnica Territorial
            </p>
          </div>
        </div>
      </div>

      <!-- Botón de menú hamburguesa para móvil -->
      <button
        @click="toggleMobileMenu"
        class="md:hidden p-2 rounded-lg hover:bg-accent transition-colors"
        aria-label="Menú"
      >
        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
        <X v-else class="h-5 w-5" />
      </button>

      <!-- Menú de navegación desktop - Oculto en móvil -->
      <div class="hidden md:flex items-center space-x-4 lg:space-x-6">
        <a href="#pnf" class="text-sm font-medium text-foreground/70 hover:text-foreground transition-colors">
          Programas
        </a>
      </div>

      <!-- Botones de acción y switch de tema - Responsive -->
      <div class="flex items-center gap-2 sm:gap-3">
        <template v-if="$page.props.auth.user">
          <Link :href="dashboard()">
            <Button variant="default" size="sm" class="hidden sm:inline-flex md:size-default">
              Ir al Dashboard
            </Button>
            <Button variant="default" size="icon" class="sm:hidden">
              <GraduationCap class="h-4 w-4" />
            </Button>
          </Link>
        </template>
        <template v-else>
          <Link :href="login()" class="hidden sm:inline-block">
            <Button variant="ghost" class="hover:cursor-pointer">
              Iniciar Sesión
            </Button>
          </Link>
          <Link :href="login()" class="sm:hidden">
            <Button variant="ghost" size="icon" class="hover:cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" x2="3" y1="12" y2="12"/>
              </svg>
            </Button>
          </Link>

          <Link v-if="canRegister" :href="register()" class="hidden sm:inline-block">
            <Button class="hover:cursor-pointer bg-primary text-primary-foreground hover:bg-primary/90">
              Registrarse
              <ChevronRight class="ml-2 h-4 w-4" />
            </Button>
          </Link>
          <Link v-if="canRegister" :href="register()" class="sm:hidden">
            <Button size="icon" class="hover:cursor-pointer bg-primary text-primary-foreground hover:bg-primary/90">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" x2="19" y1="8" y2="14"/>
                <line x1="22" x2="16" y1="11" y2="11"/>
              </svg>
            </Button>
          </Link>

          <div class="py-2">
            <AppearanceTabsSwitch :compact="true" />
          </div>
        </template>
      </div>
    </nav>

    <!-- Menú móvil desplegable -->
    <div
      v-if="mobileMenuOpen"
      class="md:hidden absolute top-full left-0 right-0 bg-background/95 backdrop-blur-lg border-b border-border shadow-lg"
    >
      <div class="container mx-auto px-4 py-4 flex flex-col gap-4">
        <a
          href="#pnf"
          @click="closeMobileMenu"
          class="text-base font-medium text-foreground/70 hover:text-foreground transition-colors py-2"
        >
          Programas
        </a>
      </div>
    </div>
  </header>



    <!-- Hero Section -->
  <section class="relative min-h-162.5 md:min-h-187.5 flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
      <div class="absolute inset-0 bg-cover bg-center">
        <img src="/img/entradauptp1.jpeg" alt="Campus" class="w-full h-full object-cover" />
      </div>
      <div class="absolute inset-0 bg-linear-to-t from-black/50 via-black/30 to-background/5 dark:from-background/90 dark:via-background/60 dark:to-background/30"></div>
    </div>

    <div class="container mx-auto px-4 md:px-6 relative z-10 text-center">
      <div class="animate-bounce">
        <Badge class="mb-6 bg-primary text-primary-foreground border-0 shadow-lg">
          <Sparkles class="mr-1 h-3 w-3" />
          Innovación Educativa 2026
        </Badge>
      </div>

      <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 max-w-5xl mx-auto">
        La Universidad Granera N° 1 de Venezuela
      </h1>

      <p class="text-lg md:text-xl text-white mb-10 max-w-3xl mx-auto leading-relaxed">
        Destacada como líder en educación universitaria en Venezuela, representamos un símbolo de excelencia académica y compromiso con la formación integral de profesionales capaces de transformar la sociedad.
      </p>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto mt-16">
        <div class="text-center p-3 rounded-lg bg-background/10 backdrop-blur-sm border border-border">
          <div class="text-2xl font-bold text-white">+10</div>
          <div class="text-xs text-white/70">Programas</div>
        </div>
        <div class="text-center p-3 rounded-lg bg-background/10 backdrop-blur-sm border border-border">
          <div class="text-2xl font-bold text-white">+5k</div>
          <div class="text-xs text-white/70">Estudiantes</div>
        </div>
        <div class="text-center p-3 rounded-lg bg-background/10 backdrop-blur-sm border border-border">
          <div class="text-2xl font-bold text-white">+200</div>
          <div class="text-xs text-white/70">Docentes</div>
        </div>
        <div class="text-center p-3 rounded-lg bg-background/10 backdrop-blur-sm border border-border">
          <div class="text-2xl font-bold text-white">47</div>
          <div class="text-xs text-white/70">Años de Trayectoria</div>
        </div>
      </div>
    </div>
  </section>

  <!-- PNF Section -->
  <section id="pnf" class="py-16 md:py-24 bg-background">
    <div class="container mx-auto px-4 md:px-6">

      <div class="text-center max-w-3xl mx-auto mb-12">
        <Badge class="mb-4 bg-primary/10 text-primary border-0">
          <Sparkles class="mr-1 h-3 w-3" />
          Programas Nacionales de Formación
        </Badge>
        <h2 class="text-3xl md:text-4xl font-bold mb-4 bg-linear-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
          Oferta Académica
        </h2>
        <p class="text-lg text-muted-foreground">
          Descubre nuestros 8 programas de formación profesional diseñados para tu éxito
        </p>
      </div>

      <!-- Carrusel principal de PNF -->
      <div class="relative max-w-6xl mx-auto">
        <div class="relative overflow-hidden rounded-2xl shadow-2xl">
          <div
            class="flex transition-transform duration-500 ease-out"
            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
          >
            <div
              v-for="(program, index) in pnfPrograms"
              :key="index"
              class="w-full shrink-0"
            >
              <Card class="border-border shadow-none bg-card overflow-hidden">
                <div class="relative">
                  <div :class="`absolute top-0 left-0 right-0 h-2 bg-linear-to-r ${program.iconColor}`"></div>

                  <div class="grid md:grid-cols-2 gap-6 p-6 md:p-8">
                    <!-- Contenido de la izquierda -->
                    <div class="flex flex-col justify-center space-y-4">
                      <div class="mb-4">
                        <div :class="`w-16 h-16 rounded-2xl ${program.bgColor} flex items-center justify-center shadow-lg`">
                          <component :is="program.icon" :class="`h-8 w-8 ${program.color}`" />
                        </div>
                      </div>

                      <CardTitle class="text-2xl md:text-3xl mb-3 text-card-foreground">
                        {{ program.title }}
                      </CardTitle>

                      <CardDescription class="text-base text-muted-foreground leading-relaxed">
                        {{ program.description }}
                      </CardDescription>

                      <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="flex items-center gap-2 text-sm">
                          <Clock class="h-4 w-4 text-primary" />
                          <span class="text-muted-foreground">Duración: {{ program.duration }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                          <Users class="h-4 w-4 text-primary" />
                          <span class="text-muted-foreground">Modalidad: {{ program.modality }}</span>
                        </div>
                      </div>

                      <div class="grid grid-cols-2 gap-4 mt-2">
                        <div class="bg-muted rounded-lg p-3 text-center">
                          <div class="text-lg font-bold text-primary">{{ program.studentsCount }}</div>
                          <div class="text-xs text-muted-foreground">Estudiantes</div>
                        </div>
                        <div class="bg-muted rounded-lg p-3 text-center">
                          <div class="text-lg font-bold text-primary">{{ program.graduatesCount }}</div>
                          <div class="text-xs text-muted-foreground">Graduados</div>
                        </div>
                      </div>

                      <div class="mt-4">
                        <h4 class="font-semibold text-card-foreground mb-2 flex items-center gap-2">
                          <Award class="h-4 w-4 text-primary" />
                          Oportunidades Laborales:
                        </h4>
                        <div class="flex flex-wrap gap-2">
                          <Badge v-for="career in program.careerOpportunities" :key="career" variant="secondary" class="text-xs">
                            {{ career }}
                          </Badge>
                        </div>
                      </div>

                      <Button variant="outline" class="mt-6 w-fit group border-border hover:bg-accent">
                        Ver plan de estudios
                        <ChevronRight class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform" />
                      </Button>
                    </div>

                    <!-- Lado derecho con imagen -->
                    <div class="relative rounded-xl overflow-hidden bg-cover bg-center bg-no-repeat min-h-87.5 flex flex-col items-center justify-center p-6">
                      <img
                        :src="program.backgroundImage"
                        :alt="program.title"
                        class="absolute inset-0 w-full h-full object-cover transition-all duration-500"
                        :class="{ 'scale-110': currentIndex === index }"
                      />
                      <div :class="`absolute inset-0 bg-linear-to-br ${program.overlayColor}`"></div>
                    </div>
                  </div>
                </div>
              </Card>
            </div>
          </div>
        </div>

        <!-- Controles del carrusel -->
        <div class="absolute top-1/2 left-4 transform -translate-y-1/2">
          <button
            @click="prevSlide"
            class="bg-background/90 hover:bg-background rounded-full p-2 shadow-lg transition-all duration-300 hover:scale-110 border border-border"
          >
            <ChevronLeft class="h-6 w-6 text-foreground" />
          </button>
        </div>

        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
          <button
            @click="nextSlide"
            class="bg-background/90 hover:bg-background rounded-full p-2 shadow-lg transition-all duration-300 hover:scale-110 border border-border"
          >
            <ChevronRightIcon class="h-6 w-6 text-foreground" />
          </button>
        </div>

        <!-- Indicadores -->
        <div class="flex justify-center mt-8 space-x-2">
          <button
            v-for="(program, index) in pnfPrograms"
            :key="index"
            @click="goToSlide(index)"
            :class="[
              'h-2 rounded-full transition-all duration-300',
              currentIndex === index
                ? 'w-8 bg-primary'
                : 'w-2 bg-border hover:bg-primary/50'
            ]"
          />
        </div>

        <!-- Control de autoplay -->
        <div class="absolute -bottom-12 right-0">
          <button
            @click="toggleAutoPlay"
            class="bg-background border border-border rounded-full p-2 shadow-md hover:shadow-lg transition-all duration-300"
          >
            <Pause v-if="isAutoPlaying" class="h-4 w-4 text-foreground" />
            <Play v-else class="h-4 w-4 text-foreground" />
          </button>
        </div>
      </div>
    </div>
  </section>


    <!-- Footer -->
    <footer class="bg-card border-t border-border text-muted-foreground py-12">
    <div class="container mx-auto px-4 md:px-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <img src="/img/logouptp1.png" alt="Logo" class="h-8 w-auto object-contain md:h-8" />
            <span class="text-sm sm:text-base md:text-xl font-bold text-foreground bg-clip-text whitespace-nowrap">
              UPTP J.J. MONTILLA
            </span>
          </div>
          <p class="text-sm text-muted-foreground">
            Transformando la educación superior a través de la tecnología.
          </p>
        </div>
        <div>
          <h3 class="text-foreground font-semibold mb-4">Plataforma</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#pnf" class="hover:text-foreground transition-colors">Programas</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Características</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Demo</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-foreground font-semibold mb-4">Soporte</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-foreground transition-colors">Centro de Ayuda</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Contacto</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Documentación</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-foreground font-semibold mb-4">Legal</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-foreground transition-colors">Privacidad</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Términos</a></li>
            <li><a href="#" class="hover:text-foreground transition-colors">Cookies</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-border pt-8 text-center text-sm text-muted-foreground">
        <p>© 2025 Derechos reservados. Universidad Politécnica Territorial del estado Portuguesa "Juan de Jesús Montilla".</p>
      </div>
    </div>
  </footer>
</template>
