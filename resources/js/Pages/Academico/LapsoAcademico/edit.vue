<script setup lang="ts">
/**
 * Vista Standalone: Edición de Lapso Académico (Módulo: Estructura Académica).
 *
 * Propósito: Formulario precargado para modificar datos, período lectivo y estado operativo.
 *
 * Actualización 2026-10-21 (Protección de Padre Inmutable y Límites de Fechas):
 * Se bloqueó la modificación del año académico padre durante la edición (inmutable) y se
 * aplicaron restricciones dinámicas (`min` y `max`) a los selectores de fechas para que no
 * se puedan asignar fechas fuera del rango del año académico padre.
 */
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import Badge from '@/components/ui/badge/Badge.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Clock,
    ArrowLeft,
    Save,
    AlertCircle,
    CalendarDays,
    BookOpen,
    Lock,
    AlertTriangle,
} from 'lucide-vue-next';

interface AnoSelectItem {
    id_ano: number;
    nombre_ano: string;
    fecha_inicio_ano?: string | null;
    fecha_fin_ano?: string | null;
}

interface LapsoActivo {
    id_la: number;
    nombre_la: string;
}

interface LapsoAcademicoModel {
    id_la: number;
    ano_id?: number | null;
    nombre_la: string;
    fecha_inicio_la?: string | null;
    fecha_fin_la?: string | null;
    estado_la: boolean | number;
    nombre_ano?: string | null;
    fecha_inicio_ano?: string | null;
    fecha_fin_ano?: string | null;
}

const props = defineProps<{
    lapso: LapsoAcademicoModel;
    anosSelect?: AnoSelectItem[];
    lapsoActivo?: LapsoActivo | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
    { title: props.lapso?.nombre_la ?? 'Lapso Académico', subtitle: 'Modificación', href: route('academico.lapso-academico.edit', props.lapso?.id_la) },
];

const form = useForm({
    ano_id: props.lapso?.ano_id ?? '',
    nombre_la: props.lapso?.nombre_la || '',
    fecha_inicio_la: props.lapso?.fecha_inicio_la ? String(props.lapso.fecha_inicio_la).substring(0, 10) : '',
    fecha_fin_la: props.lapso?.fecha_fin_la ? String(props.lapso.fecha_fin_la).substring(0, 10) : '',
    estado_la: Boolean(props.lapso?.estado_la === true || props.lapso?.estado_la === 1 || props.lapso?.estado_la === '1'),
    status_id: (props.lapso?.estado_la === true || props.lapso?.estado_la === 1 || props.lapso?.estado_la === '1') ? 1 : 2,
    cerrar_anterior: false,
    guardar_desactivado: false,
});

const minFechaAno = computed(() => {
    return props.lapso?.fecha_inicio_ano ? String(props.lapso.fecha_inicio_ano).substring(0, 10) : undefined;
});

const maxFechaAno = computed(() => {
    return props.lapso?.fecha_fin_ano ? String(props.lapso.fecha_fin_ano).substring(0, 10) : undefined;
});

