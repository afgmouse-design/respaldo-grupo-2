<script setup lang="ts">
/**
 * Vista Standalone: Editar Turno Académico (Programación Académica).
 *
 * Propósito: Modificación de un turno existente en PostgreSQL (`turno`).
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Clock } from 'lucide-vue-next';

const props = defineProps<{
    turno: {
        id_tur: number;
        codigo_tur: string;
        nombre_tur: string;
        hora_inicio_tur?: string | null;
        hora_fin_tur?: string | null;
        descripcion_tur?: string | null;
        status?: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: route('seccion.index') },
    { title: 'Turnos Académicos', subtitle: 'Listado', href: route('turno.index') },
    { title: 'Editar Turno', subtitle: 'Actualización', href: route('turno.edit', props.turno.id_tur) },
];

const form = useForm({
    codigo_tur: props.turno.codigo_tur || '',
    nombre_tur: props.turno.nombre_tur || '',
    hora_inicio_tur: props.turno.hora_inicio_tur ? props.turno.hora_inicio_tur.substring(0, 5) : '07:00',
    hora_fin_tur: props.turno.hora_fin_tur ? props.turno.hora_fin_tur.substring(0, 5) : '12:30',
    descripcion_tur: props.turno.descripcion_tur || '',
});

const submit = () => {
    form.put(route('turno.update', props.turno.id_tur), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Turno: ${turno.nombre_tur} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('turno.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Turno Académico
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los horarios y datos del turno seleccionado.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Turno #{{ turno.id_tur }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice los campos que considere necesarios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tur">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tur"
                                    v-model="form.codigo_tur"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.codigo_tur }"
                                />
                                <span v-if="form.errors.codigo_tur" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tur }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tur">
                                    Nombre del Turno <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tur"
                                    v-model="form.nombre_tur"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_tur }"
                                />
                                <span v-if="form.errors.nombre_tur" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tur }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="hora_inicio_tur">Hora de Inicio</Label>
                                <Input
                                    id="hora_inicio_tur"
                                    v-model="form.hora_inicio_tur"
                                    type="time"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="hora_fin_tur">Hora de Fin</Label>
                                <Input
                                    id="hora_fin_tur"
                                    v-model="form.hora_fin_tur"
                                    type="time"
                                />
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tur">Descripción y Notas Operativas</Label>
                                <Textarea
                                    id="descripcion_tur"
                                    v-model="form.descripcion_tur"
                                    rows="3"
                                />
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('turno.index')">Cancelar</Link>
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

