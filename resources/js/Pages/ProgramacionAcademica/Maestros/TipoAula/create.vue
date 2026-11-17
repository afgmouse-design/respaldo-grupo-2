<script setup lang="ts">
/**
 * Vista Standalone: Registrar Tipo de Aula (Programación Académica).
 *
 * Propósito: Permite crear una nueva tipología de aula o ambiente físico mediante navegación directa SPA.
 *
 * Historial:
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, DoorClosed } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Aula', subtitle: 'Listado', href: route('tipo_aula.index') },
    { title: 'Registrar Tipo de Aula', subtitle: 'Nuevo Registro', href: route('tipo_aula.create') },
];

const form = useForm({
    codigo_tia: '',
    nombre_tia: '',
    descripcion_tia: '',
});

const submit = () => {
    form.post(route('tipo_aula.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tipo de Aula - Programación Académica" />

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
                            Registrar Tipo de Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una nueva categoría de ambiente de aprendizaje (Laboratorio, Aula Magistral, Taller...).
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <DoorClosed class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Aula</CardTitle>
                    </div>
                    <CardDescription>
                        Complete la información requerida para el registro.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tia">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tia"
                                    v-model="form.codigo_tia"
                                    type="text"
                                    placeholder="Ej. LAB, TEOR, AUD..."
                                    :class="{ 'border-destructive': form.errors.codigo_tia }"
                                />
                                <span v-if="form.errors.codigo_tia" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tia }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tia">
                                    Nombre del Tipo de Aula <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tia"
                                    v-model="form.nombre_tia"
                                    type="text"
                                    placeholder="Ej. Laboratorio de Computación"
                                    :class="{ 'border-destructive': form.errors.nombre_tia }"
                                />
                                <span v-if="form.errors.nombre_tia" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tia }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tia">Descripción</Label>
                                <Textarea
                                    id="descripcion_tia"
                                    v-model="form.descripcion_tia"
                                    rows="3"
                                    placeholder="Características y equipamiento habitual..."
                                    :class="{ 'border-destructive': form.errors.descripcion_tia }"
                                />
                                <span v-if="form.errors.descripcion_tia" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tia }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('tipo_aula.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

