<script setup lang="ts">
/**
 * Vista Standalone: Creación de Lapso Académico (Módulo: Estructura Académica).
 *
 * Propósito: Formulario para registrar un nuevo lapso académico vinculado
 * a un año académico existente activo y a un tipo de lapso del catálogo.
 *
 * Historial:
 *  - 2026-11-14: Alineado a esquema `sogac_mio.sql` (`ana_id`, `tla_id`, `codigo_laa`,
 *    `nombre_laa`, `fecha_inicio_laa`, `fecha_fin_laa`, `fase_operativa_laa`, `status_id`).
 *  - 2026-11-16: Se corrige `@submit.prevent="submit(false)"` para no pasar `SubmitEvent` como
 *    `cerrarAnterior = true` y se actualiza `<Switch>` a `:model-value` / `@update:model-value`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ArrowLeft,
    BookOpen,
    CalendarDays,
    Clock,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

interface AnoSelectItem {
    id_ana: number;
    nombre_ana: string;
    fecha_inicio_ana?: string | null;
    fecha_fin_ana?: string | null;
}

interface TipoLapsoSelectItem {
    id_tla: number;
    codigo_tla: string;
    nombre_tla: string;
}

interface LapsoActivo {
    id_laa: number;
    nombre_laa: string;
}

const props = defineProps<{
    anosSelect?: AnoSelectItem[];
    tiposLapso?: TipoLapsoSelectItem[];
    lapsoActivo?: LapsoActivo | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Lapsos Académicos', subtitle: 'Gestión de Lapsos Académicos', href: route('academico.lapso-academico.index') },
    { title: 'Crear Lapso Académico', subtitle: 'Nuevo Registro', href: route('academico.lapso-academico.create') },
];

const form = useForm({
    ana_id: '' as number | string,
    tla_id: (props.tiposLapso?.[0]?.id_tla ?? '') as number | string,
    codigo_laa: '',
    nombre_laa: '',
    fecha_inicio_laa: '',
    fecha_fin_laa: '',
    fase_operativa_laa: 'Planificado',
    status_id: props.lapsoActivo ? 2 : 1,
    cerrar_anterior: false,
    guardar_desactivado: false,
});

const selectedAno = computed(() => {
    if (!form.ana_id) return null;
    return props.anosSelect?.find(a => a.id_ana === Number(form.ana_id)) ?? null;
});

const minFechaAno = computed(() => {
    return selectedAno.value?.fecha_inicio_ana ? String(selectedAno.value.fecha_inicio_ana).substring(0, 10) : undefined;
});

const maxFechaAno = computed(() => {
    return selectedAno.value?.fecha_fin_ana ? String(selectedAno.value.fecha_fin_ana).substring(0, 10) : undefined;
});

const submit = (cerrarAnterior = false) => {
    form.clearErrors();
    form.cerrar_anterior = cerrarAnterior === true;

    if (minFechaAno.value && form.fecha_inicio_laa && form.fecha_inicio_laa < minFechaAno.value) {
        form.setError('fecha_inicio_laa', `La fecha de inicio no puede ser anterior al inicio del año (${minFechaAno.value})`);
        return;
    }
    if (maxFechaAno.value && form.fecha_fin_laa && form.fecha_fin_laa > maxFechaAno.value) {
        form.setError('fecha_fin_laa', `La fecha de fin no puede superar el límite del año (${maxFechaAno.value})`);
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

                <form @submit.prevent="submit(false)">
                    <CardContent class="space-y-5 pt-2">
                        <!-- MENSAJE DE ERROR GLOBAL -->
                        <div
                            v-if="form.errors.error || (form.errors as Record<string, string>).status_id"
                            class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error || (form.errors as Record<string, string>).status_id }}</span>
                        </div>

                        <!-- FILA 1: AÑO ACADÉMICO PADRE, TIPO DE LAPSO, CÓDIGO Y NOMBRE -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- AÑO ACADÉMICO PADRE -->
                            <div class="space-y-2">
                                <Label for="ana_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <BookOpen class="h-4 w-4 text-muted-foreground" />
                                    Año Académico Padre <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="ana_id"
                                    v-model="form.ana_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.ana_id }"
                                    required
                                >
                                    <option value="" disabled>Seleccione el año académico padre...</option>
                                    <option
                                        v-for="ano in props.anosSelect"
                                        :key="ano.id_ana"
                                        :value="ano.id_ana"
                                    >
                                        {{ ano.nombre_ana }}
                                    </option>
                                </select>
                                <p v-if="selectedAno && (minFechaAno || maxFechaAno)" class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                    <CalendarDays class="h-3.5 w-3.5 text-primary" />
                                    Vigencia del año: {{ minFechaAno ?? 'Sin límite inf.' }} al {{ maxFechaAno ?? 'Sin límite sup.' }}
                                </p>
                                <p v-if="form.errors.ana_id" class="text-xs text-red-500">
                                    {{ form.errors.ana_id }}
                                </p>
                            </div>

                            <!-- TIPO DE LAPSO -->
                            <div class="space-y-2">
                                <Label for="tla_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Tipo de Lapso <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="tla_id"
                                    v-model="form.tla_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.tla_id }"
                                    required
                                >
                                    <option value="" disabled>Seleccione tipo de lapso...</option>
                                    <option
                                        v-for="tipo in props.tiposLapso"
                                        :key="tipo.id_tla"
                                        :value="tipo.id_tla"
                                    >
                                        {{ tipo.nombre_tla }} ({{ tipo.codigo_tla }})
                                    </option>
                                </select>
                                <p v-if="form.errors.tla_id" class="text-xs text-red-500">
                                    {{ form.errors.tla_id }}
                                </p>
                            </div>

                            <!-- CÓDIGO DEL LAPSO -->
                            <div class="space-y-2">
                                <Label for="codigo_laa" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Código del Lapso
                                </Label>
                                <Input
                                    id="codigo_laa"
                                    v-model="form.codigo_laa"
                                    type="text"
                                    placeholder="Ej. 2026-I (opcional, se autogenera)"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.codigo_laa }"
                                />
                                <p v-if="form.errors.codigo_laa" class="text-xs text-red-500">
                                    {{ form.errors.codigo_laa }}
                                </p>
                            </div>

                            <!-- NOMBRE DEL LAPSO -->
                            <div class="space-y-2">
                                <Label for="nombre_laa" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nombre del Lapso <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_laa"
                                    v-model="form.nombre_laa"
                                    type="text"
                                    placeholder="Ej. Lapso I, Primer Período, 2026-I"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nombre_laa }"
                                    required
                                />
                                <p v-if="form.errors.nombre_laa" class="text-xs text-red-500">
                                    {{ form.errors.nombre_laa }}
                                </p>
                            </div>
                        </div>

                        <!-- FILA 2: FECHAS INICIO Y FIN -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- FECHA INICIO -->
                            <div class="space-y-2">
                                <Label for="fecha_inicio_laa" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Inicio <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="fecha_inicio_laa"
                                    v-model="form.fecha_inicio_laa"
                                    type="date"
                                    :min="minFechaAno"
                                    :max="form.fecha_fin_laa || maxFechaAno"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_inicio_laa }"
                                    required
                                />
                                <p v-if="form.errors.fecha_inicio_laa" class="text-xs text-red-500">
                                    {{ form.errors.fecha_inicio_laa }}
                                </p>
                            </div>

                            <!-- FECHA FIN -->
                            <div class="space-y-2">
                                <Label for="fecha_fin_laa" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Finalización <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="fecha_fin_laa"
                                    v-model="form.fecha_fin_laa"
                                    type="date"
                                    :min="form.fecha_inicio_laa || minFechaAno"
                                    :max="maxFechaAno"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_fin_laa }"
                                    required
                                />
                                <p v-if="form.errors.fecha_fin_laa" class="text-xs text-red-500">
                                    {{ form.errors.fecha_fin_laa }}
                                </p>
                            </div>
                        </div>

                        <!-- ESTATUS OPERATIVO (TOGGLE) -->
                        <div class="flex items-center justify-between rounded-lg border p-3 shadow-xs">
                            <div class="space-y-0.5">
                                <Label for="status_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Estatus del Lapso Académico
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ form.status_id === 1 ? 'El lapso académico estará Activo para operaciones lectivas.' : 'El lapso académico estará Inactivo.' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch
                                    id="status_id"
                                    :model-value="form.status_id === 1"
                                    @update:model-value="(val: boolean) => form.status_id = val ? 1 : 2"
                                />
                                <span class="text-xs font-semibold" :class="form.status_id === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500'">
                                    {{ form.status_id === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <!-- ALERTA DE ACTIVACIÓN SI EXISTE OTRO LAPSO EN CURSO -->
                        <div
                            v-if="props.lapsoActivo && form.status_id === 1"
                            class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-900/50 p-4 space-y-2.5 transition-all"
                        >
                            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-semibold text-sm">
                                <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600" />
                                <span>Alerta de Activación: Lapso Académico en Curso Detectado</span>
                            </div>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                Actualmente el lapso académico <strong>"{{ props.lapsoActivo.nombre_laa }}"</strong> se encuentra en curso (Activo).
                                El sistema exige cerrar el período anterior para activar este nuevo lapso, o puede registrarlo como desactivado.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="text-xs h-8 border-amber-300 text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:text-amber-200"
                                    @click="form.status_id = 2"
                                >
                                    Registrar como Desactivado
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="text-xs h-8 bg-amber-600 hover:bg-amber-700 text-white shadow-xs"
                                    @click="submit(true)"
                                >
                                    Cerrar "{{ props.lapsoActivo.nombre_laa }}" y Activar Este
                                </Button>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('academico.lapso-academico.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

