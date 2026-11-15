<script setup lang="ts">
/**
 * Vista Standalone: Registrar Turno Académico (Programación Académica).
 *
 * Propósito: Permite registrar un nuevo turno institucional en PostgreSQL (`turno`).
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
import { ArrowLeft, Save, Clock } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: route('seccion.index') },
    { title: 'Turnos Académicos', subtitle: 'Listado', href: route('turno.index') },
    { title: 'Registrar Turno', subtitle: 'Nuevo Registro', href: route('turno.create') },
];

const form = useForm({
    codigo_tur: '',
    nombre_tur: '',
    hora_inicio_tur: '07:00',
    hora_fin_tur: '12:30',
    descripcion_tur: '',
});

const submit = () => {
    form.post(route('turno.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Turno Académico - Programación Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('turno.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Turno Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una nueva franja horaria institucional para la oferta de secciones (Diurno, Vespertino, Nocturno...).
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Turno</CardTitle>
                    </div>
                    <CardDescription>
                        Complete la información requerida para el registro en la tabla `turno`.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tur">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tur"
                                    v-model="form.codigo_tur"
                                    type="text"
                                    placeholder="Ej. DIU, VES, NOC, SAB..."
                                    :class="{ 'border-destructive': form.errors.codigo_tur }"
                                />
                                <span v-if="form.errors.codigo_tur" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tur }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tur">
                                    Nombre del Turno <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tur"
                                    v-model="form.nombre_tur"
                                    type="text"
                                    placeholder="Ej. Diurno / Mañana"
                                    :class="{ 'border-destructive': form.errors.nombre_tur }"
                                />
                                <span v-if="form.errors.nombre_tur" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tur }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="hora_inicio_tur">Hora de Inicio</Label>
                                <Input
                                    id="hora_inicio_tur"
                                    v-model="form.hora_inicio_tur"
                                    type="time"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="hora_fin_tur">Hora de Fin</Label>
                                <Input
                                    id="hora_fin_tur"
                                    v-model="form.hora_fin_tur"
                                    type="time"
                                />
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tur">Descripción y Notas Operativas</Label>
                                <Textarea
                                    id="descripcion_tur"
                                    v-model="form.descripcion_tur"
                                    rows="3"
                                    placeholder="Notas sobre la jornada horaria..."
                                />
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('turno.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Turno' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

