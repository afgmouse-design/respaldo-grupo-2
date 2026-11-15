<script setup lang="ts">
/**
 * Vista Standalone: Creación de PNF (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Permite registrar un nuevo Programa Nacional de Formación mediante useForm de Inertia,
 * siguiendo la arquitectura de diseño y componentes de Trayecto / Sede.
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'PNF', subtitle: 'Oferta Académica', href: route('academico.pnf.index') },
    { title: 'Registrar PNF', subtitle: 'Nuevo Registro', href: route('academico.pnf.create') },
];

const form = useForm({
    codigo_pnf: '',
    nombre_pnf: '',
});

const submit = () => {
    form.post(route('academico.pnf.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar PNF - Estructura Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.pnf.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nuevo PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Defina los datos principales del Programa Nacional de Formación a aperturar en la institución.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Información del Programa Nacional de Formación</CardTitle>
                    </div>
                    <CardDescription>
                        Complete los datos de la oferta académica. Los campos con (*) son obligatorios.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- ERROR GLOBAL -->
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- CÓDIGO DEL PNF -->
                        <div class="space-y-2">
                            <Label for="codigo_pnf" class="text-sm font-medium">
                                Código del PNF (Opcional)
                            </Label>
                            <Input
                                id="codigo_pnf"
                                v-model="form.codigo_pnf"
                                type="text"
                                placeholder="Ej: PNF-INF, INF-01, 1024"
                                :class="{ 'border-destructive': form.errors.codigo_pnf }"
                            />
                            <p v-if="form.errors.codigo_pnf" class="text-xs text-destructive">
                                {{ form.errors.codigo_pnf }}
                            </p>
                        </div>

                        <!-- NOMBRE DEL PNF -->
                        <div class="space-y-2">
                            <Label for="nombre_pnf" class="text-sm font-medium">
                                Nombre del PNF / Carrera <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="nombre_pnf"
                                v-model="form.nombre_pnf"
                                type="text"
                                placeholder="Ej: PNF en Informática, Licenciatura en Administración"
                                :class="{ 'border-destructive': form.errors.nombre_pnf }"
                                required
                            />
                            <p v-if="form.errors.nombre_pnf" class="text-xs text-destructive">
                                {{ form.errors.nombre_pnf }}
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/10 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('academico.pnf.index')">
                                Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Guardar' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
