<script setup lang="ts">
/**
 * Vista Standalone: Registrar Tipo de Sección (Programación Académica).
 *
 * Propósito: Permite registrar una nueva naturaleza o régimen de sección en PostgreSQL (`tipo_seccion`).
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Layers } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: route('seccion.index') },
    { title: 'Tipos de Sección', subtitle: 'Listado', href: route('tipo_seccion.index') },
    { title: 'Registrar Tipo de Sección', subtitle: 'Nuevo Registro', href: route('tipo_seccion.create') },
];

const form = useForm({
    codigo_tis: '',
    nombre_tis: '',
    descripcion_tis: '',
});

const submit = () => {
    form.post(route('tipo_seccion.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tipo de Sección - Programación Académica" />

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
                            Registrar Tipo de Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una nueva naturaleza pedagógica o administrativa para clasificar las secciones (Regular, PER, Repitiente...).
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Layers class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Sección</CardTitle>
                    </div>
                    <CardDescription>
                        Complete la información requerida para el registro en la tabla `tipo_seccion`.
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
                                    placeholder="Ej. REG, PER, REP, INT..."
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
                                    placeholder="Ej. Plan Especial de Referencia (PER)"
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
                                    placeholder="Criterios de asignación, prelación y administración de la sección..."
                                    :class="{ 'border-destructive': form.errors.descripcion_tis }"
                                />
                                <span v-if="form.errors.descripcion_tis" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tis }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('tipo_seccion.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Tipo de Sección' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

