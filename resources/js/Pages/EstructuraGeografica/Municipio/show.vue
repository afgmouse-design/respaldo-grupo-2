<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Municipio (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados de un municipio, su jerarquía territorial ascendente (Estado y País)
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado del municipio y en la tabla de parroquias subordinadas.
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
    Building2,
    ArrowLeft,
    Pencil,
    Power,
    Globe,
    MapPin,
    Compass,
    Plus,
    Calendar,
    Hash,
    Layers,
    Eye,
} from 'lucide-vue-next';

interface ParroquiaSubordinada {
    id_par: number;
    nombre_par: string;
    status_id?: number;
    status?: boolean | number;
    localidades_count?: number;
    created_at?: string;
}

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoRef {
    id_esd: number;
    nombre_esd: string;
    pai_id: number;
    pais?: PaisRef;
}

interface MunicipioModel {
    id_mun: number;
    nombre_mun: string;
    esd_id: number;
    status_id?: number;
    status?: boolean | number;
    created_at?: string;
    updated_at?: string;
    estado?: EstadoRef;
    parroquias?: ParroquiaSubordinada[];
}

const props = defineProps<{
    municipio: MunicipioModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Municipios', subtitle: 'Catálogo de Municipios', href: route('geografia.municipio.index') },
    { title: props.municipio.nombre_mun, subtitle: 'Ficha Detallada', href: route('geografia.municipio.show', props.municipio.id_mun) },
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
    const accion = esActivo(props.municipio) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el municipio "${props.municipio.nombre_mun}"?`)) {
        router.patch(route('geografia.municipio.status', props.municipio.id_mun), {}, { preserveScroll: true });
    }
};

const toggleEstatusParroquia = async (parr: ParroquiaSubordinada) => {
    const accion = esActivo(parr) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la parroquia "${parr.nombre_par}"?`)) {
        router.patch(route('geografia.parroquia.status', parr.id_par), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.municipio.nombre_mun} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES PRINCIPALES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.municipio.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-3">
                            <span>{{ props.municipio.nombre_mun }}</span>
                            <StatusSwitch :status="esActivo(props.municipio)" @toggle="toggleEstatus" />
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10 flex items-center gap-2 flex-wrap">
                        <span>Código territorial: #{{ props.municipio.id_mun }}</span>
                        <span>•</span>
                        <span v-if="props.municipio.estado" class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                            <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                            <Link :href="route('geografia.estado.show', props.municipio.estado.id_esd)" class="hover:underline text-primary">
                                {{ props.municipio.estado.nombre_esd }}
                            </Link>
                        </span>
                        <template v-if="props.municipio.estado?.pais">
                            <span>•</span>
                            <span class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                                <Link :href="route('geografia.pais.show', props.municipio.estado.pais.id_pai)" class="hover:underline text-primary">
                                    {{ props.municipio.estado.pais.nombre_pai }}
                                </Link>
                            </span>
                        </template>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.municipio.edit', props.municipio.id_mun)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar Municipio
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="esActivo(props.municipio) ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700'"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-2" />
                        {{ esActivo(props.municipio) ? 'Desactivar' : 'Activar' }}
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
                            #{{ props.municipio.id_mun }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Llave primaria en tabla <code class="text-xs bg-muted px-1 py-0.5 rounded">municipio</code>
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <MapPin class="h-3.5 w-3.5" /> Estado Perteneciente
                        </CardDescription>
                        <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">
                            <Link v-if="props.municipio.estado" :href="route('geografia.estado.show', props.municipio.estado.id_esd)" class="text-primary hover:underline">
                                {{ props.municipio.estado.nombre_esd }}
                            </Link>
                            <span v-else>No asignado</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Clave foránea <code class="text-xs bg-muted px-1 py-0.5 rounded">esd_id</code>: {{ props.municipio.esd_id }}
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Compass class="h-3.5 w-3.5" /> Parroquias
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ props.municipio.parroquias?.length ?? 0 }} parroquias
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Divisiones territoriales menores
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Calendar class="h-3.5 w-3.5" /> Registro en Sistema
                        </CardDescription>
                        <CardTitle class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatDate(props.municipio.created_at) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Actualizado: {{ formatDate(props.municipio.updated_at) }}
                    </CardContent>
                </Card>
            </div>

            <!-- SECCIÓN: ENTIDADES HIJAS (PARROQUIAS) -->
            <Card class="shadow-xs border border-border">
                <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <Compass class="h-5 w-5 text-primary" />
                            <span>Parroquias Pertenecientes</span>
                        </CardTitle>
                        <CardDescription>
                            Listado de parroquias dependientes de este municipio.
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div v-if="!props.municipio.parroquias || props.municipio.parroquias.length === 0" class="text-center py-12 px-4 space-y-3">
                        <Compass class="h-10 w-10 text-muted-foreground mx-auto stroke-1" />
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                            No hay parroquias registradas para este municipio
                        </h3>
                        <p class="text-sm text-muted-foreground max-w-sm mx-auto">
                            Las parroquias podrán vincularse a este municipio una vez habilitado el maestro de parroquias.
                        </p>
                    </div>

                    <div v-else class="divide-y dark:divide-slate-800 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted/40 text-xs text-muted-foreground uppercase">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Nombre de la Parroquia</th>
                                    <th class="px-6 py-3 font-semibold text-center">Localidades</th>
                                    <th class="px-6 py-3 font-semibold text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="parr in props.municipio.parroquias"
                                    :key="parr.id_par"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-6 py-4 font-mono text-xs text-muted-foreground">
                                        #{{ parr.id_par }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ parr.nombre_par }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ parr.localidades_count ?? 0 }} localidades
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <StatusSwitch :status="esActivo(parr)" @toggle="toggleEstatusParroquia(parr)" />
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

