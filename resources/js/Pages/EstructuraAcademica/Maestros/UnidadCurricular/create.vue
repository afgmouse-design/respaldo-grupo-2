<script setup lang="ts">
/**
 * Vista Standalone: Creación de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite registrar una nueva Unidad Curricular (materia) mediante useForm de Inertia,
 * especificando claramente el Semestre y Trayecto al que pertenece, tipología y carga horaria/créditos.
 */
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    BookOpen,
    ArrowLeft,
    Save,
    AlertCircle,
    Calendar,
    Layers,
    Clock,
    Award,
} from 'lucide-vue-next';

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
}

interface TrayectoItem {
    id_tra: number;
    nombre_tra: string;
    numero_orden_tra?: number;
}

const props = defineProps<{
    tipos_unidad?: TipoUnidadCurricularItem[];
    tipos?: TipoUnidadCurricularItem[];
    trayectos?: TrayectoItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Unidades Curriculares', subtitle: 'Materias y Asignaturas', href: route('academico.unidad-curricular.index') },
    { title: 'Registrar Materia', subtitle: 'Nuevo Registro', href: route('academico.unidad-curricular.create') },
];

const listaTipos = computed(() => props.tipos_unidad || props.tipos || []);
const listaTrayectos = computed(() => props.trayectos || []);

const form = useForm({
    codigo_unc: '',
    nombre_unc: '',
    tuc_id: '' as string | number,
    semestre_unc: 1 as number,
    tra_id: '' as string | number,
    unidades_credito_unc: 3 as number,
    htea_unc: 3 as number,
    htei_unc: 3 as number,
    thte_unc: 6 as number,
});

// Auto-cálculo de horas totales al cambiar HTEA o HTEI
watch([() => form.htea_unc, () => form.htei_unc], ([nuevoHtea, nuevoHtei]) => {
    form.thte_unc = (Number(nuevoHtea) || 0) + (Number(nuevoHtei) || 0);
});

