<script setup lang="ts">
/**
 * Vista Standalone: Registrar Aula (Programación Académica).
 *
 * Propósito: Permite dar de alta una nueva aula o ambiente físico en una sede mediante navegación directa SPA.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, School } from 'lucide-vue-next';

defineProps<{
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    tiposAula: Array<{ id_tia: number; nombre_tia: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Aulas', subtitle: 'Listado', href: route('aula.index') },
    { title: 'Registrar Aula', subtitle: 'Nuevo Registro', href: route('aula.create') },
];

const form = useForm({
    codigo_aula_aul: '',
    capacidad_maxima_aul: 30,
    tipo_espacio_aul: '',
    sed_id: '',
    tia_id: '',
});

const submit = () => {
    form.post(route('aula.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Aula - Programación Académica" />

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
                            Registrar Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Registre un nuevo espacio o ambiente físico asignado a una sede universitaria.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <School class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Aula</CardTitle>
                    </div>
                    <CardDescription>
                        Complete los campos obligatorios del ambiente académico.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="sed_id">
                                    Sede Universitaria <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="sed_id"
                                    v-model="form.sed_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.sed_id }"
                                >
                                    <option value="" disabled>Seleccione una sede...</option>
                                    <option v-for="s in sedes" :key="s.id_sed" :value="s.id_sed">
                                        {{ s.nombre_sed }}
                                    </option>
                                </select>
                                <span v-if="form.errors.sed_id" class="text-xs text-destructive">
                                    {{ form.errors.sed_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="tia_id">
                                    Tipo de Aula <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="tia_id"
                                    v-model="form.tia_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.tia_id }"
                                >
                                    <option value="" disabled>Seleccione el tipo de ambiente...</option>
                                    <option v-for="t in tiposAula" :key="t.id_tia" :value="t.id_tia">
                                        {{ t.nombre_tia }}
                                    </option>
                                </select>
                                <span v-if="form.errors.tia_id" class="text-xs text-destructive">
                                    {{ form.errors.tia_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="codigo_aula_aul">
                                    Código del Aula <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_aula_aul"
                                    v-model="form.codigo_aula_aul"
                                    type="text"
                                    placeholder="Ej. A-101, LAB-02..."
                                    :class="{ 'border-destructive': form.errors.codigo_aula_aul }"
                                />
                                <span v-if="form.errors.codigo_aula_aul" class="text-xs text-destructive">
                                    {{ form.errors.codigo_aula_aul }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="capacidad_maxima_aul">
                                    Capacidad Máxima (Estudiantes) <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="capacidad_maxima_aul"
                                    v-model="form.capacidad_maxima_aul"
                                    type="number"
                                    min="1"
                                    :class="{ 'border-destructive': form.errors.capacidad_maxima_aul }"
                                />
                                <span v-if="form.errors.capacidad_maxima_aul" class="text-xs text-destructive">
                                    {{ form.errors.capacidad_maxima_aul }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="tipo_espacio_aul">Tipo de Espacio / Ubicación</Label>
                                <Input
                                    id="tipo_espacio_aul"
                                    v-model="form.tipo_espacio_aul"
                                    type="text"
                                    placeholder="Ej. Módulo B, Segundo Piso, Edificio Central..."
                                    :class="{ 'border-destructive': form.errors.tipo_espacio_aul }"
                                />
                                <span v-if="form.errors.tipo_espacio_aul" class="text-xs text-destructive">
                                    {{ form.errors.tipo_espacio_aul }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('aula.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Aula' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

