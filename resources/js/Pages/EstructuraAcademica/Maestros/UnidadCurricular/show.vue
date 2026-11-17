<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Ficha técnica completa de una unidad curricular (`unidad_curricular`), mostrando
 * su clasificación, asignaciones reales en `malla_curricular_detalle` y prelaciones en `regla_prelacion`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`status_id` con `@/lib/status`, `codigo_anterior_unc`,
 *    `detalles_malla` y `prelaciones_exigidas` de `regla_prelacion`).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { esActivo as esRegistroActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    BookOpen,
    ArrowLeft,
    Pencil,
    CheckCircle2,
    XCircle,
    FileText,
    Tag,
    Layers,
    GitBranch,
} from 'lucide-vue-next';

interface DetalleMallaItem {
    id_mcd: number;
    semestre_mcd?: number | null;
    etapa_curricular_mcd?: string | null;
    unidades_credito_mcd?: number | null;
    htea_mcd?: number | null;
    htei_mcd?: number | null;
    thte_mcd?: number | null;
    trayecto?: { id_tra: number; nombre_tra: string } | null;
}

interface PrelacionItem {
    id_rep: number;
    tipo_requisito_rep?: string | null;
    condicion_unidades_credito_rep?: number | null;
    unidad_origen?: { id_unc: number; codigo_unc?: string | null; nombre_unc: string } | null;
}

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc: string | null;
    codigo_anterior_unc?: string | null;
    nombre_unc: string;
    tuc_id: number;
    tipo_unidad_curricular?: { id_tuc: number; nombre_tuc: string } | null;
    detalles_malla?: DetalleMallaItem[];
    prelaciones_exigidas?: PrelacionItem[];
    status_id?: number;
    status?: boolean;
}

const props = defineProps<{
    unidad: UnidadCurricularItem;
}>();

const esActivo = computed(() => esRegistroActivo(props.unidad));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Unidades Curriculares', subtitle: 'Materias y Asignaturas', href: route('academico.unidad-curricular.index') },
    { title: `Ficha: ${props.unidad.nombre_unc}`, subtitle: 'Detalle', href: route('academico.unidad-curricular.show', props.unidad.id_unc) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Materia: ${unidad.nombre_unc} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle maestro de la asignatura, asignaciones en mallas y prelaciones normativas.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.unidad-curricular.edit', unidad.id_unc)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Materia</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ unidad.nombre_unc }}
                                </CardTitle>
                                <CardDescription>Código interno: #{{ unidad.id_unc }}</CardDescription>
                            </div>
                        </div>

                        <Badge :variant="esActivo ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3 w-fit">
                            <CheckCircle2 v-if="esActivo" class="h-3.5 w-3.5 text-emerald-500" />
                            <XCircle v-else class="h-3.5 w-3.5 text-slate-400" />
                            {{ esActivo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="h-3.5 w-3.5" /> Código Actual
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                {{ unidad.codigo_unc || 'No asignado' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="h-3.5 w-3.5" /> Código Anterior
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                {{ unidad.codigo_anterior_unc || 'N/A' }}
                            </p>
                        </div>

                        <div class="space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <BookOpen class="h-3.5 w-3.5" /> Nombre de la Asignatura
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ unidad.nombre_unc }}
                            </p>
                        </div>

                        <div class="space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Tag class="h-3.5 w-3.5" /> Tipo de Unidad Curricular
                            </span>
                            <p class="text-base font-medium text-slate-800 dark:text-slate-200">
                                {{ unidad.tipo_unidad_curricular?.nombre_tuc || 'No clasificada' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <Layers class="h-5 w-5 text-primary" />
                            <h3 class="text-base font-semibold">Carga Curricular por Malla (`malla_curricular_detalle`)</h3>
                        </div>
                        <div v-if="unidad.detalles_malla && unidad.detalles_malla.length > 0" class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-muted/60 font-semibold uppercase">
                                    <tr>
                                        <th class="px-4 py-2">Trayecto</th>
                                        <th class="px-4 py-2 text-center">Semestre</th>
                                        <th class="px-4 py-2 text-center">Etapa</th>
                                        <th class="px-4 py-2 text-center">UC</th>
                                        <th class="px-4 py-2 text-center">HTEA</th>
                                        <th class="px-4 py-2 text-center">HTEI</th>
                                        <th class="px-4 py-2 text-center">THTE</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="det in unidad.detalles_malla" :key="det.id_mcd">
                                        <td class="px-4 py-2">{{ det.trayecto?.nombre_tra || 'Sin trayecto' }}</td>
                                        <td class="px-4 py-2 text-center">{{ det.semestre_mcd ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center">{{ det.etapa_curricular_mcd || '-' }}</td>
                                        <td class="px-4 py-2 text-center font-semibold">{{ det.unidades_credito_mcd ?? 0 }}</td>
                                        <td class="px-4 py-2 text-center">{{ det.htea_mcd ?? 0 }}h</td>
                                        <td class="px-4 py-2 text-center">{{ det.htei_mcd ?? 0 }}h</td>
                                        <td class="px-4 py-2 text-center font-semibold">{{ det.thte_mcd ?? 0 }}h</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">
                            Esta unidad curricular aún no ha sido asignada a ningún detalle de malla.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <GitBranch class="h-5 w-5 text-primary" />
                            <h3 class="text-base font-semibold">Prelaciones Exigidas (`regla_prelacion`)</h3>
                        </div>
                        <div v-if="unidad.prelaciones_exigidas && unidad.prelaciones_exigidas.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="pre in unidad.prelaciones_exigidas" :key="pre.id_rep" variant="outline" class="gap-1.5 py-1 px-3">
                                <span class="font-mono font-semibold">{{ pre.unidad_origen?.codigo_unc || 'UC' }}</span>
                                <span>{{ pre.unidad_origen?.nombre_unc }}</span>
                                <span v-if="pre.tipo_requisito_rep" class="text-muted-foreground">({{ pre.tipo_requisito_rep }})</span>
                            </Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">No registra prelaciones exigidas.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
