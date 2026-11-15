<script setup lang="ts">
/**
 * Vista Standalone: Creación de Estado (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario para dar de alta una nueva entidad
 * federal/estado en el sistema SOGAC, asignando obligatoriamente su País padre.
 * Cumple estrictamente la regla de CERO MODALES.
 */
import { onMounted } from 'vue';
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
    MapPin,
    ArrowLeft,
    Save,
    AlertCircle,
    Globe,
} from 'lucide-vue-next';

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

const props = defineProps<{
    paises: PaisRef[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Estados', subtitle: 'Entidades Federales', href: route('geografia.estado.index') },
    { title: 'Registrar Estado', subtitle: 'Nuevo Registro', href: route('geografia.estado.create') },
];

const form = useForm({
    nombre_esd: '',
    pai_id: props.paises.length === 1 ? props.paises[0].id_pai : '',
});

// Capturar parámetro pai_id si vino predefinido desde la URL
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramPaiId = urlParams.get('pai_id');
    if (paramPaiId) {
        form.pai_id = Number(paramPaiId);
    }
});

const submit = () => {
    form.post(route('geografia.estado.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Estado - Estructura Geográfica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.estado.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nuevo Estado o Provincia
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Seleccione el país correspondiente e indique el nombre de la nueva entidad federal.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <MapPin class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Adscripción Territorial</CardTitle>
                            <CardDescription>
                                Todo estado debe pertenecer a un país previamente registrado y activo en el sistema.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-5 pt-2">
                        <!-- MENSAJE DE ERROR GLOBAL -->
                        <div
                            v-if="form.errors.error"
                            class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- CAMPO: SELECTOR DE PAÍS PADRE -->
                        <div class="space-y-2">
                            <Label for="pai_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                País Perteneciente <span class="text-red-500">*</span>
                            </Label>
                            <div class="relative">
                                <select
                                    id="pai_id"
                                    v-model="form.pai_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.pai_id }"
                                >
                                    <option value="" disabled>-- Seleccione un país --</option>
                                    <option
                                        v-for="pais in props.paises"
                                        :key="pais.id_pai"
                                        :value="pais.id_pai"
                                    >
                                        {{ pais.nombre_pai }}
                                    </option>
                                </select>
                            </div>
                            <p v-if="form.errors.pai_id" class="text-xs text-destructive font-medium">
                                {{ form.errors.pai_id }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                Solo se muestran países con estatus activo en la base de datos.
                            </p>
                        </div>

                        <!-- CAMPO: NOMBRE DEL ESTADO -->
                        <div class="space-y-2">
                            <Label for="nombre_esd" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre del Estado o Provincia <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_esd"
                                v-model="form.nombre_esd"
                                type="text"
                                placeholder="Ej: Portuguesa, Barinas, Lara, Antioquia"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_esd }"
                                autocomplete="off"
                                @input="form.nombre_esd = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_esd" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_esd }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                El nombre no debe repetirse dentro del mismo país seleccionado.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('geografia.estado.index')">
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

