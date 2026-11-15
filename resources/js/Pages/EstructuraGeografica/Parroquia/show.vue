<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Parroquia (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados de una parroquia, su jerarquía ascendente (Municipio -> Estado -> País)
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado de la parroquia y en la tabla de localidades subordinadas.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Compass,
    ArrowLeft,
    Pencil,
    Power,
    Globe,
    MapPin,
    Building2,
    Home,
    Plus,
    Calendar,
    Hash,
    Eye,
} from 'lucide-vue-next';

interface LocalidadSubordinada {
    id_loc: number;
    nombre_loc: string;
    status: boolean;
    created_at?: string;
}

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

interface ParroquiaModel {
    id_par: number;
    nombre_par: string;
    mun_id: number;
    status: boolean;
    created_at?: string;
    updated_at?: string;
    municipio?: MunicipioRef;
    localidades?: LocalidadSubordinada[];
}

const props = defineProps<{
    parroquia: ParroquiaModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Parroquias', subtitle: 'Catálogo de Parroquias', href: route('geografia.parroquia.index') },
    { title: props.parroquia.nombre_par, subtitle: 'Ficha Detallada', href: route('geografia.parroquia.show', props.parroquia.id_par) },
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
    const accion = props.parroquia.status ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la parroquia "${props.parroquia.nombre_par}"?`)) {
        router.delete(route('geografia.parroquia.destroy', props.parroquia.id_par));
    }
};

const toggleEstatusLocalidad = async (loc: LocalidadSubordinada) => {
    const accion = loc.status ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la localidad "${loc.nombre_loc}"?`)) {
        router.patch(route('geografia.localidad.status', loc.id_loc), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.parroquia.nombre_par} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES PRINCIPALES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.parroquia.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-3">
                            <span>{{ props.parroquia.nombre_par }}</span>
                            <StatusSwitch :status="props.parroquia.status" @toggle="toggleEstatus" />
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10 flex items-center gap-2 flex-wrap">
                        <span>Código territorial: #{{ props.parroquia.id_par }}</span>
                        <span>•</span>
                        <span v-if="props.parroquia.municipio" class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                            <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                            <Link :href="route('geografia.municipio.show', props.parroquia.municipio.id_mun)" class="hover:underline text-primary">
                                {{ props.parroquia.municipio.nombre_mun }}
                            </Link>
                        </span>
                        <template v-if="props.parroquia.municipio?.estado">
                            <span>•</span>
                            <span class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                                <span>{{ props.parroquia.municipio.estado.nombre_esd }}</span>
                            </span>
                        </template>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.parroquia.edit', props.parroquia.id_par)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar Parroquia
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="props.parroquia.status ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700'"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-2" />
                        {{ props.parroquia.status ? 'Desactivar' : 'Activar' }}
                    </Button>
                </div>
            </div>

            <!-- TARJETAS DE DETALLE TÉCNICO -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Hash class="h-3.5 w-3.5" /> Identificador Primario
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            #{{ props.parroquia.id_par }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Llave primaria en tabla <code class="text-xs bg-muted px-1 py-0.5 rounded">parroquia</code>
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Building2 class="h-3.5 w-3.5" /> Municipio Adscrito
                        </CardDescription>
                        <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">
                            <Link v-if="props.parroquia.municipio" :href="route('geografia.municipio.show', props.parroquia.municipio.id_mun)" class="text-primary hover:underline">
                                {{ props.parroquia.municipio.nombre_mun }}
                            </Link>
                            <span v-else>No asignado</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Clave foránea <code class="text-xs bg-muted px-1 py-0.5 rounded">mun_id</code>: {{ props.parroquia.mun_id }}
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Home class="h-3.5 w-3.5" /> Localidades
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ props.parroquia.localidades?.length ?? 0 }} sectores
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Comunidades y sectores adscritos
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Calendar class="h-3.5 w-3.5" /> Registro en Sistema
                        </CardDescription>
                        <CardTitle class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatDate(props.parroquia.created_at) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Actualizado: {{ formatDate(props.parroquia.updated_at) }}
                    </CardContent>
                </Card>
            </div>

            <!-- SECCIÓN: ENTIDADES HIJAS (LOCALIDADES) -->
            <Card class="shadow-xs border border-border">
                <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <Home class="h-5 w-5 text-primary" />
                            <span>Localidades y Sectores Pertenecientes</span>
                        </CardTitle>
                        <CardDescription>
                            Listado de caseríos, barrios y sectores delimitados en esta parroquia.
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div v-if="!props.parroquia.localidades || props.parroquia.localidades.length === 0" class="text-center py-12 px-4 space-y-3">
                        <Home class="h-10 w-10 text-muted-foreground mx-auto stroke-1" />
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                            No hay localidades registradas para esta parroquia
                        </h3>
                        <p class="text-sm text-muted-foreground max-w-sm mx-auto">
                            Las localidades y sectores podrán asociarse una vez habilitado el maestro de localidades.
                        </p>
                    </div>

                    <div v-else class="divide-y dark:divide-slate-800 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted/40 text-xs text-muted-foreground uppercase">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Nombre de la Localidad / Sector</th>
                                    <th class="px-6 py-3 font-semibold text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="loc in props.parroquia.localidades"
                                    :key="loc.id_loc"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-6 py-4 font-mono text-xs text-muted-foreground">
                                        #{{ loc.id_loc }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ loc.nombre_loc }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <StatusSwitch :status="loc.status" @toggle="toggleEstatusLocalidad(loc)" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

