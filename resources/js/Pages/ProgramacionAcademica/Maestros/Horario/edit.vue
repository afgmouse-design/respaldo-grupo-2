<script setup lang="ts">
/**
 * Vista Standalone: Editar Horario de Clase (Programación Académica).
 * Grupo 3: Ángel Gonzales - Oferta y Horarios.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, CalendarClock } from 'lucide-vue-next';

const props = defineProps<{
    horario: any;
    cargasAcademicas: Array<{ id_caa: number; descripcion_caa: string }>;
    aulas: Array<{ id_aul: number; codigo_aula_aul: string; capacidad_maxima_aul?: number }>;
    diasSemana: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Horarios', subtitle: 'Listado', href: route('horario.index') },
    { title: 'Editar Horario', subtitle: 'Actualización', href: route('horario.edit', props.horario.id_hor) },
];

const formatTime = (timeStr?: string) => {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
};

const form = useForm({
    dia_semana_hor: props.horario.dia_semana_hor || 'Lunes',
    hora_inicio_hor: formatTime(props.horario.hora_inicio_hor),
    hora_fin_hor: formatTime(props.horario.hora_fin_hor),
    caa_id: props.horario.caa_id || '',
    aul_id: props.horario.aul_id || '',
});

const submit = () => {
    form.put(route('horario.update', props.horario.id_hor), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Horario - Programación Académica" />

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
                            Editar Horario de Clase
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice los detalles de tiempo, carga académica o aula asignada al bloque.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <CalendarClock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Modificar Bloque Horario</CardTitle>
                    </div>
                    <CardDescription>
                        Ajuste la información y guarde para verificar la disponibilidad sin choques de aula.
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

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('horario.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

