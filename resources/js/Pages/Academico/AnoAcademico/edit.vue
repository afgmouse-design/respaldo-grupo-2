<script setup lang="ts">
/**
 * Vista Standalone: Edición de Año Académico (Módulo: Estructura Académica).
 *
 * Propósito: Formulario precargado para modificar los datos y estatus de un año académico existente.
 */
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
    Calendar,
    ArrowLeft,
    Save,
    AlertCircle,
    CalendarDays,
    AlertTriangle,
} from 'lucide-vue-next';

interface AnoAcademicoModel {
    id_ano: number;
    nombre_ano: string;
    fecha_inicio_ano?: string | null;
    fecha_fin_ano?: string | null;
    status: boolean | number;
}

interface AnoActivo {
    id_ano: number;
    nombre_ano: string;
}

const props = defineProps<{
    ano: AnoAcademicoModel;
    anoActivo?: AnoActivo | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Calendario', href: '#' },
    { title: 'Años Académicos', subtitle: 'Gestión de Años Académicos', href: route('academico.ano-academico.index') },
    { title: props.ano?.nombre_ano ?? 'Año Académico', subtitle: 'Modificación', href: route('academico.ano-academico.edit', props.ano?.id_ano) },
];

const form = useForm({
    nombre_ano: props.ano?.nombre_ano || '',
    fecha_inicio_ano: props.ano?.fecha_inicio_ano ? String(props.ano.fecha_inicio_ano).substring(0, 10) : '',
    fecha_fin_ano: props.ano?.fecha_fin_ano ? String(props.ano.fecha_fin_ano).substring(0, 10) : '',
    status: Boolean(props.ano?.status === true || props.ano?.status === 1 || props.ano?.status === '1'),
    cerrar_anterior: false,
    guardar_desactivado: false,
});

const submit = (cerrarAnterior = false) => {
    form.cerrar_anterior = cerrarAnterior;
    form.put(route('academico.ano-academico.update', props.ano.id_ano), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.ano?.nombre_ano ?? 'Año Académico'} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                            <Link :href="route('academico.ano-academico.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Año Académico: {{ props.ano?.nombre_ano }}
                        </h1>
                        <Badge :variant="form.status ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ form.status ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice las fechas y el estado operativo del período anual seleccionado.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE DENTRO DE CARD -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600 dark:text-amber-400">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Editar Año Académico</CardTitle>
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

                        <!-- CAMPO: NOMBRE DEL AÑO ACADÉMICO -->
                        <div class="space-y-2">
                            <Label for="nombre_ano" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre del Año Académico <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_ano"
                                v-model="form.nombre_ano"
                                type="text"
                                placeholder="Ej. 2026, Año Académico 2026-2027"
                                class="h-10"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nombre_ano }"
                                required
                            />
                            <p v-if="form.errors.nombre_ano" class="text-xs text-red-500">
                                {{ form.errors.nombre_ano }}
                            </p>
                        </div>

                        <!-- FILA DE FECHAS (INICIO Y FIN) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- FECHA INICIO -->
                            <div class="space-y-2">
                                <Label for="fecha_inicio_ano" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Inicio
                                </Label>
                                <Input
                                    id="fecha_inicio_ano"
                                    v-model="form.fecha_inicio_ano"
                                    type="date"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_inicio_ano }"
                                />
                                <p v-if="form.errors.fecha_inicio_ano" class="text-xs text-red-500">
                                    {{ form.errors.fecha_inicio_ano }}
                                </p>
                            </div>

                            <!-- FECHA FIN -->
                            <div class="space-y-2">
                                <Label for="fecha_fin_ano" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                                    Fecha de Finalización
                                </Label>
                                <Input
                                    id="fecha_fin_ano"
                                    v-model="form.fecha_fin_ano"
                                    type="date"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.fecha_fin_ano }"
                                />
                                <p v-if="form.errors.fecha_fin_ano" class="text-xs text-red-500">
                                    {{ form.errors.fecha_fin_ano }}
                                </p>
                            </div>
                        </div>

                        <!-- ESTATUS OPERATIVO (TOGGLE) -->
                        <div class="flex items-center justify-between rounded-lg border p-3 shadow-xs">
                            <div class="space-y-0.5">
                                <Label for="status" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Estatus del Año Académico
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ form.status ? 'El año académico se encuentra Activo.' : 'El año académico se encuentra Inactivo.' }}
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

                        <!-- ALERTA DE ACTIVACIÓN SI EXISTE OTRO AÑO EN CURSO -->
                        <div
                            v-if="props.anoActivo && form.status"
                            class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-900/50 p-4 space-y-2.5 transition-all"
                        >
                            <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-semibold text-sm">
                                <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600" />
                                <span>Alerta de Activación: Año Académico en Curso Detectado</span>
                            </div>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                Actualmente el año académico <strong>"{{ props.anoActivo.nombre_ano }}"</strong> se encuentra en curso (Activo).
                                El sistema exige cerrar el año anterior para activar este período, o puede mantenerlo desactivado.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    class="text-xs h-8 border-amber-300 text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:text-amber-200"
                                    @click="form.status = false"
                                >
                                    Mantener Desactivado
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="text-xs h-8 bg-amber-600 hover:bg-amber-700 text-white shadow-xs"
                                    @click="submit(true)"
                                >
                                    Cerrar "{{ props.anoActivo.nombre_ano }}" y Activar Este
                                </Button>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-2 border-t pt-4 dark:border-slate-800">
                        <Button as-child variant="outline" type="button">
                            <Link :href="route('academico.ano-academico.index')">
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

