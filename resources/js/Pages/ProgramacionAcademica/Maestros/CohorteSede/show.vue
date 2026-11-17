<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Cohorte por Sede (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta y detalle de la asignación de cohorte en sede.
 * Historial:
 *  - 2026-11-14: Alineado a `sede_programa(id_spr)`, `programa(id_prg)` y `status_id` (`esActivo`).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, MapPin, CheckCircle2, XCircle, GraduationCap, Building2 } from 'lucide-vue-next';
import { esActivo } from '@/lib/status';

interface SedeProgramaDetalle {
    id_spr?: number;
    id_sep?: number;
    sede?: { id_sed: number; nombre_sed: string };
    programa?: { id_prg: number; nombre_prg: string };
    pnf?: { id_pnf: number; nombre_pnf: string };
}

const props = defineProps<{
    cohorteSede: {
        id_cos: number;
        nombre_cos: string;
        status_id?: number;
        status?: boolean;
        cohorte?: { id_coh: number; nombre_coh: string };
        sede_programa?: SedeProgramaDetalle;
        sedePrograma?: SedeProgramaDetalle;
        sede_pnf?: SedeProgramaDetalle;
    };
}>();

const ofertaSedePrograma = computed(
    () => props.cohorteSede.sede_programa ?? props.cohorteSede.sedePrograma ?? props.cohorteSede.sede_pnf
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Cohortes por Sede', subtitle: 'Listado', href: route('cohorte_sede.index') },
    { title: 'Detalle de Asignación', subtitle: 'Consulta', href: route('cohorte_sede.show', props.cohorteSede.id_cos) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Cohorte por Sede: ${cohorteSede.nombre_cos} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('cohorte_sede.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Cohorte por Sede
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle y relaciones de la oferta académica por sede.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('cohorte_sede.edit', cohorteSede.id_cos)">
                            <Pencil class="h-4 w-4" />
                            <span>Editar</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <MapPin class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">{{ cohorteSede.nombre_cos }}</CardTitle>
                        </div>
                        <Badge :variant="esActivo(cohorteSede) ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="esActivo(cohorteSede)" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ esActivo(cohorteSede) ? 'Activa' : 'Inactiva' }}
                        </Badge>
                    </div>
                    <CardDescription>Código de asignación: #{{ cohorteSede.id_cos }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Cohorte Matriz</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <GraduationCap class="h-4 w-4 text-primary" />
                                {{ cohorteSede.cohorte?.nombre_coh || 'No asignada' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Sede Universitaria</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <Building2 class="h-4 w-4 text-primary" />
                                {{ ofertaSedePrograma?.sede?.nombre_sed || 'No asignada' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Programa Académico</span>
                            <p class="text-base font-medium text-slate-900 dark:text-slate-100 mt-1">
                                {{ ofertaSedePrograma?.programa?.nombre_prg || ofertaSedePrograma?.pnf?.nombre_pnf || 'No asignado' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('cohorte_sede.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