const submit = () => {
    form.post(route('academico.unidad-curricular.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Unidad Curricular - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina una materia o asignatura indicando claramente el semestre, trayecto, créditos y horas correspondientes.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Información de la Materia</CardTitle>
                    </div>
                    <CardDescription>
                        Complete los datos curriculares de la asignatura. Los campos con (*) son obligatorios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- 1. IDENTIFICACIÓN DE LA ASIGNATURA -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- CÓDIGO DE LA MATERIA -->
                            <div class="space-y-2">
                                <Label for="codigo_unc" class="text-sm font-medium">
                                    Código de la Asignatura (Opcional)
                                </Label>
                                <Input
                                    id="codigo_unc"
                                    v-model="form.codigo_unc"
                                    type="text"
                                    placeholder="Ej: MAT-1101, INF-1102, PROY-2104"
                                    :class="{ 'border-destructive': form.errors.codigo_unc }"
                                />
                                <p v-if="form.errors.codigo_unc" class="text-xs text-destructive">
                                    {{ form.errors.codigo_unc }}
                                </p>
                            </div>

                            <!-- NOMBRE DE LA MATERIA -->
                            <div class="space-y-2">
                                <Label for="nombre_unc" class="text-sm font-medium">
                                    Nombre de la Asignatura <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_unc"
                                    v-model="form.nombre_unc"
                                    type="text"
                                    placeholder="Ej: Algorítmica y Programación, Matemática I, Base de Datos"
                                    :class="{ 'border-destructive': form.errors.nombre_unc }"
                                    required
                                />
                                <p v-if="form.errors.nombre_unc" class="text-xs text-destructive">
                                    {{ form.errors.nombre_unc }}
                                </p>
                            </div>
                        </div>

                        <!-- 2. UBICACIÓN CURRICULAR: SEMESTRE Y TRAYECTO -->
                        <div class="p-4 rounded-xl border bg-muted/20 space-y-4">
                            <div class="flex items-center gap-2 text-primary font-semibold text-sm">
                                <Calendar class="h-4 w-4" />
                                <span>Ubicación Curricular (Semestre y Trayecto)</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- SEMESTRE -->
                                <div class="space-y-2">
                                    <Label for="semestre_unc" class="text-sm font-medium">
                                        Semestre / Período <span class="text-destructive">*</span>
                                    </Label>
                                    <select
                                        id="semestre_unc"
                                        v-model.number="form.semestre_unc"
                                        class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        :class="{ 'border-destructive': form.errors.semestre_unc }"
                                        required
                                    >
                                        <option :value="1">Semestre 1 (Primer Semestre)</option>
                                        <option :value="2">Semestre 2 (Segundo Semestre)</option>
                                        <option :value="3">Semestre 3 (Tercer Semestre)</option>
                                        <option :value="4">Semestre 4 (Cuarto Semestre)</option>
                                        <option :value="5">Semestre 5 (Quinto Semestre)</option>
                                        <option :value="6">Semestre 6 (Sexto Semestre)</option>
                                        <option :value="7">Semestre 7 (Séptimo Semestre)</option>
                                        <option :value="8">Semestre 8 (Octavo Semestre)</option>
                                    </select>
                                    <p v-if="form.errors.semestre_unc" class="text-xs text-destructive">
                                        {{ form.errors.semestre_unc }}
                                    </p>
                                </div>

                                <!-- TRAYECTO -->
                                <div class="space-y-2">
                                    <Label for="tra_id" class="text-sm font-medium">
                                        Trayecto Formativo
                                    </Label>
                                    <select
                                        id="tra_id"
                                        v-model="form.tra_id"
                                        class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        :class="{ 'border-destructive': form.errors.tra_id }"
                                    >
                                        <option value="">Sin trayecto específico</option>
                                        <option
                                            v-for="trayecto in listaTrayectos"
                                            :key="trayecto.id_tra"
                                            :value="trayecto.id_tra"
                                        >
                                            {{ trayecto.nombre_tra }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.tra_id" class="text-xs text-destructive">
                                        {{ form.errors.tra_id }}
                                    </p>
                                </div>

                                <!-- TIPO DE UNIDAD CURRICULAR -->
                                <div class="space-y-2">
                                    <Label for="tuc_id" class="text-sm font-medium">
                                        Tipo de Asignatura <span class="text-destructive">*</span>
                                    </Label>
                                    <select
                                        id="tuc_id"
                                        v-model="form.tuc_id"
                                        class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        :class="{ 'border-destructive': form.errors.tuc_id }"
                                        required
                                    >
                                        <option value="" disabled>Seleccione tipo...</option>
                                        <option
                                            v-for="tipo in listaTipos"
                                            :key="tipo.id_tuc"
                                            :value="tipo.id_tuc"
                                        >
                                            {{ tipo.nombre_tuc }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.tuc_id" class="text-xs text-destructive">
                                        {{ form.errors.tuc_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. CARGA ACADÉMICA Y CRÉDITOS -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <!-- UNIDADES DE CRÉDITO -->
                            <div class="space-y-2">
                                <Label for="unidades_credito_unc" class="text-sm font-medium flex items-center gap-1.5">
                                    <Award class="h-4 w-4 text-amber-600" />
                                    <span>Unidades de Crédito (UC)</span>
                                </Label>
                                <Input
                                    id="unidades_credito_unc"
                                    v-model.number="form.unidades_credito_unc"
                                    type="number"
                                    min="0"
                                    max="30"
                                    placeholder="Ej: 3"
                                    :class="{ 'border-destructive': form.errors.unidades_credito_unc }"
                                />
                                <p v-if="form.errors.unidades_credito_unc" class="text-xs text-destructive">
                                    {{ form.errors.unidades_credito_unc }}
                                </p>
                            </div>

                            <!-- HTEA -->
                            <div class="space-y-2">
                                <Label for="htea_unc" class="text-sm font-medium flex items-center gap-1.5">
                                    <Clock class="h-4 w-4 text-blue-600" />
                                    <span>Horas Acompañadas (HTEA)</span>
                                </Label>
                                <Input
                                    id="htea_unc"
                                    v-model.number="form.htea_unc"
                                    type="number"
                                    min="0"
                                    placeholder="Ej: 3"
                                />
                            </div>

                            <!-- HTEI -->
                            <div class="space-y-2">
                                <Label for="htei_unc" class="text-sm font-medium flex items-center gap-1.5">
                                    <Clock class="h-4 w-4 text-indigo-600" />
                                    <span>Horas Estudio Indep. (HTEI)</span>
                                </Label>
                                <Input
                                    id="htei_unc"
                                    v-model.number="form.htei_unc"
                                    type="number"
                                    min="0"
                                    placeholder="Ej: 3"
                                />
                            </div>

                            <!-- THTE -->
                            <div class="space-y-2">
                                <Label for="thte_unc" class="text-sm font-medium flex items-center gap-1.5">
                                    <Layers class="h-4 w-4 text-primary" />
                                    <span>Total Horas (THTE)</span>
                                </Label>
                                <Input
                                    id="thte_unc"
                                    v-model.number="form.thte_unc"
                                    type="number"
                                    min="0"
                                    class="font-bold bg-muted/20"
                                    readonly
                                />
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.unidad-curricular.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Unidad Curricular' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
