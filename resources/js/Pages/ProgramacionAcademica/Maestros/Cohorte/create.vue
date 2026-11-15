<script setup lang="ts">
/**
 * Vista Standalone: Registrar Cohorte (Programación Académica).
 *
 * Propósito: Registro de una nueva cohorte académica vinculada a un lapso lectivo mediante navegación directa SPA.
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

defineProps<{
    lapsos: Array<{ id_la: number; nombre_la: string; fecha_inicio_la: string; fecha_fin_la: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Cohortes', subtitle: 'Listado', href: route('cohorte.index') },
    { title: 'Registrar Cohorte', subtitle: 'Nuevo Registro', href: route('cohorte.create') },
];

const form = useForm({
    nombre_coh: '',
    fecha_inicio_coh: '',
    laa_id: '',
});

const submit = () => {
    form.post(route('cohorte.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Cohorte - Programación Académica" />

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
                            Registrar Cohorte
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Cree una nueva cohorte o grupo generacional vinculado a un lapso académico.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos de la Cohorte</CardTitle>
                    </div>
                    <CardDescription>
                        Ingrese la denominación, el periodo lectivo y la fecha de inicio.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 md:col-span-2">
                                <Label for="laa_id">
                                    Lapso Académico <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="laa_id"
                                    v-model="form.laa_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.laa_id }"
                                >
                                    <option value="" disabled>Seleccione el lapso académico...</option>
                                    <option v-for="l in lapsos" :key="l.id_la" :value="l.id_la">
                                        {{ l.nombre_la }} ({{ l.fecha_inicio_la }} al {{ l.fecha_fin_la }})
                                    </option>
                                </select>
                                <span v-if="form.errors.laa_id" class="text-xs text-destructive">
                                    {{ form.errors.laa_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_coh">
                                    Nombre de la Cohorte <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_coh"
                                    v-model="form.nombre_coh"
                                    type="text"
                                    placeholder="Ej. Cohorte 2026-I, Grupo Alpha..."
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
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar Cohorte' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

