<script setup lang="ts">
/**
 * Vista Standalone: Edición de Unidad Curricular (Módulo: Estructura Académica / Currículo).
 *
 * Propósito: Permite editar los campos maestros de `unidad_curricular` (`codigo_unc`,
 * `codigo_anterior_unc`, `nombre_unc`, `tuc_id`).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`unidad_curricular`), eliminando los campos de carga horaria movidos a `malla_curricular_detalle`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, BookOpen, Info } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

interface TipoUnidadCurricularItem {
    id_tuc: number;
    nombre_tuc: string;
}

interface UnidadCurricularItem {
    id_unc: number;
    codigo_unc: string | null;
    codigo_anterior_unc?: string | null;
    nombre_unc: string;
    tuc_id: number;
    status_id?: number;
}

const props = defineProps<{
    unidad: UnidadCurricularItem;
    tipos_unidad?: TipoUnidadCurricularItem[];
    tipos?: TipoUnidadCurricularItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Unidades Curriculares', subtitle: 'Materias y Asignaturas', href: route('academico.unidad-curricular.index') },
    { title: `Editar: ${props.unidad.nombre_unc}`, subtitle: 'Modificación', href: route('academico.unidad-curricular.edit', props.unidad.id_unc) },
];

const listaTipos = computed(() => props.tipos_unidad || props.tipos || []);

const form = useForm({
    codigo_unc: props.unidad.codigo_unc || '',
    codigo_anterior_unc: props.unidad.codigo_anterior_unc || '',
    nombre_unc: props.unidad.nombre_unc,
    tuc_id: props.unidad.tuc_id,
});

const submit = () => {
    form.put(route('academico.unidad-curricular.update', props.unidad.id_unc), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Materia: ${unidad.nombre_unc} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.unidad-curricular.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Unidad Curricular
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice los datos de la asignatura #{{ unidad.id_unc }}: {{ unidad.nombre_unc }}.
                    </p>
                </div>
            </div>

            <Card class="shadow-xs border border-border max-w-4xl">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Información de la Materia</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice el código vigente, histórico, nombre o tipología de la asignatura.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="codigo_unc" class="text-sm font-medium">Código Actual (Opcional)</Label>
                                <Input
                                    id="codigo_unc"
                                    v-model="form.codigo_unc"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ej: MAT-1101, INF-1102"
                                    :class="{ 'border-destructive': form.errors.codigo_unc }"
                                />
                                <p v-if="form.errors.codigo_unc" class="text-xs text-destructive">{{ form.errors.codigo_unc }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="codigo_anterior_unc" class="text-sm font-medium">Código Anterior / Histórico (Opcional)</Label>
                                <Input
                                    id="codigo_anterior_unc"
                                    v-model="form.codigo_anterior_unc"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ej: MAT101"
                                    :class="{ 'border-destructive': form.errors.codigo_anterior_unc }"
                                />
                                <p v-if="form.errors.codigo_anterior_unc" class="text-xs text-destructive">{{ form.errors.codigo_anterior_unc }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="nombre_unc" class="text-sm font-medium">
                                    Nombre de la Asignatura <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_unc"
                                    v-model="form.nombre_unc"
                                    type="text"
                                    placeholder="Ej: Algorítmica y Programación"
                                    :class="{ 'border-destructive': form.errors.nombre_unc }"
                                    required
                                />
                                <p v-if="form.errors.nombre_unc" class="text-xs text-destructive">{{ form.errors.nombre_unc }}</p>
                            </div>

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
                                    <option v-for="tipo in listaTipos" :key="tipo.id_tuc" :value="tipo.id_tuc">
                                        {{ tipo.nombre_tuc }}
                                    </option>
                                </select>
                                <p v-if="form.errors.tuc_id" class="text-xs text-destructive">{{ form.errors.tuc_id }}</p>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-muted/30 border text-xs text-muted-foreground flex items-start gap-2">
                            <Info class="h-4 w-4 shrink-0 mt-0.5 text-primary" />
                            <span>
                                La carga horaria (HTEA, HTEI, THTE) y unidades de crédito viven por plan de estudios en `malla_curricular_detalle`.
                            </span>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('academico.unidad-curricular.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
