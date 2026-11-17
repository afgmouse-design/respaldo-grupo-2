<script setup lang="ts">
/**
 * Vista Standalone: Registrar Horario de Clase (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 *
 * Propósito: Permite asignar bloques de horario semanal vinculando carga académica y ambiente físico.
 *
 * Historial:
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CalendarClock } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

defineProps<{
    cargasAcademicas: Array<{ id_caa: number; descripcion_caa: string }>;
    aulas: Array<{ id_aul: number; codigo_aula_aul: string; capacidad_maxima_aul?: number }>;
    diasSemana: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Horarios', subtitle: 'Listado', href: route('horario.index') },
    { title: 'Registrar Horario', subtitle: 'Nuevo Registro', href: route('horario.create') },
];

const form = useForm({
    dia_semana_hor: 'Lunes',
    hora_inicio_hor: '07:30',
    hora_fin_hor: '09:00',
    caa_id: '',
    aul_id: '',
});

const submit = () => {
    form.post(route('horario.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Horario - Programación Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('horario.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Horario de Clase
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Asigne un bloque de horario semanal asociando una sección académica y un aula física.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <CalendarClock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Bloque Horario</CardTitle>
                    </div>
                    <CardDescription>
                        Complete los campos para registrar la clase en la programación institucional.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div v-if="form.errors.error" class="p-3 bg-destructive/10 text-destructive text-sm rounded-md border border-destructive/20 font-medium">
                            {{ form.errors.error }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Carga Académica -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="caa_id">
                                    Carga Académica (Sección y Materia) <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="caa_id"
                                    v-model="form.caa_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.caa_id }"
                                >
                                    <option value="" disabled>Seleccione la sección y materia...</option>
                                    <option v-for="c in cargasAcademicas" :key="c.id_caa" :value="c.id_caa">
                                        {{ c.descripcion_caa }}
                                    </option>
                                </select>
                                <p v-if="cargasAcademicas.length === 0" class="text-xs text-amber-600 dark:text-amber-400">
                                    No hay cargas académicas registradas. Puede crear secciones y asignar materias en el módulo <strong>Secciones</strong>.
                                </p>
                                <span v-if="form.errors.caa_id" class="text-xs text-destructive">
                                    {{ form.errors.caa_id }}
                                </span>
                            </div>

                            <!-- Aula -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="aul_id">
                                    Aula / Ambiente Físico <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="aul_id"
                                    v-model="form.aul_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.aul_id }"
                                >
                                    <option value="" disabled>Seleccione el aula asignada...</option>
                                    <option v-for="a in aulas" :key="a.id_aul" :value="a.id_aul">
                                        {{ a.codigo_aula_aul }} (Capacidad: {{ a.capacidad_maxima_aul || 'N/A' }})
                                    </option>
                                </select>
                                <p v-if="aulas.length === 0" class="text-xs text-amber-600 dark:text-amber-400">
                                    No hay aulas activas registradas en el módulo <strong>Aulas</strong>.
                                </p>
                                <span v-if="form.errors.aul_id" class="text-xs text-destructive">
                                    {{ form.errors.aul_id }}
                                </span>
                            </div>

                            <!-- Día de la Semana -->
                            <div class="space-y-2">
                                <Label for="dia_semana_hor">
                                    Día de la Semana <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="dia_semana_hor"
                                    v-model="form.dia_semana_hor"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.dia_semana_hor }"
                                >
                                    <option v-for="d in diasSemana" :key="d" :value="d">
                                        {{ d }}
                                    </option>
                                </select>
                                <span v-if="form.errors.dia_semana_hor" class="text-xs text-destructive">
                                    {{ form.errors.dia_semana_hor }}
                                </span>
                            </div>

                            <!-- Rango Horario -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-2">
                                    <Label for="hora_inicio_hor">
                                        Hora Inicio <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="hora_inicio_hor"
                                        v-model="form.hora_inicio_hor"
                                        type="time"
                                        :class="{ 'border-destructive': form.errors.hora_inicio_hor }"
                                    />
                                    <span v-if="form.errors.hora_inicio_hor" class="text-xs text-destructive">
                                        {{ form.errors.hora_inicio_hor }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <Label for="hora_fin_hor">
                                        Hora Fin <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="hora_fin_hor"
                                        v-model="form.hora_fin_hor"
                                        type="time"
                                        :class="{ 'border-destructive': form.errors.hora_fin_hor }"
                                    />
                                    <span v-if="form.errors.hora_fin_hor" class="text-xs text-destructive">
                                        {{ form.errors.hora_fin_hor }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('horario.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

