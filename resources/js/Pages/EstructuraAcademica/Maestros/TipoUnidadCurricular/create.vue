<script setup lang="ts">
/**
 * Vista Standalone: Creación de Tipo de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite registrar una nueva tipología de unidad curricular mediante useForm de Inertia,
 * siguiendo la arquitectura de diseño y componentes de Trayecto / Sede.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    BookOpen,
    ArrowLeft,
    Save,
    AlertCircle,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipo de Unidad Curricular', subtitle: 'Clasificación', href: route('academico.tipo-unidad-curricular.index') },
    { title: 'Registrar Tipo de UC', subtitle: 'Nuevo Registro', href: route('academico.tipo-unidad-curricular.create') },
];

const form = useForm({
    nombre_tuc: '',
});

const submit = () => {
    form.post(route('academico.tipo-unidad-curricular.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tipo de Unidad Curricular - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.tipo-unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Tipo de Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una nueva clasificación o categoría para las unidades de aprendizaje.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Información de la Tipología Curricular</CardTitle>
                    </div>
                    <CardDescription>
                        Ingrese el nombre de la tipología formativa. Los campos con (*) son obligatorios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- NOMBRE DE LA TIPOLOGÍA -->
                        <div class="space-y-2">
                            <Label for="nombre_tuc" class="text-sm font-medium">
                                Nombre del Tipo de Unidad Curricular <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="nombre_tuc"
                                v-model="form.nombre_tuc"
                                type="text"
                                placeholder="Ej: Proyecto, Obligatoria, Electiva, Acreditable, Taller"
                                :class="{ 'border-destructive': form.errors.nombre_tuc }"
                                required
                            />
                            <p v-if="form.errors.nombre_tuc" class="text-xs text-destructive">
                                {{ form.errors.nombre_tuc }}
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.tipo-unidad-curricular.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
