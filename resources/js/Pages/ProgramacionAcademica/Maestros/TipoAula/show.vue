<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Tipo de Aula (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta y detalle de un tipo de aula registrado.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, DoorClosed, CheckCircle2, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    tipoAula: {
        id_tia: number;
        codigo_tia: string;
        nombre_tia: string;
        descripcion_tia?: string | null;
        status?: boolean;
        created_at?: string;
        updated_at?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Aula', subtitle: 'Listado', href: route('tipo_aula.index') },
    { title: 'Detalle de Tipo de Aula', subtitle: 'Consulta', href: route('tipo_aula.show', props.tipoAula.id_tia) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Tipo de Aula: ${tipoAula.nombre_tia} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_aula.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Ficha de Tipo de Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Detalle y características del ambiente de aprendizaje.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('tipo_aula.edit', tipoAula.id_tia)">
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
                            <DoorClosed class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">{{ tipoAula.nombre_tia }}</CardTitle>
                        </div>
                        <Badge :variant="tipoAula.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="tipoAula.status" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ tipoAula.status ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <CardDescription>Código de registro: #{{ tipoAula.id_tia }}</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Código</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ tipoAula.codigo_tia }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Denominación</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                {{ tipoAula.nombre_tia }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Estatus Operativo</span>
                            <p class="text-base font-medium mt-1">
                                {{ tipoAula.status ? 'Disponible para asignación de aulas' : 'Inactivo' }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Descripción y Equipamiento</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ tipoAula.descripcion_tia || 'Sin descripción adicional.' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('tipo_aula.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

