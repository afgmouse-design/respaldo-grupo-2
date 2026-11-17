<script setup lang="ts">
/**
 * Vista Standalone: Edición de Localidad (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario precargado para modificar
 * los datos de una localidad existente y reasignar su parroquia padre.
 * Cumple estrictamente la regla de CERO MODALES.
 *
 * Historial de Modificaciones:
 * - [Actualización sogac_mio]: Uso de `status_id` entero (1 Activo / 2 Inactivo) en formulario e interfaz.
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Building2, Compass, Eye, Home } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo, resolverStatusId } from '@/lib/status';
import { formatNombre } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types/navigation';

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
}

interface ParroquiaRef {
    id_par: number;
    nombre_par: string;
    mun_id: number;
}

interface LocalidadModel {
    id_loc: number;
    nombre_loc: string;
    par_id: number;
    status_id?: number;
    status?: boolean | number;
    created_at?: string;
    updated_at?: string;
    parroquia?: ParroquiaRef;
}

const props = defineProps<{
    localidad: LocalidadModel;
    municipios: MunicipioRef[];
    parroquias: ParroquiaRef[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Localidades', subtitle: 'Sectores y Comunidades', href: route('geografia.localidad.index') },
    { title: props.localidad.nombre_loc, subtitle: 'Modificación', href: route('geografia.localidad.edit', props.localidad.id_loc) },
];

const municipioFiltroId = ref<number | string>(props.localidad.parroquia?.mun_id ?? '');

const form = useForm({
    nombre_loc: props.localidad.nombre_loc,
    par_id: props.localidad.par_id as number | string,
    status_id: resolverStatusId(props.localidad),
});

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

const submit = () => {
    form.put(route('geografia.localidad.update', props.localidad.id_loc), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.localidad.nombre_loc} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.localidad.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Localidad: {{ props.localidad.nombre_loc }}
                        </h1>
                        <Badge :variant="esActivo(props.localidad) ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ esActivo(props.localidad) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique la adscripción parroquial, nombre del sector o estatus operativo.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.localidad.show', props.localidad.id_loc)">
                            <Eye class="h-4 w-4 mr-2" />
                            Ver Ficha
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600 dark:text-amber-400">
                            <Home class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Actualización de Localidad</CardTitle>
                            <CardDescription>
                                Identificador único del sistema: #{{ props.localidad.id_loc }}
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
                            <!-- PASO 1: FILTRO POR MUNICIPIO -->
                            <div class="space-y-2">
                                <Label for="municipioFiltro" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>Filtrar Parroquias por Municipio</span>
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
                            </div>

                            <!-- PASO 2: PARROQUIA PADRE (CAMPO OBLIGATORIO) -->
                            <div class="space-y-2">
                                <Label for="par_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Compass class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>Parroquia Perteneciente <span class="text-red-500">*</span></span>
                                </Label>
                                <select
                                    id="par_id"
                                    v-model="form.par_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-slate-900"
                                    :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.par_id }"
                                    @change="onParroquiaChange"
                                >
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
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_loc }"
                                autocomplete="off"
                                @input="form.nombre_loc = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_loc" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_loc }}
                            </p>
                        </div>

                        <!-- CAMPO: ESTATUS -->
                        <div class="space-y-2 pt-2 border-t dark:border-slate-800">
                            <Label for="status" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Estatus Operativo
                            </Label>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input
                                        id="status"
                                        type="checkbox"
                                        :checked="form.status_id === 1"
                                        @change="form.status_id = ($event.target as HTMLInputElement).checked ? 1 : 2"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 dark:bg-slate-700"></div>
                                    <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ form.status_id === 1 ? 'Registro Activo' : 'Registro Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('geografia.localidad.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

