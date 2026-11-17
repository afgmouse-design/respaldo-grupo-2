<script setup lang="ts">
/**
 * Vista Standalone: Crear Tipo de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Registrar un nuevo tipo de período/lapso (`codigo_tla`, `nombre_tla`,
 * `duracion_semanas_tla`, `nomenclatura_tla`, `descripcion_tla`) alineado a `sogac_mio.sql`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a columnas reales de `tipo_lapso(id_tla)` y `StoreTipoLapsoRequest`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Clock } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Lapso', subtitle: 'Listado', href: route('tipo_lapso.index') },
    { title: 'Crear Tipo de Lapso', subtitle: 'Nuevo Registro', href: route('tipo_lapso.create') },
];

const form = useForm({
    codigo_tla: '',
    nombre_tla: '',
    duracion_semanas_tla: 18,
    nomenclatura_tla: '',
    descripcion_tla: '',
});

const submit = () => {
    form.post(route('tipo_lapso.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Tipo de Lapso - Programación Académica" />

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
                            Crear Tipo de Lapso Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina la clasificación y duración estándar en semanas para los lapsos académicos.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Lapso</CardTitle>
                    </div>
                    <CardDescription>
                        Complete el código único, nombre descriptivo y semanas de duración.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tla">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tla"
                                    v-model="form.codigo_tla"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ej. SEM, TRI, INT"
                                    :class="{ 'border-destructive': form.errors.codigo_tla }"
                                />
                                <span v-if="form.errors.codigo_tla" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tla">
                                    Nombre <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tla"
                                    v-model="form.nombre_tla"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Ej. Semestral Regular"
                                    :class="{ 'border-destructive': form.errors.nombre_tla }"
                                />
                                <span v-if="form.errors.nombre_tla" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="duracion_semanas_tla">
                                    Duración (Semanas) <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="duracion_semanas_tla"
                                    v-model.number="form.duracion_semanas_tla"
                                    type="number"
                                    min="1"
                                    max="104"
                                    :class="{ 'border-destructive': form.errors.duracion_semanas_tla }"
                                />
                                <span v-if="form.errors.duracion_semanas_tla" class="text-xs text-destructive">
                                    {{ form.errors.duracion_semanas_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nomenclatura_tla">
                                    Nomenclatura / Prefijo
                                </Label>
                                <Input
                                    id="nomenclatura_tla"
                                    v-model="form.nomenclatura_tla"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ej. S, T, INT"
                                    :class="{ 'border-destructive': form.errors.nomenclatura_tla }"
                                />
                                <span v-if="form.errors.nomenclatura_tla" class="text-xs text-destructive">
                                    {{ form.errors.nomenclatura_tla }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tla">
                                    Descripción
                                </Label>
                                <Input
                                    id="descripcion_tla"
                                    v-model="form.descripcion_tla"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Descripción o alcance académico del tipo de lapso..."
                                    :class="{ 'border-destructive': form.errors.descripcion_tla }"
                                />
                                <span v-if="form.errors.descripcion_tla" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tla }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('tipo_lapso.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
