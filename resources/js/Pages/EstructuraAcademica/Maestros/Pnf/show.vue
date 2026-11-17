<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Programa / PNF (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Ficha técnica completa de un programa registrado en `programa` (`id_prg`, `status_id`),
 * mostrando sus datos ministeriales, clasificación académica y mallas curriculares asociadas.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`programa`, `id_prg`, `status_id`, relaciones
 *    `tipoPrograma`, `regimenEstudio`, `modalidadEstudio`, `escalaMedicion`) usando `@/lib/status`.
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
    GraduationCap,
    ArrowLeft,
    Pencil,
    Network,
    CheckCircle2,
    XCircle,
    FileText,
    Eye,
} from 'lucide-vue-next';

interface MallaItem {
    id_mac: number;
    nombre_mac: string;
    codigo_gaceta_mac?: string | null;
    fecha_aprobacion_mac?: string | null;
    status?: boolean;
    status_id?: number;
}

interface ProgramaItem {
    id_prg: number;
    codigo_prg?: string | null;
    codigo_ministerio_prg?: string | null;
    nombre_prg: string;
    siglas_prg?: string | null;
    titulo_prg?: string | null;
    intermedia_prg?: string | null;
    status_id?: number;
    status?: boolean;
    tipo_programa?: { id_tpg: number; nombre_tpg: string } | null;
    regimen_estudio?: { id_ree: number; nombre_ree: string } | null;
    modalidad_estudio?: { id_moe: number; nombre_moe: string } | null;
    escala_medicion?: { id_esm: number; nombre_esm: string } | null;
    mallas?: MallaItem[] | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    programa?: ProgramaItem;
    pnf?: ProgramaItem;
}>();

const programaActual = computed<ProgramaItem>(() => (props.programa ?? props.pnf) as ProgramaItem);
const esActivo = computed(() => esRegistroActivo(programaActual.value));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'PNF / Programas', subtitle: 'Oferta Académica', href: route('academico.pnf.index') },
    { title: `Ficha: ${programaActual.value.nombre_prg}`, subtitle: 'Detalle', href: route('academico.pnf.show', programaActual.value.id_prg) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Programa: ${programaActual.nombre_prg} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO SUPERIOR -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.pnf.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Programa de Formación
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle del programa académico y planes de estudio asociados.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.pnf.edit', programaActual.id_prg)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Programa</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHA PRINCIPAL -->
            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
                                <GraduationCap class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ programaActual.nombre_prg }}
                                </CardTitle>
                                <CardDescription>Código interno: #{{ programaActual.id_prg }}</CardDescription>
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
                    <!-- DATOS BÁSICOS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="h-3.5 w-3.5" /> Código Interno / Ministerial
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                {{ programaActual.codigo_prg || programaActual.codigo_ministerio_prg || 'No asignado' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <GraduationCap class="h-3.5 w-3.5" /> Nombre del Programa
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ programaActual.nombre_prg }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Siglas / Código Ministerio</span>
                            <p class="text-sm font-mono text-slate-800 dark:text-slate-200">
                                {{ programaActual.siglas_prg || 'N/A' }}
                                <span v-if="programaActual.codigo_ministerio_prg" class="text-muted-foreground">
                                    · Min: {{ programaActual.codigo_ministerio_prg }}
                                </span>
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Título que Otorga</span>
                            <p class="text-sm text-slate-800 dark:text-slate-200">
                                {{ programaActual.titulo_prg || 'No especificado' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Tipo / Régimen</span>
                            <p class="text-sm text-slate-800 dark:text-slate-200">
                                {{ programaActual.tipo_programa?.nombre_tpg || 'N/A' }} · {{ programaActual.regimen_estudio?.nombre_ree || 'N/A' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Modalidad / Escala</span>
                            <p class="text-sm text-slate-800 dark:text-slate-200">
                                {{ programaActual.modalidad_estudio?.nombre_moe || 'N/A' }} · {{ programaActual.escala_medicion?.nombre_esm || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- MALLAS CURRICULARES ASOCIADAS -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Network class="h-5 w-5 text-primary" />
                                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                    Mallas Curriculares Vinculadas
                                </h3>
                            </div>
                            <span v-if="programaActual.mallas && programaActual.mallas.length > 0" class="text-xs bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-md text-slate-700 dark:text-slate-300 font-medium">
                                {{ programaActual.mallas.length }} malla(s) registrada(s)
                            </span>
                        </div>

                        <div v-if="programaActual.mallas && programaActual.mallas.length > 0" class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5">Nombre de la Malla</th>
                                        <th class="px-4 py-2.5">Gaceta</th>
                                        <th class="px-4 py-2.5">Fecha</th>
                                        <th class="px-4 py-2.5 text-center">Estado</th>
                                        <th class="px-4 py-2.5 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="malla in programaActual.mallas" :key="malla.id_mac" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">
                                            {{ malla.nombre_mac }}
                                        </td>
                                        <td class="px-4 py-2.5 font-mono text-muted-foreground">
                                            {{ malla.codigo_gaceta_mac || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-muted-foreground">
                                            {{ malla.fecha_aprobacion_mac || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <Badge :variant="esRegistroActivo(malla) ? 'default' : 'secondary'" class="text-[10px] py-0.5">
                                                {{ esRegistroActivo(malla) ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <Button as-child variant="ghost" size="icon" class="h-7 w-7 text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                <Link :href="route('academico.malla-curricular.show', malla.id_mac)">
                                                    <Eye class="h-3.5 w-3.5" />
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-8 px-4 border border-dashed rounded-lg border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <Network class="h-8 w-8 text-muted-foreground mx-auto mb-2 opacity-50" />
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                No hay mallas curriculares asociadas a este programa
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
