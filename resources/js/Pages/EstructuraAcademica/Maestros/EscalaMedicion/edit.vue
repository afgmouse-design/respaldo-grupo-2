<script setup lang="ts">
/**
 * Vista Standalone: Edición de Escala de Medición (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite modificar una escala de medición existente mediante useForm de Inertia,
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
    Calculator,
    ArrowLeft,
    Save,
    AlertCircle,
} from 'lucide-vue-next';

interface EscalaItem {
    id_esm: number;
    nombre_esm: string;
    nota_aprobatoria_esm: number;
    nota_minima_esm: number | null;
    nota_maxima_esm: number | null;
    status_id?: number;
    status?: boolean;
}

const props = defineProps<{
    escala: EscalaItem;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Escalas de Medición', subtitle: 'Calificaciones', href: route('academico.escala-medicion.index') },
    { title: `Editar: ${props.escala.nombre_esm}`, subtitle: 'Modificación', href: route('academico.escala-medicion.edit', props.escala.id_esm) },
];

const form = useForm({
    nombre_esm: props.escala.nombre_esm,
    nota_minima_esm: Number(props.escala.nota_minima_esm ?? 0),
    nota_aprobatoria_esm: Number(props.escala.nota_aprobatoria_esm),
    nota_maxima_esm: Number(props.escala.nota_maxima_esm ?? 0),
});

const submit = () => {
    form.put(route('academico.escala-medicion.update', props.escala.id_esm), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Escala: ${escala.nombre_esm} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.escala-medicion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Escala de Medición
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice los parámetros de la escala #{{ escala.id_esm }}: {{ escala.nombre_esm }}.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Calculator class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Modificar Parámetros de Calificación</CardTitle>
                    </div>
                    <CardDescription>
                        Ajuste la denominación, nota mínima, nota aprobatoria o nota máxima de la escala.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- NOMBRE DE LA ESCALA -->
                        <div class="space-y-2">
                            <Label for="nombre_esm" class="text-sm font-medium">
                                Nombre de la Escala de Medición <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="nombre_esm"
                                v-model="form.nombre_esm"
                                type="text"
                                placeholder="Ej: Escala Vigecimal (0 - 20)"
                                :class="{ 'border-destructive': form.errors.nombre_esm }"
                                required
                            />
                            <p v-if="form.errors.nombre_esm" class="text-xs text-destructive">
                                {{ form.errors.nombre_esm }}
                            </p>
                        </div>

                        <!-- FILA DE VALORES NUMÉRICOS -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- NOTA MÍNIMA -->
                            <div class="space-y-2">
                                <Label for="nota_minima_esm" class="text-sm font-medium">
                                    Nota Mínima <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nota_minima_esm"
                                    v-model.number="form.nota_minima_esm"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej: 0.00"
                                    :class="{ 'border-destructive': form.errors.nota_minima_esm }"
                                    required
                                />
                                <p v-if="form.errors.nota_minima_esm" class="text-xs text-destructive">
                                    {{ form.errors.nota_minima_esm }}
                                </p>
                            </div>

                            <!-- NOTA APROBATORIA -->
                            <div class="space-y-2">
                                <Label for="nota_aprobatoria_esm" class="text-sm font-medium">
                                    Nota Aprobatoria <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nota_aprobatoria_esm"
                                    v-model.number="form.nota_aprobatoria_esm"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej: 10.00 o 12.00"
                                    :class="{ 'border-destructive': form.errors.nota_aprobatoria_esm }"
                                    required
                                />
                                <p v-if="form.errors.nota_aprobatoria_esm" class="text-xs text-destructive">
                                    {{ form.errors.nota_aprobatoria_esm }}
                                </p>
                            </div>

                            <!-- NOTA MÁXIMA -->
                            <div class="space-y-2">
                                <Label for="nota_maxima_esm" class="text-sm font-medium">
                                    Nota Máxima <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nota_maxima_esm"
                                    v-model.number="form.nota_maxima_esm"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej: 20.00 o 100.00"
                                    :class="{ 'border-destructive': form.errors.nota_maxima_esm }"
                                    required
                                />
                                <p v-if="form.errors.nota_maxima_esm" class="text-xs text-destructive">
                                    {{ form.errors.nota_maxima_esm }}
                                </p>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.escala-medicion.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando cambios...' : 'Actualizar' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
