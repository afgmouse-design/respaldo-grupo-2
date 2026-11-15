<script setup lang="ts">
/**
 * Vista Standalone: Editar Cohorte (Programación Académica).
 *
 * Propósito: Modificación de una cohorte existente mediante navegación directa SPA.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, GraduationCap } from 'lucide-vue-next';

const props = defineProps<{
    cohorte: {
        id_coh: number;
        nombre_coh: string;
        fecha_inicio_coh: string;
        laa_id: number;
        status?: boolean;
    };
    lapsos: Array<{ id_la: number; nombre_la: string; fecha_inicio_la: string; fecha_fin_la: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Cohortes', subtitle: 'Listado', href: route('cohorte.index') },
    { title: 'Editar Cohorte', subtitle: 'Actualización', href: route('cohorte.edit', props.cohorte.id_coh) },
];

const form = useForm({
    nombre_coh: props.cohorte.nombre_coh || '',
    fecha_inicio_coh: props.cohorte.fecha_inicio_coh || '',
    laa_id: props.cohorte.laa_id,
});

const submit = () => {
    form.put(route('cohorte.update', props.cohorte.id_coh), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Cohorte: ${cohorte.nombre_coh} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('cohorte.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Cohorte
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los atributos de la cohorte seleccionada.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos de la Cohorte #{{ cohorte.id_coh }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice el nombre o fecha de inicio. El lapso académico permanece vinculado.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 md:col-span-2">
                                <Label for="laa_id">Lapso Académico Vinculado</Label>
                                <select
                                    id="laa_id"
                                    v-model="form.laa_id"
                                    disabled
                                    class="w-full h-10 rounded-md border border-input bg-muted px-3 text-sm text-muted-foreground cursor-not-allowed"
                                >
                                    <option v-for="l in lapsos" :key="l.id_la" :value="l.id_la">
                                        {{ l.nombre_la }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">Por regla de integridad, el lapso académico no puede modificarse tras su creación.</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_coh">
                                    Nombre de la Cohorte <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_coh"
                                    v-model="form.nombre_coh"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_coh }"
                                />
                                <span v-if="form.errors.nombre_coh" class="text-xs text-destructive">
                                    {{ form.errors.nombre_coh }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="fecha_inicio_coh">
                                    Fecha de Inicio <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="fecha_inicio_coh"
                                    v-model="form.fecha_inicio_coh"
                                    type="date"
                                    :class="{ 'border-destructive': form.errors.fecha_inicio_coh }"
                                />
                                <span v-if="form.errors.fecha_inicio_coh" class="text-xs text-destructive">
                                    {{ form.errors.fecha_inicio_coh }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('cohorte.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

