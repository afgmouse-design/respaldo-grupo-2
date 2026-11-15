<script setup lang="ts">
/**
 * Vista Standalone: Creación de Localidad (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario para registrar una nueva localidad,
 * caserío o sector, con selectores encadenados asistidos (Filtro por Municipio -> Selector de Parroquia padre).
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
    Home,
    ArrowLeft,
    Save,
    AlertCircle,
    Compass,
    Building2,
} from 'lucide-vue-next';

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
}

interface ParroquiaRef {
    id_par: number;
    nombre_par: string;
    mun_id: number;
}

const props = defineProps<{
    municipios: MunicipioRef[];
    parroquias: ParroquiaRef[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Localidades', subtitle: 'Sectores y Comunidades', href: route('geografia.localidad.index') },
    { title: 'Registrar Localidad', subtitle: 'Nuevo Registro', href: route('geografia.localidad.create') },
];

const municipioFiltroId = ref<number | string>(props.municipios.length === 1 ? props.municipios[0].id_mun : '');

const form = useForm({
    nombre_loc: '',
    par_id: '' as number | string,
});

// Parroquias filtradas reactivamente por el municipio seleccionado
const parroquiasDisponibles = computed(() => {
    if (!municipioFiltroId.value) {
        return props.parroquias;
    }
    return props.parroquias.filter(p => p.mun_id === Number(municipioFiltroId.value));
});

const onMunicipioChange = () => {
    if (form.par_id) {
        const existe = parroquiasDisponibles.value.some(p => p.id_par === Number(form.par_id));
        if (!existe) {
            form.par_id = '';
        }
    }
};

const onParroquiaChange = () => {
    if (form.par_id) {
        const parr = props.parroquias.find(p => p.id_par === Number(form.par_id));
        if (parr && municipioFiltroId.value !== parr.mun_id) {
            municipioFiltroId.value = parr.mun_id;
        }
    }
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramParId = urlParams.get('par_id');
    if (paramParId) {
        form.par_id = Number(paramParId);
        onParroquiaChange();
    }
});

const submit = () => {
    form.post(route('geografia.localidad.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Localidad - Estructura Geográfica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.localidad.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nueva Localidad o Sector
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Seleccione la parroquia adscrita e ingrese el nombre del caserío, barrio o sector urbano.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Home class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Adscripción Parroquial</CardTitle>
                            <CardDescription>
                                Toda localidad debe pertenecer a una parroquia previamente registrada en el sistema.
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
                            <!-- PASO 1: FILTRAR POR MUNICIPIO -->
                            <div class="space-y-2">
                                <Label for="municipioFiltro" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>1. Filtrar por Municipio</span>
                                </Label>
                                <select
                                    id="municipioFiltro"
                                    v-model="municipioFiltroId"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    @change="onMunicipioChange"
                                >
                                    <option value="">Todos los municipios</option>
                                    <option
                                        v-for="mun in props.municipios"
                                        :key="mun.id_mun"
                                        :value="mun.id_mun"
                                    >
                                        {{ mun.nombre_mun }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    Acota las parroquias disponibles en la siguiente lista.
                                </p>
                            </div>

                            <!-- PASO 2: PARROQUIA PADRE (CAMPO OBLIGATORIO) -->
                            <div class="space-y-2">
                                <Label for="par_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Compass class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>2. Parroquia Perteneciente <span class="text-red-500">*</span></span>
                                </Label>
                                <select
                                    id="par_id"
                                    v-model="form.par_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.par_id }"
                                    @change="onParroquiaChange"
                                >
                                    <option value="" disabled>-- Seleccione una parroquia --</option>
                                    <option
                                        v-for="parr in parroquiasDisponibles"
                                        :key="parr.id_par"
                                        :value="parr.id_par"
                                    >
                                        {{ parr.nombre_par }}
                                    </option>
                                </select>
                                <p v-if="form.errors.par_id" class="text-xs text-destructive font-medium">
                                    {{ form.errors.par_id }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    {{ parroquiasDisponibles.length }} parroquias disponibles.
                                </p>
                            </div>
                        </div>

                        <!-- CAMPO: NOMBRE DE LA LOCALIDAD -->
                        <div class="space-y-2 pt-2 border-t dark:border-slate-800">
                            <Label for="nombre_loc" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre de la Localidad / Caserío / Sector <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_loc"
                                v-model="form.nombre_loc"
                                type="text"
                                placeholder="Ej: Casco Central, Barrio Bella Vista, Sector Goajira"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_loc }"
                                autocomplete="off"
                                @input="form.nombre_loc = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_loc" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_loc }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                El nombre no debe repetirse dentro de la misma parroquia.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('geografia.localidad.index')">
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

