<script setup lang="ts">
/**
 * Vista Standalone: Edición de Trayecto (Módulo: Estructura Académica / Régimen).
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    GraduationCap,
    ArrowLeft,
    Save,
    AlertCircle,
} from 'lucide-vue-next';

interface TipoPeriodoItem {
    id_tpd: number;
    nombre_tpd: string;
    codigo_tpd?: string;
    nomenclatura?: string;
}

interface TrayectoItem {
    id_tra: number;
    codigo_tra?: string;
    nombre_tra: string;
    numero_orden_tra: number;
    tpd_id: number;
    status: boolean;
}

const props = defineProps<{
    trayecto: TrayectoItem;
    tiposPeriodo: TipoPeriodoItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Trayectos', subtitle: 'Niveles Académicos', href: route('academico.trayecto.index') },
    { title: props.trayecto.nombre_tra, subtitle: 'Modificación', href: route('academico.trayecto.edit', props.trayecto.id_tra) },
];

const form = useForm({
    nombre_tra: props.trayecto.nombre_tra,
    codigo_tra: props.trayecto.codigo_tra || '',
    numero_orden_tra: props.trayecto.numero_orden_tra,
    tpd_id: props.trayecto.tpd_id,
    status: props.trayecto.status,
});

const submit = () => {
    form.put(route('academico.trayecto.update', props.trayecto.id_tra), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Trayecto: ${trayecto.nombre_tra} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.trayecto.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Trayecto
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice los datos del trayecto #{{ trayecto.id_tra }}: {{ trayecto.nombre_tra }}.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Modificar Nivel Académico</CardTitle>
                    </div>
                    <CardDescription>
                        Ajuste el nombre, código, orden o la modalidad académica asociada.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- MODALIDAD ACADÉMICA (TIPO DE PERÍODO) -->
                        <div class="space-y-2">
                            <Label for="tpd_id" class="text-sm font-medium">
                                Modalidad Académica (Tipo de Período) <span class="text-destructive">*</span>
                            </Label>
                            <select
                                id="tpd_id"
                                v-model="form.tpd_id"
                                class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                :class="{ 'border-destructive': form.errors.tpd_id }"
                                required
                            >
                                <option value="" disabled>Seleccionar modalidad o tipo de período...</option>
                                <option
                                    v-for="tpd in tiposPeriodo"
                                    :key="tpd.id_tpd"
                                    :value="tpd.id_tpd"
                                >
                                    {{ tpd.nombre_tpd }} {{ tpd.nomenclatura ? `(${tpd.nomenclatura})` : '' }}
                                </option>
                            </select>
                            <p v-if="form.errors.tpd_id" class="text-xs text-destructive">
                                {{ form.errors.tpd_id }}
                            </p>
                        </div>

                        <!-- NOMBRE DEL TRAYECTO E IDENTIFICACIÓN DEL SEMESTRE -->
                        <div class="space-y-2">
                            <Label for="nombre_tra" class="text-sm font-medium">
                                Nombre del Trayecto / Semestre <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="nombre_tra"
                                v-model="form.nombre_tra"
                                type="text"
                                placeholder="Ej: Trayecto 1 - Semestre 1, Semestre I, Trayecto Inicial"
                                :class="{ 'border-destructive': form.errors.nombre_tra }"
                                required
                            />
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-xs text-muted-foreground">Identificación rápida:</span>
                                <button
                                    type="button"
                                    class="text-xs px-2 py-0.5 rounded bg-muted hover:bg-primary/10 hover:text-primary transition-colors border text-foreground"
                                    @click="form.nombre_tra = 'Semestre 1'"
                                >
                                    Semestre 1
                                </button>
                                <button
                                    type="button"
                                    class="text-xs px-2 py-0.5 rounded bg-muted hover:bg-primary/10 hover:text-primary transition-colors border text-foreground"
                                    @click="form.nombre_tra = 'Semestre 2'"
                                >
                                    Semestre 2
                                </button>
                                <button
                                    type="button"
                                    class="text-xs px-2 py-0.5 rounded bg-muted hover:bg-primary/10 hover:text-primary transition-colors border text-foreground"
                                    @click="form.nombre_tra = 'Trayecto 1 - Semestre 1'"
                                >
                                    Trayecto 1 - Semestre 1
                                </button>
                                <button
                                    type="button"
                                    class="text-xs px-2 py-0.5 rounded bg-muted hover:bg-primary/10 hover:text-primary transition-colors border text-foreground"
                                    @click="form.nombre_tra = 'Trayecto 1 - Semestre 2'"
                                >
                                    Trayecto 1 - Semestre 2
                                </button>
                            </div>
                            <p v-if="form.errors.nombre_tra" class="text-xs text-destructive">
                                {{ form.errors.nombre_tra }}
                            </p>
                        </div>

                        <!-- FILA DE CÓDIGO Y NÚMERO DE ORDEN -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- CÓDIGO REFERENCIAL -->
                            <div class="space-y-2">
                                <Label for="codigo_tra" class="text-sm font-medium">
                                    Código Referencial
                                </Label>
                                <Input
                                    id="codigo_tra"
                                    v-model="form.codigo_tra"
                                    type="text"
                                    placeholder="Ej: T-INI, T-1"
                                    :class="{ 'border-destructive': form.errors.codigo_tra }"
                                />
                                <p v-if="form.errors.codigo_tra" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tra }}
                                </p>
                            </div>

                            <!-- NÚMERO DE ORDEN -->
                            <div class="space-y-2">
                                <Label for="numero_orden_tra" class="text-sm font-medium">
                                    Número de Orden Correlativo <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="numero_orden_tra"
                                    v-model.number="form.numero_orden_tra"
                                    type="number"
                                    min="0"
                                    max="50"
                                    placeholder="Ej: 1, 2, 3"
                                    :class="{ 'border-destructive': form.errors.numero_orden_tra }"
                                    required
                                />
                                <p v-if="form.errors.numero_orden_tra" class="text-xs text-destructive">
                                    {{ form.errors.numero_orden_tra }}
                                </p>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.trayecto.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Actualizando...' : 'Actualizar' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

