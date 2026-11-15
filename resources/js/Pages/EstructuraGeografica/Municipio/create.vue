<script setup lang="ts">
/**
 * Vista Standalone: Creación de Municipio (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario para registrar un nuevo municipio,
 * incorporando selector jerárquico asistido (Filtro por País -> Selector de Estado padre).
 * Cumple estrictamente la regla de CERO MODALES.
 */
import { ref, computed, onMounted } from 'vue';
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
    Building2,
    ArrowLeft,
    Save,
    AlertCircle,
    Globe,
    MapPin,
} from 'lucide-vue-next';

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
    pai_id: number;
}

const props = defineProps<{
    paises: PaisRef[];
    estados: EstadoRef[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Municipios', subtitle: 'Catálogo de Municipios', href: route('geografia.municipio.index') },
    { title: 'Registrar Municipio', subtitle: 'Nuevo Registro', href: route('geografia.municipio.create') },
];

// Filtro interactivo de país para asistir la selección de estados
const paisFiltroId = ref<number | string>(props.paises.length === 1 ? props.paises[0].id_pai : '');

const form = useForm({
    nombre_mun: '',
    esd_id: '' as number | string,
});

// Estados filtrados reactivamente por el país seleccionado
const estadosDisponibles = computed(() => {
    if (!paisFiltroId.value) {
        return props.estados;
    }
    return props.estados.filter(e => e.pai_id === Number(paisFiltroId.value));
});

const onPaisChange = () => {
    // Si el estado seleccionado ya no pertenece al nuevo país, limpiarlo
    if (form.esd_id) {
        const existe = estadosDisponibles.value.some(e => e.id_esd === Number(form.esd_id));
        if (!existe) {
            form.esd_id = '';
        }
    }
};

// Sincronizar país si el usuario cambia directamente el estado
const onEstadoChange = () => {
    if (form.esd_id) {
        const est = props.estados.find(e => e.id_esd === Number(form.esd_id));
        if (est && paisFiltroId.value !== est.pai_id) {
            paisFiltroId.value = est.pai_id;
        }
    }
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramEsdId = urlParams.get('esd_id');
    if (paramEsdId) {
        form.esd_id = Number(paramEsdId);
        onEstadoChange();
    }
});

const submit = () => {
    form.post(route('geografia.municipio.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Municipio - Estructura Geográfica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.municipio.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nuevo Municipio
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Seleccione el estado o provincia correspondiente e ingrese el nombre del municipio.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Building2 class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Adscripción Territorial</CardTitle>
                            <CardDescription>
                                Todo municipio debe pertenecer a una entidad federal previamente registrada.
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

                        <!-- FILA DE SELECTORES JERÁRQUICOS -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- PASO 1: FILTRAR POR PAÍS -->
                            <div class="space-y-2">
                                <Label for="paisFiltro" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>1. Filtrar por País</span>
                                </Label>
                                <select
                                    id="paisFiltro"
                                    v-model="paisFiltroId"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    @change="onPaisChange"
                                >
                                    <option value="">Todos los países</option>
                                    <option
                                        v-for="pais in props.paises"
                                        :key="pais.id_pai"
                                        :value="pais.id_pai"
                                    >
                                        {{ pais.nombre_pai }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    Use este selector para acotar la lista de estados.
                                </p>
                            </div>

                            <!-- PASO 2: SELECCIONAR ESTADO PADRE (CAMPO OBLIGATORIO) -->
                            <div class="space-y-2">
                                <Label for="esd_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>2. Estado Perteneciente <span class="text-red-500">*</span></span>
                                </Label>
                                <select
                                    id="esd_id"
                                    v-model="form.esd_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.esd_id }"
                                    @change="onEstadoChange"
                                >
                                    <option value="" disabled>-- Seleccione un estado --</option>
                                    <option
                                        v-for="estado in estadosDisponibles"
                                        :key="estado.id_esd"
                                        :value="estado.id_esd"
                                    >
                                        {{ estado.nombre_esd }}
                                    </option>
                                </select>
                                <p v-if="form.errors.esd_id" class="text-xs text-destructive font-medium">
                                    {{ form.errors.esd_id }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    {{ estadosDisponibles.length }} estados disponibles.
                                </p>
                            </div>
                        </div>

                        <!-- CAMPO: NOMBRE DEL MUNICIPIO -->
                        <div class="space-y-2 pt-2 border-t dark:border-slate-800">
                            <Label for="nombre_mun" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre del Municipio <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_mun"
                                v-model="form.nombre_mun"
                                type="text"
                                placeholder="Ej: Páez, Araure, Guanare, Sucre"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_mun }"
                                autocomplete="off"
                                @input="form.nombre_mun = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_mun" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_mun }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                El nombre no debe repetirse dentro del mismo estado seleccionado.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('geografia.municipio.index')">
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

