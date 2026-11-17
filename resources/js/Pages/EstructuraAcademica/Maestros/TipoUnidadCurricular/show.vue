<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Tipo de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Ficha técnica y de consulta completa de una clasificación de Unidad Curricular.
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
    Bookmark,
} from 'lucide-vue-next';

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
    status_id?: number;
    status?: boolean;
}

const props = defineProps<{
    tipo: TipoUnidadCurricularItem;
}>();

const esActivo = computed(() => esRegistroActivo(props.tipo));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipo de Unidad Curricular', subtitle: 'Clasificación', href: route('academico.tipo-unidad-curricular.index') },
    { title: `Ficha: ${props.tipo.nombre_tuc}`, subtitle: 'Detalle', href: route('academico.tipo-unidad-curricular.show', props.tipo.id_tuc) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Tipo de UC: ${tipo.nombre_tuc} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.tipo-unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Tipo de Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Clasificación tipológica y pedagógica de materias académicas.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('academico.tipo-unidad-curricular.edit', tipo.id_tuc)">
                            <Pencil class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            <span>Editar Tipo</span>
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
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ tipo.nombre_tuc }}
                                </CardTitle>
                                <CardDescription>Código de registro: #{{ tipo.id_tuc }}</CardDescription>
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
                                <Bookmark class="h-3.5 w-3.5" /> ID Identificador
                            </span>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">
                                #{{ tipo.id_tuc }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                                <BookOpen class="h-3.5 w-3.5" /> Denominación
                            </span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                {{ tipo.nombre_tuc }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

