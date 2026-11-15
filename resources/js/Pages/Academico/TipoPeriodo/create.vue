<script setup lang="ts">
/**
 * Vista Standalone: Creación de Tipo de Período (Módulo: Estructura Académica).
 *
 * Propósito: Página independiente con formulario para registrar un nuevo tipo de período
 * académico en el sistema SOGAC. Cumple con la arquitectura Zero-Modal.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Calendar,
    ArrowLeft,
    Save,
    AlertCircle,
    Clock,
    FileText,
    Tag,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipos de Período', subtitle: 'Gestión de Tipos de Período', href: route('academico.tipo-periodo.index') },
    { title: 'Crear Tipo de Período', subtitle: 'Nuevo Registro', href: route('academico.tipo-periodo.create') },
];

const form = useForm({
    nombre_tpd: '',
    codigo_tpd: '',
    nomenclatura: 'Semestre',
    duracion_semanas_tpd: null as number | null,
    descripcion_tpd: '',
    status: true,
});

const submit = () => {
    form.post(route('academico.tipo-periodo.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Tipo de Período - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                            <Link :href="route('academico.tipo-periodo.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Crear Tipo de Período
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Configure la modalidad y duración reglamentaria del nuevo período académico.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE DENTRO DE CARD -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Información General</CardTitle>
                            <CardDescription>
                                Complete los campos requeridos para habilitar esta modalidad académica en los trayectos.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-5 pt-2">
                        <!-- MENSAJE DE ERROR GLOBAL -->
                        <div
                            v-if="form.errors.error"
                            class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- FILA 1: NOMBRE, CÓDIGO Y NOMENCLATURA -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- CAMPO: NOMBRE DEL TIPO DE PERÍODO -->
                            <div class="space-y-2">
                                <Label for="nombre_tpd" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nombre del Tipo de Período <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_tpd"
                                    v-model="form.nombre_tpd"
                                    type="text"
                                    placeholder="Ej. Semestral, Anual, Trimestral"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nombre_tpd }"
                                    required
                                />
                                <p v-if="form.errors.nombre_tpd" class="text-xs text-red-500">
                                    {{ form.errors.nombre_tpd }}
                                </p>
                            </div>

                            <!-- CAMPO: CÓDIGO DEL TIPO DE PERÍODO -->
                            <div class="space-y-2">
                                <Label for="codigo_tpd" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Código Corto
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="codigo_tpd"
                                        v-model="form.codigo_tpd"
                                        type="text"
                                        placeholder="Ej. SEM, TRA, TRI"
                                        maxlength="30"
                                        class="h-10 uppercase font-mono"
                                        :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.codigo_tpd }"
                                    />
                                </div>
                                <p v-if="form.errors.codigo_tpd" class="text-xs text-red-500">
                                    {{ form.errors.codigo_tpd }}
                                </p>
                            </div>

                            <!-- CAMPO: NOMENCLATURA (SEMESTRE / TRIMESTRE / ANUAL) -->
                            <div class="space-y-2">
                                <Label for="nomenclatura" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nomenclatura Visual <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="nomenclatura"
                                    v-model="form.nomenclatura"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :class="{ 'border-red-500': form.errors.nomenclatura }"
                                    required
                                >
                                    <option value="" disabled>Seleccionar nomenclatura...</option>
                                    <option value="Semestre">Semestre</option>
                                    <option value="Trimestre">Trimestre</option>
                                    <option value="Anual">Anual</option>
                                </select>
                                <p v-if="form.errors.nomenclatura" class="text-xs text-red-500">
                                    {{ form.errors.nomenclatura }}
                                </p>
                            </div>
                        </div>

                        <!-- FILA 2: DURACIÓN EN SEMANAS -->
                        <div class="space-y-2">
                            <Label for="duracion_semanas_tpd" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                Duración en Semanas
                            </Label>
                            <Input
                                id="duracion_semanas_tpd"
                                v-model.number="form.duracion_semanas_tpd"
                                type="number"
                                min="1"
                                max="100"
                                placeholder="Ej. 16, 18, 36"
                                class="h-10 max-w-xs"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.duracion_semanas_tpd }"
                            />
                            <p class="text-xs text-muted-foreground">
                                Número aproximado de semanas de actividad académica de esta modalidad.
                            </p>
                            <p v-if="form.errors.duracion_semanas_tpd" class="text-xs text-red-500">
                                {{ form.errors.duracion_semanas_tpd }}
                            </p>
                        </div>

                        <!-- FILA 3: DESCRIPCIÓN -->
                        <div class="space-y-2">
                            <Label for="descripcion_tpd" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                Descripción o Base Normativa
                            </Label>
                            <Textarea
                                id="descripcion_tpd"
                                v-model="form.descripcion_tpd"
                                rows="3"
                                placeholder="Indique consideraciones o características reglamentarias de este tipo de período..."
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.descripcion_tpd }"
                            />
                            <p v-if="form.errors.descripcion_tpd" class="text-xs text-red-500">
                                {{ form.errors.descripcion_tpd }}
                            </p>
                        </div>

                        <!-- FILA 4: ESTATUS OPERATIVO (TOGGLE) -->
                        <div class="flex items-center justify-between rounded-lg border p-3 shadow-xs">
                            <div class="space-y-0.5">
                                <Label for="status" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Estatus del Tipo de Período
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ form.status ? 'El tipo de período estará Activo e inmediatamente disponible.' : 'El tipo de período estará Inactivo al crearse.' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch
                                    id="status"
                                    :checked="form.status"
                                    @update:checked="(val: boolean) => form.status = val"
                                />
                                <span class="text-xs font-semibold" :class="form.status ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500'">
                                    {{ form.status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-2 border-t pt-4 dark:border-slate-800">
                        <Button as-child variant="outline" type="button">
                            <Link :href="route('academico.tipo-periodo.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="shadow-sm">
                            <Save class="h-4 w-4 mr-2" />
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

