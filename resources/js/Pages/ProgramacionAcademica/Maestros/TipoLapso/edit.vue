<script setup lang="ts">
/**
 * Vista Standalone: Editar Tipo de Lapso (Programación Académica).
 *
 * Propósito: Modificación de un tipo de lapso académico mediante navegación directa SPA.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Calendar } from 'lucide-vue-next';

const props = defineProps<{
    tipoLapso: {
        id: number;
        tap_codigo: number | string;
        tap_nombre: string;
        tap_estatus?: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Lapso', subtitle: 'Listado', href: route('tipo_lapso.index') },
    { title: 'Editar Tipo de Lapso', subtitle: 'Actualización', href: route('tipo_lapso.edit', props.tipoLapso.id) },
];

const form = useForm({
    tap_codigo: props.tipoLapso.tap_codigo,
    tap_nombre: props.tipoLapso.tap_nombre || '',
});

const submit = () => {
    form.put(route('tipo_lapso.update', props.tipoLapso.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Tipo de Lapso: ${tipoLapso.tap_nombre} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_lapso.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Tipo de Lapso
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los datos del tipo de lapso académico.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Lapso #{{ tipoLapso.id }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice el código y/o nombre asignado a este lapso.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="tap_codigo">
                                    Código del Lapso <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="tap_codigo"
                                    v-model="form.tap_codigo"
                                    type="number"
                                    :class="{ 'border-destructive': form.errors.tap_codigo }"
                                />
                                <span v-if="form.errors.tap_codigo" class="text-xs text-destructive">
                                    {{ form.errors.tap_codigo }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="tap_nombre">
                                    Nombre del Lapso <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="tap_nombre"
                                    v-model="form.tap_nombre"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.tap_nombre }"
                                />
                                <span v-if="form.errors.tap_nombre" class="text-xs text-destructive">
                                    {{ form.errors.tap_nombre }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('tipo_lapso.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

