<script setup lang="ts">
/**
 * Vista Standalone: Creación de País (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario para dar de alta un nuevo país
 * en el sistema SOGAC. Cumple estrictamente la regla de CERO MODALES.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { formatNombre } from '@/lib/utils';
import {
    Globe,
    ArrowLeft,
    Save,
    AlertCircle,
    CheckCircle2,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Países', subtitle: 'Catálogo Maestro', href: route('geografia.pais.index') },
    { title: 'Registrar País', subtitle: 'Nuevo Registro', href: route('geografia.pais.create') },
];

const form = useForm({
    nombre_pai: '',
});

const submit = () => {
    form.post(route('geografia.pais.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar País - Estructura Geográfica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.pais.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nuevo País
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Ingrese la información oficial del país para habilitar su árbol territorial.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE DENTRO DE CARD -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Globe class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Información General</CardTitle>
                            <CardDescription>
                                El país será configurado por defecto con estatus Activo y disponible para vincular estados.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-4 pt-2">
                        <!-- MENSAJE DE ERROR GLOBAL -->
                        <div
                            v-if="form.errors.error"
                            class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- CAMPO: NOMBRE DEL PAÍS -->
                        <div class="space-y-2">
                            <Label for="nombre_pai" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre Oficial del País <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_pai"
                                v-model="form.nombre_pai"
                                type="text"
                                placeholder="Ej: Venezuela, Colombia, España"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_pai }"
                                autocomplete="off"
                                @input="form.nombre_pai = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_pai" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_pai }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                El nombre debe ser único en todo el catálogo institucional.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('geografia.pais.index')">
                                Cancelar
                            </Link>
                        </Button>

                        <Button type="submit" :disabled="form.processing" class="shadow-xs">
                            <Save class="h-4 w-4 mr-2" />
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

