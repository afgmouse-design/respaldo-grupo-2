<script setup lang="ts">
/**
 * Vista Standalone: Listado De Clases (Informes)
 *
 * Propósito: Listado detallado y cronológico de todas las sesiones de clase
 * programadas en `horario` + `carga_academica` con filtros por día de la semana.
 *
 * Historial:
 *  - 2026-11-15: Alineado a `horario(id_hor, dia_semana_hor, hora_inicio_hor, hora_fin_hor, tipo_sesion_hor)`,
 *    `carga_academica`, `unidad_curricular` (`nombre_unc`, `codigo_unc`), `docente_perfil.persona` y `aula.codigo_aula_aul`.
 */
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Calendar,
    Printer,
} from 'lucide-vue-next';
import {
    formatearHoraCorta,
    obtenerAulaEtiqueta,
    obtenerCodigoMateriaHorario,
    obtenerNombreDocenteCarga,
    obtenerNombreMateriaHorario,
    obtenerSeccionHorario,
} from '@/lib/academico';

const props = defineProps<{
    clases: {
        data: any[];
        total: number;
    };
    filters: {
        dia?: string | number;
    };
}>();

const diaFiltro = ref(props.filters.dia ?? '');

const filtrarDia = () => {
    router.get(route('informes.clases'), { dia: diaFiltro.value || undefined }, { preserveState: true });
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', subtitle: 'Secciones', href: route('seccion.index') },
        { title: 'Informes', subtitle: 'Programación', href: route('informes.secciones') },
        { title: 'Listado De Clases', subtitle: 'Clases Programadas', href: route('informes.clases') },
    ]">
        <Head title="Listado De Clases - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:hidden">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-500/10 rounded-lg text-blue-600">
                            <Calendar class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Listado General de Clases Programadas
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Reporte consolidado de bloques horarios, aulas asignadas y docentes por día.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <select
                        v-model="diaFiltro"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @change="filtrarDia"
                    >
                        <option value="">Todos los Días</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miercoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sabado">Sábado</option>
                    </select>

                    <Button variant="outline" @click="imprimir">
                        <Printer class="h-4 w-4 mr-2" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 border-b text-xs font-semibold text-muted-foreground uppercase">
                        <tr>
                            <th class="p-4">Día / Horario</th>
                            <th class="p-4">Materia / Unidad Curricular</th>
                            <th class="p-4">Sección</th>
                            <th class="p-4">Profesor Asignado</th>
                            <th class="p-4">Espacio / Aula</th>
                            <th class="p-4 text-center">Tipo Sesión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="c in props.clases.data" :key="c.id_hor ?? c.id_sho" class="hover:bg-muted/30">
                            <td class="p-4 font-mono font-medium">
                                <span class="font-bold text-foreground">{{ c.dia_semana_hor ?? c.dia_semana }}</span>
                                <div class="text-xs text-muted-foreground">
                                    {{ formatearHoraCorta(c.hora_inicio_hor ?? c.hora_inicio) }} - {{ formatearHoraCorta(c.hora_fin_hor ?? c.hora_fin) }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ obtenerNombreMateriaHorario(c) }}
                                </div>
                                <div class="text-xs text-muted-foreground font-mono">
                                    {{ obtenerCodigoMateriaHorario(c) }}
                                </div>
                            </td>
                            <td class="p-4 font-medium">
                                {{ obtenerSeccionHorario(c) }}
                            </td>
                            <td class="p-4 text-muted-foreground">
                                {{ obtenerNombreDocenteCarga(c.carga_academica ?? c.seccion_unidad_docente) }}
                            </td>
                            <td class="p-4 font-semibold text-primary">
                                {{ obtenerAulaEtiqueta(c.aula) }}
                            </td>
                            <td class="p-4 text-center">
                                <Badge variant="outline" class="text-xs">
                                    {{ c.tipo_sesion_hor || c.tipo_sesion || 'Teoría' }}
                                </Badge>
                            </td>
                        </tr>
                        <tr v-if="props.clases.data.length === 0">
                            <td colspan="6" class="p-8 text-center text-muted-foreground text-sm">
                                No se encontraron clases programadas para el criterio seleccionado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
