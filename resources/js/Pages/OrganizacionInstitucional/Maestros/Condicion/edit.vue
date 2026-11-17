<script setup lang="ts">
/**
 * Vista Standalone: Editar Condición de Sede (Organización Institucional).
 *
 * Propósito: Permite modificar una condición operativa en `condicion_sede`.
 *
 * Historial:
 *  - 2026-11-14: Alineado con `condicion_sede` (`id_cse`, `nombre_cse`, `descripcion_cse`, `status_id`).
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Building2 } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { resolverStatusId } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    condicion: {
        id_cse: number;
        nombre_cse: string;
        descripcion_cse?: string | null;
        status_id?: number;
        status?: boolean | number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Maestros', href: '#' },
    { title: 'Condición', subtitle: 'Listado', href: route('condicion.index') },
    { title: 'Editar Condición', subtitle: 'Actualización', href: route('condicion.edit', props.condicion.id_cse) },
];

const form = useForm({
    nombre_cse: props.condicion.nombre_cse || '',
    descripcion_cse: props.condicion.descripcion_cse || '',
    status_id: resolverStatusId(props.condicion),
});

const submit = () => {
    form.put(route('condicion.update', props.condicion.id_cse), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Condición: ${condicion.nombre_cse} - Organización Institucional`" />

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
                        <CardTitle class="text-lg font-semibold">Datos de la Condición #{{ condicion.id_cse }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice los campos que considere pertinentes.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <Label for="nombre_cse">
                                    Nombre de la Condición <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_cse"
                                    v-model="form.nombre_cse"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.nombre_cse }"
                                />
                                <span v-if="form.errors.nombre_cse" class="text-xs text-destructive">
                                    {{ form.errors.nombre_cse }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="descripcion_cse">Descripción</Label>
                                <Textarea
                                    id="descripcion_cse"
                                    v-model="form.descripcion_cse"
                                    rows="4"
                                    :class="{ 'border-destructive': form.errors.descripcion_cse }"
                                />
                                <span v-if="form.errors.descripcion_cse" class="text-xs text-destructive">
                                    {{ form.errors.descripcion_cse }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('condicion.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

