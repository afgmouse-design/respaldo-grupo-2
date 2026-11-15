<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Eje Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Ficha técnica y de consulta completa de un Eje Curricular registrado en el SOGAC.
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Layers,
    ArrowLeft,
    Pencil,
    CheckCircle2,
    XCircle,
    FileText,
    Info,
} from 'lucide-vue-next';

interface EjeCurricularItem {
    id_ecu: number;
    codigo_ecu: string | null;
    nombre_ecu: string;
    descripcion_ecu: string | null;
    status_id?: number;
    status?: boolean;
}

const props = defineProps<{
    eje: EjeCurricularItem;
}>();

const esActivo = computed(() => {
    return props.eje.status ?? (props.eje.status_id === 1);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Ejes Curriculares', subtitle: 'Áreas Formativas', href: route('academico.eje-curricular.index') },
    { title: `Ficha: ${props.eje.nombre_ecu}`, subtitle: 'Detalle', href: route('academico.eje-curricular.show', props.eje.id_ecu) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Eje Curricular: ${eje.nombre_ecu} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.eje-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Eje Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Área de formación transversal de las mallas y unidades curriculares.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.eje-curricular.edit', eje.id_ecu)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Eje</span>
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
                                <Layers class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ eje.nombre_ecu }}
                                </CardTitle>
                                <CardDescription>Código de registro: #{{ eje.id_ecu }}</CardDescription>
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
                                <FileText class="h-3.5 w-3.5" /> Código del Eje
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                {{ eje.codigo_ecu || 'No asignado' }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Layers class="h-3.5 w-3.5" /> Denominación
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ eje.nombre_ecu }}
                            </p>
                        </div>

                        <div class="md:col-span-2 space-y-1 border-t pt-3 dark:border-slate-800">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <Info class="h-3.5 w-3.5" /> Descripción / Alcance
                            </span>
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                {{ eje.descripcion_ecu || 'Sin descripción registrada.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

