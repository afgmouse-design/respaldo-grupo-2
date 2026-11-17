<script setup lang="ts">
/**
 * Vista Standalone: Horarios (Programación General de Espacios y Tiempo)
 *
 * Propósito: Matriz interactiva semanal para consultar y coordinar los bloques
 * de clase asignados a secciones, docentes y aulas bajo el motor Anti-Choque.
 */
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Calendar,
    School,
    Layers,
    Plus,
    UserCheck,
    Printer,
    Trash2,
} from 'lucide-vue-next';
import AsignarHorarioUnificadoModal from './Partials/AsignarHorarioUnificadoModal.vue';
import {
    coincideDiaHorario,
    formatearHoraCorta,
    obtenerAulaEtiqueta,
    obtenerCodigoMateriaHorario,
    obtenerNombreDocenteCarga,
    obtenerNombreMateriaHorario,
    obtenerSeccionHorario,
    type DocenteSelectOption,
} from '@/lib/academico';

const props = defineProps<{
    secciones: Array<{
        id_sec: number;
        nombre_sec: string;
        codigo_sec?: string;
        malla_curricular?: {
            detalles?: Array<{
                id_mcd: number;
                unidad_curricular: {
                    id_unc: number;
                    nombre_unc: string;
                    codigo_unc: string;
                };
            }>;
        };
    }>;
    aulas: Array<{ id_aul: number; codigo_aula_aul: string; nombre_aul?: string }>;
    docentes: DocenteSelectOption[];
    horarios: any[];
    filters: {
        seccion_id?: string | number;
        aula_id?: string | number;
    };
}>();

const isModalNuevoHorarioOpen = ref(false);
const seccionFiltro = ref(props.filters.seccion_id ?? '');
const aulaFiltro = ref(props.filters.aula_id ?? '');

const aplicarFiltros = () => {
    router.get(route('horarios.index'), {
        seccion_id: seccionFiltro.value || undefined,
        aula_id: aulaFiltro.value || undefined,
    }, { preserveState: true });
};

const limpiarFiltros = () => {
    seccionFiltro.value = '';
    aulaFiltro.value = '';
    aplicarFiltros();
};

const eliminarBloque = (id: number) => {
    if (confirm('¿Está seguro de que desea eliminar este bloque de horario?')) {
        router.delete(route('horarios.eliminar_bloque', id), {
            preserveScroll: true,
            onSuccess: () => aplicarFiltros(),
        });
    }
};

const dias = [
    { id: 1, nombre: 'Lunes' },
    { id: 2, nombre: 'Martes' },
    { id: 3, nombre: 'Miércoles' },
    { id: 4, nombre: 'Jueves' },
    { id: 5, nombre: 'Viernes' },
    { id: 6, nombre: 'Sábado' },
];

