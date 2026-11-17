<script setup lang="ts">
/**
 * Vista Standalone: Edición de Tipo de Lapso / Período (Módulo: Estructura Académica).
 *
 * Propósito: Página independiente con formulario precargado para modificar los datos
 * y estatus de un `tipo_lapso` existente en el SOGAC. Cumple regla Zero-Modal.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `tipo_lapso` (`id_tla`, `codigo_tla`, `nombre_tla`,
 *    `nomenclatura_tla`, `duracion_semanas_tla`, `descripcion_tla`, `status_id` con `@/lib/status`).
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Calendar,
    Clock,
    FileText,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { resolverStatusId } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';

interface TipoPeriodoModel {
    id_tla?: number;
    id_tpd: number;
    nombre_tla?: string;
    nombre_tpd: string;
    codigo_tla?: string | null;
    codigo_tpd?: string | null;
    nomenclatura_tla?: string | null;
    nomenclatura?: string | null;
    duracion_semanas_tla?: number | null;
    duracion_semanas_tpd?: number | null;
    descripcion_tla?: string | null;
    descripcion_tpd?: string | null;
    status_id?: number;
    status?: boolean;
    est_tpd?: boolean;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    tipoPeriodo: TipoPeriodoModel;
}>();

const tipoId = props.tipoPeriodo.id_tla ?? props.tipoPeriodo.id_tpd;
const nombreInicial = props.tipoPeriodo.nombre_tla ?? props.tipoPeriodo.nombre_tpd ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Tipos de Período', subtitle: 'Gestión de Tipos de Período', href: route('academico.tipo-periodo.index') },
    { title: nombreInicial, subtitle: 'Modificación', href: route('academico.tipo-periodo.edit', tipoId) },
];

const form = useForm({
    nombre_tla: nombreInicial,
    codigo_tla: props.tipoPeriodo.codigo_tla ?? props.tipoPeriodo.codigo_tpd ?? '',
    nomenclatura_tla: props.tipoPeriodo.nomenclatura_tla ?? props.tipoPeriodo.nomenclatura ?? 'Semestre',
    duracion_semanas_tla: props.tipoPeriodo.duracion_semanas_tla ?? props.tipoPeriodo.duracion_semanas_tpd ?? 16,
    descripcion_tla: props.tipoPeriodo.descripcion_tla ?? props.tipoPeriodo.descripcion_tpd ?? '',
    status_id: resolverStatusId(props.tipoPeriodo),
});

const submit = () => {
    form.put(route('academico.tipo-periodo.update', tipoId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${nombreInicial} - Estructura Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                            <Link :href="route('academico.tipo-periodo.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Tipo de Período: {{ nombreInicial }}
                        </h1>
                        <Badge :variant="form.status_id === 1 ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ form.status_id === 1 ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice las características reglamentarias o el estado operativo del período académico.
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
                            <CardTitle class="text-lg">Editar Tipo de Período</CardTitle>
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

                        <!-- FILA 1: NOMBRE, CÓDIGO Y NOMENCLATURA -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- CAMPO: NOMBRE DEL TIPO DE PERÍODO -->
                            <div class="space-y-2">
                                <Label for="nombre_tla" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nombre del Tipo de Período <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_tla"
                                    v-model="form.nombre_tla"
                                    type="text"
                                    placeholder="Ej. Semestral, Anual, Trimestral"
                                    class="h-10"
                                    :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nombre_tla }"
                                    required
                                />
                                <p v-if="form.errors.nombre_tla" class="text-xs text-red-500">
                                    {{ form.errors.nombre_tla }}
                                </p>
                            </div>

                            <!-- CAMPO: CÓDIGO DEL TIPO DE PERÍODO -->
                            <div class="space-y-2">
                                <Label for="codigo_tla" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Código Corto <span class="text-red-500">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="codigo_tla"
                                        v-model="form.codigo_tla"
                                        type="text"
                                        placeholder="Ej. SEM, TRA, TRI"
                                        maxlength="20"
                                        class="h-10 uppercase font-mono"
                                        :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.codigo_tla }"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.codigo_tla" class="text-xs text-red-500">
                                    {{ form.errors.codigo_tla }}
                                </p>
                            </div>

                            <!-- CAMPO: NOMENCLATURA (SEMESTRE / TRIMESTRE / ANUAL) -->
                            <div class="space-y-2">
                                <Label for="nomenclatura_tla" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nomenclatura Visual <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="nomenclatura_tla"
                                    v-model="form.nomenclatura_tla"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :class="{ 'border-red-500': form.errors.nomenclatura_tla }"
                                    required
                                >
                                    <option value="" disabled>Seleccionar nomenclatura...</option>
                                    <option value="Semestre">Semestre</option>
                                    <option value="Trimestre">Trimestre</option>
                                    <option value="Anual">Anual</option>
                                </select>
                                <p v-if="form.errors.nomenclatura_tla" class="text-xs text-red-500">
                                    {{ form.errors.nomenclatura_tla }}
                                </p>
                            </div>
                        </div>

                        <!-- FILA 2: DURACIÓN EN SEMANAS -->
                        <div class="space-y-2">
                            <Label for="duracion_semanas_tla" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                Duración en Semanas <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="duracion_semanas_tla"
                                v-model.number="form.duracion_semanas_tla"
                                type="number"
                                min="1"
                                max="104"
                                placeholder="Ej. 16, 18, 36"
                                class="h-10 max-w-xs"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.duracion_semanas_tla }"
                                required
                            />
                            <p class="text-xs text-muted-foreground">
                                Número aproximado de semanas de actividad académica de esta modalidad.
                            </p>
                            <p v-if="form.errors.duracion_semanas_tla" class="text-xs text-red-500">
                                {{ form.errors.duracion_semanas_tla }}
                            </p>
                        </div>

                        <!-- FILA 3: DESCRIPCIÓN -->
                        <div class="space-y-2">
                            <Label for="descripcion_tla" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                Descripción o Base Normativa
                            </Label>
                            <Textarea
                                id="descripcion_tla"
                                v-model="form.descripcion_tla"
                                rows="3"
                                placeholder="Indique consideraciones o características reglamentarias de este tipo de período..."
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.descripcion_tla }"
                            />
                            <p v-if="form.errors.descripcion_tla" class="text-xs text-red-500">
                                {{ form.errors.descripcion_tla }}
                            </p>
                        </div>

                        <!-- FILA 4: ESTATUS OPERATIVO (TOGGLE) - OBLIGATORIO EN EDIT -->
                        <div class="flex items-center justify-between rounded-lg border p-3 shadow-xs">
                            <div class="space-y-0.5">
                                <Label for="status_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Estatus del Tipo de Período <span class="text-red-500">*</span>
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ form.status_id === 1 ? 'El tipo de período está Activo y disponible para vincular trayectos.' : 'El tipo de período está Inactivo y no estará disponible para nuevas asignaciones.' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch
                                    id="status_id"
                                    :checked="form.status_id === 1"
                                    @update:checked="(val: boolean) => form.status_id = val ? 1 : 2"
                                />
                                <span class="text-xs font-semibold" :class="form.status_id === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500'">
                                    {{ form.status_id === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route().has('academico.tipo-periodo.index') ? route('academico.tipo-periodo.index') : route('semestres.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

