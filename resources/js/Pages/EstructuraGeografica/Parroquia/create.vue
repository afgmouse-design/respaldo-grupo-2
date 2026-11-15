<script setup lang="ts">
/**
 * Vista Standalone: Creación de Parroquia (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario para registrar una nueva parroquia,
 * con selectores encadenados asistidos (Filtro por Estado -> Selector de Municipio padre).
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
    Compass,
    ArrowLeft,
    Save,
    AlertCircle,
    Building2,
    MapPin,
} from 'lucide-vue-next';

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
}

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
    esd_id: number;
}

const props = defineProps<{
    estados: EstadoRef[];
    municipios: MunicipioRef[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Parroquias', subtitle: 'Catálogo de Parroquias', href: route('geografia.parroquia.index') },
    { title: 'Registrar Parroquia', subtitle: 'Nuevo Registro', href: route('geografia.parroquia.create') },
];

const estadoFiltroId = ref<number | string>(props.estados.length === 1 ? props.estados[0].id_esd : '');

const form = useForm({
    nombre_par: '',
    mun_id: '' as number | string,
});

// Municipios filtrados reactivamente por el estado seleccionado
const municipiosDisponibles = computed(() => {
    if (!estadoFiltroId.value) {
        return props.municipios;
    }
    return props.municipios.filter(m => m.esd_id === Number(estadoFiltroId.value));
});

const onEstadoChange = () => {
    if (form.mun_id) {
        const existe = municipiosDisponibles.value.some(m => m.id_mun === Number(form.mun_id));
        if (!existe) {
            form.mun_id = '';
        }
    }
};

const onMunicipioChange = () => {
    if (form.mun_id) {
        const mun = props.municipios.find(m => m.id_mun === Number(form.mun_id));
        if (mun && estadoFiltroId.value !== mun.esd_id) {
            estadoFiltroId.value = mun.esd_id;
        }
    }
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramMunId = urlParams.get('mun_id');
    if (paramMunId) {
        form.mun_id = Number(paramMunId);
        onMunicipioChange();
    }
});

const submit = () => {
    form.post(route('geografia.parroquia.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Parroquia - Estructura Geográfica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.parroquia.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Nueva Parroquia
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Seleccione el municipio adscrito e ingrese el nombre de la parroquia.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Compass class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Adscripción Municipal</CardTitle>
                            <CardDescription>
                                Toda parroquia debe pertenecer a un municipio previamente registrado.
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
                            <!-- PASO 1: FILTRAR POR ESTADO -->
                            <div class="space-y-2">
                                <Label for="estadoFiltro" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>1. Filtrar por Estado</span>
                                </Label>
                                <select
                                    id="estadoFiltro"
                                    v-model="estadoFiltroId"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    @change="onEstadoChange"
                                >
                                    <option value="">Todos los estados</option>
                                    <option
                                        v-for="estado in props.estados"
                                        :key="estado.id_esd"
                                        :value="estado.id_esd"
                                    >
                                        {{ estado.nombre_esd }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    Acota los municipios disponibles en la siguiente lista.
                                </p>
                            </div>

                            <!-- PASO 2: MUNICIPIO PADRE (CAMPO OBLIGATORIO) -->
                            <div class="space-y-2">
                                <Label for="mun_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>2. Municipio Perteneciente <span class="text-red-500">*</span></span>
                                </Label>
                                <select
                                    id="mun_id"
                                    v-model="form.mun_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.mun_id }"
                                    @change="onMunicipioChange"
                                >
                                    <option value="" disabled>-- Seleccione un municipio --</option>
                                    <option
                                        v-for="mun in municipiosDisponibles"
                                        :key="mun.id_mun"
                                        :value="mun.id_mun"
                                    >
                                        {{ mun.nombre_mun }}
                                    </option>
                                </select>
                                <p v-if="form.errors.mun_id" class="text-xs text-destructive font-medium">
                                    {{ form.errors.mun_id }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    {{ municipiosDisponibles.length }} municipios disponibles.
                                </p>
                            </div>
                        </div>

                        <!-- CAMPO: NOMBRE DE LA PARROQUIA -->
                        <div class="space-y-2 pt-2 border-t dark:border-slate-800">
                            <Label for="nombre_par" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre de la Parroquia <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_par"
                                v-model="form.nombre_par"
                                type="text"
                                placeholder="Ej: Acarigua, Payara, Pimpinela, Ramón Peraza"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_par }"
                                autocomplete="off"
                                @input="form.nombre_par = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_par" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_par }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                El nombre no debe repetirse dentro del mismo municipio.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="flex items-center justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button as-child variant="outline">
                            <Link :href="route('geografia.parroquia.index')">
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