const getClasesPorDia = (diaId: number, diaNombre?: string) => {
    return props.horarios.filter((h) => coincideDiaHorario(h, diaId, diaNombre));
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Espacios y Tiempo', href: route('aula.index') },
        { title: 'Horarios', href: route('horarios.index') },
    ]">
        <Head title="Programación de Horarios - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Programación y Cuadrícula de Horarios
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Matriz semanal de asignaciones de bloques por aula, sección y profesor con verificación Anti-Choque.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="default" @click="isModalNuevoHorarioOpen = true">
                        <Plus class="h-4 w-4 mr-1.5" />
                        + Nuevo Horario
                    </Button>
                    <a
                        :href="route('informes.horarios_semanal_turnos_pdf', seccionFiltro ? { seccion_id: seccionFiltro } : {})"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
                    >
                        <Printer class="h-4 w-4 mr-2" />
                        Descargar PDF Semanal
                    </a>
                    <Button variant="outline" as-child>
                        <Link :href="route('seccion_unidad_docente.index')">
                            <Layers class="h-4 w-4 mr-2" />
                            Ver en SUD
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FILTROS DE SELECCIÓN -->
            <div class="bg-card border rounded-xl p-4 shadow-xs flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1 w-full space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground">Filtrar por Sección:</label>
                    <select
                        v-model="seccionFiltro"
                        class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="aplicarFiltros"
                    >
                        <option value="">Todas las Secciones</option>
                        <option v-for="sec in props.secciones" :key="sec.id_sec" :value="sec.id_sec">
                            {{ sec.nombre_sec }} ({{ sec.codigo_sec || 'S/C' }})
                        </option>
                    </select>
                </div>

                <div class="flex-1 w-full space-y-1">
                    <label class="text-xs font-semibold text-muted-foreground">Filtrar por Aula:</label>
                    <select
                        v-model="aulaFiltro"
                        class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="aplicarFiltros"
                    >
                        <option value="">Todas las Aulas</option>
                        <option v-for="aul in props.aulas" :key="aul.id_aul" :value="aul.id_aul">
                            {{ obtenerAulaEtiqueta(aul) }}
                        </option>
                    </select>
                </div>

                <div class="self-end pt-5">
                    <Button variant="ghost" size="sm" @click="limpiarFiltros">
                        Limpiar Filtros
                    </Button>
                </div>
            </div>

            <!-- MATRIZ SEMANAL (6 DÍAS) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div
                    v-for="dia in dias"
                    :key="dia.id"
                    class="bg-card border rounded-xl overflow-hidden shadow-xs flex flex-col"
                >
                    <div class="bg-muted/50 p-3 border-b text-center">
                        <h2 class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ dia.nombre }}</h2>
                        <span class="text-[11px] text-muted-foreground">
                            {{ getClasesPorDia(dia.id, dia.nombre).length }} bloques asignados
                        </span>
                    </div>

                    <div class="p-2 space-y-2 flex-1 overflow-y-auto max-h-[500px]">
                        <div
                            v-for="item in getClasesPorDia(dia.id, dia.nombre)"
                            :key="item.id_hor ?? item.id_sho"
                            class="p-2.5 rounded-lg border bg-background text-xs space-y-1 border-l-4 border-l-primary shadow-2xs relative group"
                        >
                            <div class="flex items-center justify-between font-mono text-[11px] text-muted-foreground">
                                <span class="font-bold text-foreground">{{ formatearHoraCorta(item.hora_inicio_hor ?? item.hora_inicio) }} - {{ formatearHoraCorta(item.hora_fin_hor ?? item.hora_fin) }}</span>
                                <Badge variant="outline" class="text-[10px] px-1 py-0">
                                    {{ item.tipo_sesion_hor || item.tipo_sesion || 'Teoría' }}
                                </Badge>
                            </div>

                            <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                {{ obtenerNombreMateriaHorario(item) }}
                                <span class="text-[10px] text-muted-foreground block font-normal font-mono">
                                    {{ obtenerCodigoMateriaHorario(item) }}
                                </span>
                            </div>

                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <Layers class="h-3 w-3 shrink-0" />
                                <span>Sec: {{ obtenerSeccionHorario(item) }}</span>
                            </div>

                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                <UserCheck class="h-3 w-3 shrink-0" />
                                <span class="font-medium text-slate-700 dark:text-slate-300">
                                    Prof: {{ obtenerNombreDocenteCarga(item.carga_academica ?? item.seccion_unidad_docente) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <div class="text-[11px] font-semibold text-primary flex items-center gap-1">
                                    <School class="h-3 w-3 shrink-0" />
                                    <span>{{ obtenerAulaEtiqueta(item.aula) }}</span>
                                </div>
                                <button
                                    type="button"
                                    class="text-muted-foreground hover:text-destructive transition-colors p-1 rounded cursor-pointer"
                                    title="Eliminar este bloque"
                                    @click="eliminarBloque(item.id_hor ?? item.id_sho)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div v-if="getClasesPorDia(dia.id, dia.nombre).length === 0" class="text-center py-8 text-muted-foreground text-xs italic">
                            Sin clases este día
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL UNIFICADO: NUEVO HORARIO -->
            <AsignarHorarioUnificadoModal
                :is-open="isModalNuevoHorarioOpen"
                :secciones="props.secciones"
                :docentes="props.docentes"
                :aulas="props.aulas"
                @update:is-open="isModalNuevoHorarioOpen = $event"
                @success="aplicarFiltros"
            />
        </div>
    </AppLayout>
</template>

