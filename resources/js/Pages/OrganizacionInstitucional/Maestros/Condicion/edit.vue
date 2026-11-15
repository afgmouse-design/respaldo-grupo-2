<script setup lang="ts">
/**
 * Vista Standalone: Editar Condición (Organización Institucional).
 *
 * Propósito: Permite modificar una condición institucional existente mediante navegación directa SPA.
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
import { ArrowLeft, Save, Building2 } from 'lucide-vue-next';

const props = defineProps<{
    condicion: {
        id_cdn: number;
        nombre_cdn: string;
        descripcion_cdn?: string | null;
        status?: boolean;
    };
    tiposCondicion?: Array<{ id_tco: number; nombre_tco: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Maestros', href: '#' },
    { title: 'Condición', subtitle: 'Listado', href: route('condicion.index') },
    { title: 'Editar Condición', subtitle: 'Actualización', href: route('condicion.edit', props.condicion.id_cdn) },
];

const form = useForm({
    nombre_cdn: props.condicion.nombre_cdn || '',
    descripcion_cdn: props.condicion.descripcion_cdn || '',
});

const submit = () => {
    form.put(route('condicion.update', props.condicion.id_cdn), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Condición: ${condicion.nombre_cdn} - Organización Institucional`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('condicion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Condición
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique la información de la condición seleccionada.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos de la Condición #{{ condicion.id_cdn }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice los campos que considere pertinentes.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <Label for="nombre_cdn">
                                    Nombre de la Condición <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_cdn"
                                    v-model="form.nombre_cdn"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_cdn }"
                                />
                                <span v-if="form.errors.nombre_cdn" class="text-xs text-destructive">
                                    {{ form.errors.nombre_cdn }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="descripcion_cdn">Descripción</Label>
                                <Textarea
                                    id="descripcion_cdn"
                                    v-model="form.descripcion_cdn"
                                    rows="4"
                                    :class="{ 'border-destructive': form.errors.descripcion_cdn }"
                                />
                                <span v-if="form.errors.descripcion_cdn" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_cdn }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('condicion.index')">Cancelar</Link>
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

