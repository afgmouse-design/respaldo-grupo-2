<script setup lang="ts">
/**
 * Vista Standalone: Creación de Eje Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite registrar un nuevo Eje Curricular mediante useForm de Inertia,
 * siguiendo la arquitectura de diseño y componentes de PNF / Trayecto / Sede.
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
    Layers,
    ArrowLeft,
    Save,
    AlertCircle,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Ejes Curriculares', subtitle: 'Áreas Formativas', href: route('academico.eje-curricular.index') },
    { title: 'Registrar Eje', subtitle: 'Nuevo Registro', href: route('academico.eje-curricular.create') },
];

const form = useForm({
    codigo_ecu: '',
    nombre_ecu: '',
    descripcion_ecu: '',
});

const submit = () => {
    form.post(route('academico.eje-curricular.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Eje Curricular - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.eje-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nuevo Eje Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una línea de formación o eje integrador para articular unidades de aprendizaje.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Layers class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Información del Eje Formativo</CardTitle>
                    </div>
                    <CardDescription>
                        Complete los datos del eje curricular. Los campos con (*) son obligatorios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- CÓDIGO DEL EJE -->
                        <div class="space-y-2">
                            <Label for="codigo_ecu" class="text-sm font-medium">
                                Código del Eje Curricular (Opcional)
                            </Label>
                            <Input
                                id="codigo_ecu"
                                v-model="form.codigo_ecu"
                                type="text"
                                placeholder="Ej: EJE-PROY, EJE-SOCIO, EPIST-01"
                                :class="{ 'border-destructive': form.errors.codigo_ecu }"
                            />
                            <p v-if="form.errors.codigo_ecu" class="text-xs text-destructive">
                                {{ form.errors.codigo_ecu }}
                            </p>
                        </div>

                        <!-- NOMBRE DEL EJE -->
                        <div class="space-y-2">
                            <Label for="nombre_ecu" class="text-sm font-medium">
                                Nombre del Eje Curricular <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="nombre_ecu"
                                v-model="form.nombre_ecu"
                                type="text"
                                placeholder="Ej: Proyecto Sociotecnológico, Epistemológico, Ético-Político"
                                :class="{ 'border-destructive': form.errors.nombre_ecu }"
                                required
                            />
                            <p v-if="form.errors.nombre_ecu" class="text-xs text-destructive">
                                {{ form.errors.nombre_ecu }}
                            </p>
                        </div>

                        <!-- DESCRIPCIÓN DEL EJE -->
                        <div class="space-y-2">
                            <Label for="descripcion_ecu" class="text-sm font-medium">
                                Descripción o Alcance del Eje (Opcional)
                            </Label>
                            <textarea
                                id="descripcion_ecu"
                                v-model="form.descripcion_ecu"
                                rows="4"
                                placeholder="Describa el propósito formativo, competencias o alcance temático del eje..."
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring placeholder:text-muted-foreground"
                                :class="{ 'border-destructive': form.errors.descripcion_ecu }"
                            ></textarea>
                            <p v-if="form.errors.descripcion_ecu" class="text-xs text-destructive">
                                {{ form.errors.descripcion_ecu }}
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.eje-curricular.index')">
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

