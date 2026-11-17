<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Localidad (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados de una localidad y su árbol jerárquico ascendente completo
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado de la localidad.
 * - [Actualización sogac_mio]: Sustitución de lecturas booleanas `status` por `status_id` mediante `@/lib/status`.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Home,
    ArrowLeft,
    Pencil,
    Power,
    Globe,
    MapPin,
    Building2,
    Compass,
    Calendar,
    Hash,
    CheckCircle2,
} from 'lucide-vue-next';

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
    pais?: PaisRef;
}

interface MunicipioRef {
    id_mun: number;
    nombre_mun: string;
    estado?: EstadoRef;
}

interface ParroquiaRef {
    id_par: number;
    nombre_par: string;
    municipio?: MunicipioRef;
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
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Localidades', subtitle: 'Sectores y Comunidades', href: route('geografia.localidad.index') },
    { title: props.localidad.nombre_loc, subtitle: 'Ficha Detallada', href: route('geografia.localidad.show', props.localidad.id_loc) },
];

const formatDate = (isoString?: string) => {
    if (!isoString) return 'No registrado';
    return new Date(isoString).toLocaleDateString('es-VE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const toggleEstatus = async () => {
    const accion = esActivo(props.localidad) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la localidad "${props.localidad.nombre_loc}"?`)) {
        router.patch(route('geografia.localidad.status', props.localidad.id_loc), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.localidad.nombre_loc} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES PRINCIPALES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.localidad.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-3">
                            <span>{{ props.localidad.nombre_loc }}</span>
                            <StatusSwitch :status="esActivo(props.localidad)" @toggle="toggleEstatus" />
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10 flex items-center gap-2 flex-wrap">
                        <span>Código territorial: #{{ props.localidad.id_loc }}</span>
                        <span>•</span>
                        <span v-if="props.localidad.parroquia" class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                            <Compass class="h-3.5 w-3.5 text-muted-foreground" />
                            <Link :href="route('geografia.parroquia.show', props.localidad.parroquia.id_par)" class="hover:underline text-primary">
                                {{ props.localidad.parroquia.nombre_par }}
                            </Link>
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.localidad.edit', props.localidad.id_loc)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar Localidad
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="esActivo(props.localidad) ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700'"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-2" />
                        {{ esActivo(props.localidad) ? 'Desactivar' : 'Activar' }}
                    </Button>
                </div>
            </div>

            <!-- TARJETAS DE DETALLE TÉCNICO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Hash class="h-3.5 w-3.5" /> Identificador Primario
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            #{{ props.localidad.id_loc }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Llave primaria en tabla <code class="text-xs bg-muted px-1 py-0.5 rounded">localidad</code>
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Compass class="h-3.5 w-3.5" /> Parroquia Adscrita
                        </CardDescription>
                        <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">
                            <Link v-if="props.localidad.parroquia" :href="route('geografia.parroquia.show', props.localidad.parroquia.id_par)" class="text-primary hover:underline">
                                {{ props.localidad.parroquia.nombre_par }}
                            </Link>
                            <span v-else>No asignada</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Clave foránea <code class="text-xs bg-muted px-1 py-0.5 rounded">par_id</code>: {{ props.localidad.par_id }}
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Calendar class="h-3.5 w-3.5" /> Registro en Sistema
                        </CardDescription>
                        <CardTitle class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatDate(props.localidad.created_at) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Actualizado: {{ formatDate(props.localidad.updated_at) }}
                    </CardContent>
                </Card>
            </div>

            <!-- JERARQUÍA TERRITORIAL ASCENDENTE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <CardTitle class="text-lg flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-primary" />
                        <span>Árbol Territorial Completo</span>
                    </CardTitle>
                    <CardDescription>
                        Trazabilidad geográfica desde el nivel país hasta este sector residencial.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                        <!-- NIVEL PAÍS -->
                        <div class="relative flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-primary/20 text-primary flex items-center justify-center -ml-6 border-2 border-background">
                                <Globe class="h-3 w-3" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-muted-foreground font-medium uppercase">Nivel 1: País</span>
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    <Link
                                        v-if="props.localidad.parroquia?.municipio?.estado?.pais"
                                        :href="route('geografia.pais.show', props.localidad.parroquia.municipio.estado.pais.id_pai)"
                                        class="hover:underline text-primary"
                                    >
                                        {{ props.localidad.parroquia.municipio.estado.pais.nombre_pai }}
                                    </Link>
                                    <span v-else class="text-muted-foreground italic">No asignado</span>
                                </div>
                            </div>
                        </div>

                        <!-- NIVEL ESTADO -->
                        <div class="relative flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-primary/20 text-primary flex items-center justify-center -ml-6 border-2 border-background">
                                <MapPin class="h-3 w-3" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-muted-foreground font-medium uppercase">Nivel 2: Estado</span>
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    <Link
                                        v-if="props.localidad.parroquia?.municipio?.estado"
                                        :href="route('geografia.estado.show', props.localidad.parroquia.municipio.estado.id_esd)"
                                        class="hover:underline text-primary"
                                    >
                                        {{ props.localidad.parroquia.municipio.estado.nombre_esd }}
                                    </Link>
                                    <span v-else class="text-muted-foreground italic">No asignado</span>
                                </div>
                            </div>
                        </div>

                        <!-- NIVEL MUNICIPIO -->
                        <div class="relative flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-primary/20 text-primary flex items-center justify-center -ml-6 border-2 border-background">
                                <Building2 class="h-3 w-3" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-muted-foreground font-medium uppercase">Nivel 3: Municipio</span>
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    <Link
                                        v-if="props.localidad.parroquia?.municipio"
                                        :href="route('geografia.municipio.show', props.localidad.parroquia.municipio.id_mun)"
                                        class="hover:underline text-primary"
                                    >
                                        {{ props.localidad.parroquia.municipio.nombre_mun }}
                                    </Link>
                                    <span v-else class="text-muted-foreground italic">No asignado</span>
                                </div>
                            </div>
                        </div>

                        <!-- NIVEL PARROQUIA -->
                        <div class="relative flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-primary/20 text-primary flex items-center justify-center -ml-6 border-2 border-background">
                                <Compass class="h-3 w-3" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-muted-foreground font-medium uppercase">Nivel 4: Parroquia</span>
                                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    <Link
                                        v-if="props.localidad.parroquia"
                                        :href="route('geografia.parroquia.show', props.localidad.parroquia.id_par)"
                                        class="hover:underline text-primary"
                                    >
                                        {{ props.localidad.parroquia.nombre_par }}
                                    </Link>
                                    <span v-else class="text-muted-foreground italic">No asignada</span>
                                </div>
                            </div>
                        </div>

                        <!-- NIVEL LOCALIDAD (ACTUAL) -->
                        <div class="relative flex items-center gap-3">
                            <div class="h-5 w-5 rounded-full bg-emerald-600 text-white flex items-center justify-center -ml-6 border-2 border-background">
                                <Home class="h-3 w-3" />
                            </div>
                            <div class="flex-1">
                                <span class="text-xs text-emerald-600 font-bold uppercase">Nivel 5: Localidad / Sector (Actual)</span>
                                <div class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {{ props.localidad.nombre_loc }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

