<script setup lang="ts">
/**
 * Vista Standalone: Editar Tipo de Lapso Académico (`tipo_lapso`).
 *
 * Propósito: Modificar los parámetros oficiales de un registro en `tipo_lapso(id_tla)`.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `id_tla`, `codigo_tla`, `nombre_tla`, `duracion_semanas_tla`,
 *    `nomenclatura_tla`, `descripcion_tla` y `UpdateTipoLapsoRequest`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Clock } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    tipoLapso: {
        id_tla?: number;
        id?: number;
        codigo_tla: string;
        nombre_tla: string;
        duracion_semanas_tla: number;
        nomenclatura_tla?: string | null;
        descripcion_tla?: string | null;
        status_id?: number;
    };
}>();

const tipoLapsoId = computed(() => props.tipoLapso.id_tla ?? props.tipoLapso.id ?? 0);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Lapso', subtitle: 'Listado', href: route('tipo_lapso.index') },
    { title: 'Editar Tipo de Lapso', subtitle: 'Actualización', href: route('tipo_lapso.edit', tipoLapsoId.value) },
];

const form = useForm({
    codigo_tla: props.tipoLapso.codigo_tla || '',
    nombre_tla: props.tipoLapso.nombre_tla || '',
    duracion_semanas_tla: Number(props.tipoLapso.duracion_semanas_tla ?? 18),
    nomenclatura_tla: props.tipoLapso.nomenclatura_tla || '',
    descripcion_tla: props.tipoLapso.descripcion_tla || '',
});

const submit = () => {
    form.put(route('tipo_lapso.update', tipoLapsoId.value), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Tipo de Lapso: ${tipoLapso.nombre_tla} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_lapso.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Tipo de Lapso Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice la configuración de duración o nomenclatura del tipo de lapso.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">{{ tipoLapso.nombre_tla }}</CardTitle>
                    </div>
                    <CardDescription>
                        Código identificador interno #{{ tipoLapsoId }}.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tla">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tla"
                                    v-model="form.codigo_tla"
                                    type="text"
                                    maxlength="20"
                                    :class="{ 'border-destructive': form.errors.codigo_tla }"
                                />
                                <span v-if="form.errors.codigo_tla" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tla">
                                    Nombre <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tla"
                                    v-model="form.nombre_tla"
                                    type="text"
                                    maxlength="100"
                                    :class="{ 'border-destructive': form.errors.nombre_tla }"
                                />
                                <span v-if="form.errors.nombre_tla" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="duracion_semanas_tla">
                                    Duración (Semanas) <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="duracion_semanas_tla"
                                    v-model.number="form.duracion_semanas_tla"
                                    type="number"
                                    min="1"
                                    max="104"
                                    :class="{ 'border-destructive': form.errors.duracion_semanas_tla }"
                                />
                                <span v-if="form.errors.duracion_semanas_tla" class="text-xs text-destructive">
                                    {{ form.errors.duracion_semanas_tla }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nomenclatura_tla">
                                    Nomenclatura / Prefijo
                                </Label>
                                <Input
                                    id="nomenclatura_tla"
                                    v-model="form.nomenclatura_tla"
                                    type="text"
                                    maxlength="20"
                                    :class="{ 'border-destructive': form.errors.nomenclatura_tla }"
                                />
                                <span v-if="form.errors.nomenclatura_tla" class="text-xs text-destructive">
                                    {{ form.errors.nomenclatura_tla }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tla">
                                    Descripción
                                </Label>
                                <Input
                                    id="descripcion_tla"
                                    v-model="form.descripcion_tla"
                                    type="text"
                                    maxlength="255"
                                    :class="{ 'border-destructive': form.errors.descripcion_tla }"
                                />
                                <span v-if="form.errors.descripcion_tla" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tla }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('tipo_lapso.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
