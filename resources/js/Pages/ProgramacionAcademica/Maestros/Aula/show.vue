<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Aula (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta del aula y su sede/tipo.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, School, CheckCircle2, XCircle, MapPin, Users } from 'lucide-vue-next';

const props = defineProps<{
    aula: {
        id_aul: number;
        codigo_aula_aul: string;
        capacidad_maxima_aul: number;
        tipo_espacio_aul?: string | null;
        status?: boolean;
        sede?: { id_sed: number; nombre_sed: string };
        tipo_aula?: { id_tia: number; nombre_tia: string };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Aulas', subtitle: 'Listado', href: route('aula.index') },
    { title: 'Detalle de Aula', subtitle: 'Consulta', href: route('aula.show', props.aula.id_aul) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Aula: ${aula.codigo_aula_aul} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('aula.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalles operativos y capacidad del aula física.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('aula.edit', aula.id_aul)">
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
                            <School class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">Aula {{ aula.codigo_aula_aul }}</CardTitle>
                        </div>
                        <Badge :variant="aula.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="aula.status" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ aula.status ? 'Habilitada' : 'Inactiva' }}
                        </Badge>
                    </div>
                    <CardDescription>Identificador interno: #{{ aula.id_aul }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Sede Universitaria</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <MapPin class="h-4 w-4 text-primary" />
                                {{ aula.sede?.nombre_sed || 'No asignada' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Tipo de Aula</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ aula.tipo_aula?.nombre_tia || 'Ambiente General' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Capacidad de Estudiantes</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <Users class="h-4 w-4 text-emerald-600" />
                                {{ aula.capacidad_maxima_aul }} puestos
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Ubicación / Módulo</span>
                            <p class="text-base font-medium mt-1">
                                {{ aula.tipo_espacio_aul || 'No especificada' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('aula.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

