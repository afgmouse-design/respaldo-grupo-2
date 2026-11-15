<script setup lang="ts">
/**
 * Vista Standalone: Creación de Lapso Académico (Módulo: Estructura Académica).
 *
 * Propósito: Formulario para registrar un nuevo lapso académico vinculado
 * a un año académico existente activo.
 *
 * Actualización 2026-10-21 (Restricción de Fechas Dentro de Límites del Año Padre):
 * Se añadieron límites dinámicos (`min` y `max`) a los selectores de fechas para garantizar que
 * el lapso lectivo no sobrepase el período de vigencia del año académico seleccionado.
 */
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Clock,
    ArrowLeft,
    Save,
    AlertCircle,
    CalendarDays,
    BookOpen,
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

const props = defineProps<{
    anosSelect?: AnoSelectItem[];
    lapsoActivo?: LapsoActivo | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
    { title: 'Crear Lapso Académico', subtitle: 'Nuevo Registro', href: route('academico.lapso-academico.create') },
];

const form = useForm({
    ano_id: '' as number | string,
    nombre_la: '',
    fecha_inicio_la: '',
    fecha_fin_la: '',
    estado_la: props.lapsoActivo ? false : true,
    status_id: props.lapsoActivo ? 2 : 1,
    cerrar_anterior: false,
    guardar_desactivado: false,
});

const selectedAno = computed(() => {
    if (!form.ano_id) return null;
    return props.anosSelect?.find(a => a.id_ano === Number(form.ano_id)) ?? null;
});

const minFechaAno = computed(() => {
    return selectedAno.value?.fecha_inicio_ano ? String(selectedAno.value.fecha_inicio_ano).substring(0, 10) : undefined;
});

const maxFechaAno = computed(() => {
    return selectedAno.value?.fecha_fin_ano ? String(selectedAno.value.fecha_fin_ano).substring(0, 10) : undefined;
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

    form.post(route('academico.lapso-academico.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Crear Lapso Académico - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                            <Link :href="route('academico.lapso-academico.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Crear Lapso Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina el período lectivo asignándolo al año académico institucional correspondiente.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE DENTRO DE CARD -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Clock class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Información del Lapso Académico</CardTitle>
                            <CardDescription>
                                Complete los campos requeridos. Los campos marcados con (*) son obligatorios.
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

                        <!-- FILA 1: AÑO ACADÉMICO PADRE Y NOMBRE DEL LAPSO -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- AÑO ACADÉMICO PADRE -->
                            <div class="space-y-2">
                                <Label for="ano_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <BookOpen class="h-4 w-4 text-muted-foreground" />
                                    Año Académico Padre <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="ano_id"
                                    v-model="form.ano_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.ano_id }"
                                    required
                                >
                                    <option value="" disabled>Seleccione el año académico padre...</option>
                                    <option
                                        v-for="ano in props.anosSelect"
                                        :key="ano.id_ano"
                                        :value="ano.id_ano"
                                    >
                                        {{ ano.nombre_ano }}
                                    </option>
                                </select>
                                <p v-if="selectedAno && (minFechaAno || maxFechaAno)" class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                    <CalendarDays class="h-3.5 w-3.5 text-primary" />
                                    Vigencia del año: {{ minFechaAno ?? 'Sin límite inf.' }} al {{ maxFechaAno ?? 'Sin límite sup.' }}
                                </p>
                                <p v-if="form.errors.ano_id" class="text-xs text-red-500">
                                    {{ form.errors.ano_id }}
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
                                    {{ form.estado_la ? 'El lapso académico estará Activo para operaciones lectivas.' : 'El lapso académico estará Inactivo.' }}
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
                                El sistema exige cerrar el período anterior para activar este nuevo lapso, o puede registrarlo como desactivado.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="text-xs h-8 border-amber-300 text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:text-amber-200"
                                    @click="form.estado_la = false"
                                >
                                    Registrar como Desactivado
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
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

