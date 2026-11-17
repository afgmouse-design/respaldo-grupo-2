<script setup lang="ts">
/**
 * Vista Standalone: Editar Tipo de Sección (Programación Académica).
 *
 * Propósito: Modificación de un tipo de sección existente en PostgreSQL (`tipo_seccion`).
 *
 * Historial:
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Layers } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    tipoSeccion: {
        id_tis: number;
        codigo_tis: string;
        nombre_tis: string;
        descripcion_tis?: string | null;
        status?: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: route('seccion.index') },
    { title: 'Tipos de Sección', subtitle: 'Listado', href: route('tipo_seccion.index') },
    { title: 'Editar Tipo de Sección', subtitle: 'Actualización', href: route('tipo_seccion.edit', props.tipoSeccion.id_tis) },
];

const form = useForm({
    codigo_tis: props.tipoSeccion.codigo_tis || '',
    nombre_tis: props.tipoSeccion.nombre_tis || '',
    descripcion_tis: props.tipoSeccion.descripcion_tis || '',
});

const submit = () => {
    form.put(route('tipo_seccion.update', props.tipoSeccion.id_tis), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Tipo de Sección: ${tipoSeccion.nombre_tis} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_seccion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Tipo de Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los atributos de la naturaleza de sección seleccionada.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Layers class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Sección #{{ tipoSeccion.id_tis }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice los campos que considere necesarios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tis">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tis"
                                    v-model="form.codigo_tis"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.codigo_tis }"
                                />
                                <span v-if="form.errors.codigo_tis" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tis }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tis">
                                    Nombre del Tipo de Sección <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tis"
                                    v-model="form.nombre_tis"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_tis }"
                                />
                                <span v-if="form.errors.nombre_tis" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tis }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tis">Descripción y Criterios Operativos</Label>
                                <Textarea
                                    id="descripcion_tis"
                                    v-model="form.descripcion_tis"
                                    rows="3"
                                    :class="{ 'border-destructive': form.errors.descripcion_tis }"
                                />
                                <span v-if="form.errors.descripcion_tis" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tis }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('tipo_seccion.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

