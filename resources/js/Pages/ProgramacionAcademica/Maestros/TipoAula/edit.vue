<script setup lang="ts">
/**
 * Vista Standalone: Editar Tipo de Aula (Programación Académica).
 *
 * Propósito: Modificación de un tipo de aula existente mediante navegación directa SPA.
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
import { ArrowLeft, Save, DoorClosed } from 'lucide-vue-next';

const props = defineProps<{
    tipoAula: {
        id_tia: number;
        codigo_tia: string;
        nombre_tia: string;
        descripcion_tia?: string | null;
        status?: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Tipos de Aula', subtitle: 'Listado', href: route('tipo_aula.index') },
    { title: 'Editar Tipo de Aula', subtitle: 'Actualización', href: route('tipo_aula.edit', props.tipoAula.id_tia) },
];

const form = useForm({
    codigo_tia: props.tipoAula.codigo_tia || '',
    nombre_tia: props.tipoAula.nombre_tia || '',
    descripcion_tia: props.tipoAula.descripcion_tia || '',
});

const submit = () => {
    form.put(route('tipo_aula.update', props.tipoAula.id_tia), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Tipo de Aula: ${tipoAula.nombre_tia} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('tipo_aula.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Tipo de Aula
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los atributos de la tipología de ambiente seleccionada.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <DoorClosed class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos del Tipo de Aula #{{ tipoAula.id_tia }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice los campos que considere necesarios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="codigo_tia">
                                    Código <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="codigo_tia"
                                    v-model="form.codigo_tia"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.codigo_tia }"
                                />
                                <span v-if="form.errors.codigo_tia" class="text-xs text-destructive">
                                    {{ form.errors.codigo_tia }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_tia">
                                    Nombre del Tipo de Aula <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_tia"
                                    v-model="form.nombre_tia"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_tia }"
                                />
                                <span v-if="form.errors.nombre_tia" class="text-xs text-destructive">
                                    {{ form.errors.nombre_tia }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="descripcion_tia">Descripción</Label>
                                <Textarea
                                    id="descripcion_tia"
                                    v-model="form.descripcion_tia"
                                    rows="3"
                                    :class="{ 'border-destructive': form.errors.descripcion_tia }"
                                />
                                <span v-if="form.errors.descripcion_tia" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_tia }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('tipo_aula.index')">Cancelar</Link>
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

