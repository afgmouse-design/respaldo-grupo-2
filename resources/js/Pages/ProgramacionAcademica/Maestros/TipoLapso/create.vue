<script setup lang="ts">
/**
 * Vista Standalone: Registrar Tipo de Lapso (Programación Académica).
 *
 * Propósito: Registro de nueva tipología de lapso académico mediante navegación directa SPA.
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Lapso', subtitle: 'Listado', href: route('tipo_lapso.index') },
    { title: 'Registrar Tipo de Lapso', subtitle: 'Nuevo Registro', href: route('tipo_lapso.create') },
];

const form = useForm({
    tap_codigo: '',
    tap_nombre: '',
});

const submit = () => {
    form.post(route('tipo_lapso.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tipo de Lapso - Programación Académica" />

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
                            Registrar Tipo de Lapso
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Cree un nuevo tipo de lapso para la programación académica.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Lapso</CardTitle>
                    </div>
                    <CardDescription>
                        Ingrese el código identificador y la denominación del lapso.
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
                                    placeholder="Ej. 1"
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
                                    placeholder="Ej. Semestre I, Trimestre Regular..."
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
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Tipo de Lapso' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