const submit = (cerrarAnterior = false) => {
    form.clearErrors();
    form.cerrar_anterior = cerrarAnterior;
    form.status_id = form.estado_la ? 1 : 2;

    if (minFechaAno.value && form.fecha_inicio_la && form.fecha_inicio_la < minFechaAno.value) {
        form.setError('fecha_inicio_la', `La fecha de inicio no puede ser anterior al inicio del año (${minFechaAno.value})`);
        return;
    }
    if (maxFechaAno.value && form.fecha_fin_la && form.fecha_fin_la > maxFechaAno.value) {
        form.setError('fecha_fin_la', `La fecha de fin no puede superar el límite del año (${maxFechaAno.value})`);
        return;
    }

    form.put(route('academico.lapso-academico.update', props.lapso.id_la), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.lapso?.nombre_la ?? 'Lapso Académico'} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                            <Link :href="route('academico.lapso-academico.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Lapso Académico: {{ props.lapso?.nombre_la }}
                        </h1>
                        <Badge :variant="form.estado_la ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ form.estado_la ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice las fechas, asociación al año académico y estado operativo del lapso lectivo.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE DENTRO DE CARD -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600 dark:text-amber-400">
                            <Clock class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Editar Lapso Académico</CardTitle>
                            <CardDescription>
                                Modifique la información general del registro seleccionado.
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

                        <!-- FILA 1: AÑO ACADÉMICO PADRE (INMUTABLE) Y NOMBRE DEL LAPSO -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- AÑO ACADÉMICO PADRE -->
                            <div class="space-y-2">
                                <Label for="ano_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                                        Año Académico Padre
                                    </span>
                                    <span class="text-xs text-amber-600 dark:text-amber-400 font-normal flex items-center gap-1">
                                        <Lock class="h-3 w-3" /> No editable
                                    </span>
                                </Label>
                                <Input
                                    id="ano_id"
                                    :value="props.lapso?.nombre_ano || 'Año Académico'"
                                    disabled
                                    class="h-10 bg-muted/60 text-muted-foreground cursor-not-allowed font-medium"
                                />
                                <p v-if="minFechaAno || maxFechaAno" class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                    <CalendarDays class="h-3.5 w-3.5 text-primary" />
                                    Vigencia del año padre: {{ minFechaAno ?? 'Sin límite inf.' }} al {{ maxFechaAno ?? 'Sin límite sup.' }}
                                </p>
                            </div>

                            <!-- NOMBRE DEL LAPSO -->
                            <div class="space-y-2">
                                <Label for="nombre_la" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nombre del Lapso <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_la"
                                    v-model="form.nombre_la"
                                    type="text"
                                    placeholder="Ej. Lapso I, Primer Período, 2026-I"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nombre_la }"
                                    required
                                />
                                <p v-if="form.errors.nombre_la" class="text-xs text-red-500">
                                    {{ form.errors.nombre_la }}
                                </p>
                            </div>
                        </div>

                        <!-- FILA 2: FECHAS INICIO Y FIN -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- FECHA INICIO -->
                            <div class="space-y-2">
                                <Label for="fecha_inicio_la" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Inicio <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="fecha_inicio_la"
                                    v-model="form.fecha_inicio_la"
                                    type="date"
                                    :min="minFechaAno"
                                    :max="form.fecha_fin_la || maxFechaAno"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_inicio_la }"
                                    required
                                />
                                <p v-if="form.errors.fecha_inicio_la" class="text-xs text-red-500">
                                    {{ form.errors.fecha_inicio_la }}
                                </p>
                            </div>

                            <!-- FECHA FIN -->
                            <div class="space-y-2">
                                <Label for="fecha_fin_la" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Finalización <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="fecha_fin_la"
                                    v-model="form.fecha_fin_la"
                                    type="date"
                                    :min="form.fecha_inicio_la || minFechaAno"
                                    :max="maxFechaAno"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_fin_la }"
                                    required
                                />
                                <p v-if="form.errors.fecha_fin_la" class="text-xs text-red-500">
                                    {{ form.errors.fecha_fin_la }}
                                </p>
                            </div>
                        </div>

                        <!-- ESTATUS OPERATIVO (TOGGLE) -->
                        <div class="flex items-center justify-between rounded-lg border p-3 shadow-xs">
                            <div class="space-y-0.5">
                                <Label for="estado_la" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Estatus del Lapso Académico
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ form.estado_la ? 'El lapso académico se encuentra Activo.' : 'El lapso académico se encuentra Inactivo.' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch
                                    id="estado_la"
                                    :checked="form.estado_la"
                                    @update:checked="(val: boolean) => form.estado_la = val"
                                />
                                <span class="text-xs font-semibold" :class="form.estado_la ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500'">
                                    {{ form.estado_la ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <!-- ALERTA DE ACTIVACIÓN SI EXISTE OTRO LAPSO EN CURSO -->
                        <div
                            v-if="props.lapsoActivo && form.estado_la"
                            class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-900/50 p-4 space-y-2.5 transition-all"
                        >
                            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-semibold text-sm">
                                <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600" />
                                <span>Alerta de Activación: Lapso Académico en Curso Detectado</span>
                            </div>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                Actualmente el lapso académico <strong>"{{ props.lapsoActivo.nombre_la }}"</strong> se encuentra en curso (Activo).
                                El sistema exige cerrar el período anterior para activar este período, o puede mantenerlo desactivado.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="text-xs h-8 border-amber-300 text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:text-amber-200"
                                    @click="form.estado_la = false"
                                >
                                    Mantener Desactivado
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="text-xs h-8 bg-amber-600 hover:bg-amber-700 text-white shadow-xs"
                                    @click="submit(true)"
                                >
                                    Cerrar "{{ props.lapsoActivo.nombre_la }}" y Activar Este
                                </Button>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-2 border-t pt-4 dark:border-slate-800">
                        <Button as-child variant="outline" type="button">
                            <Link :href="route('academico.lapso-academico.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="shadow-sm">
                            <Save class="h-4 w-4 mr-2" />
                            {{ form.processing ? 'Guardando...' : 'Actualizar' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

