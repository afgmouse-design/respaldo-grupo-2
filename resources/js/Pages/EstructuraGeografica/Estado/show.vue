<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Estado (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados de un estado, su país perteneciente y sus municipios subordinados.
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado del estado y en la tabla de municipios subordinados.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import type { BreadcrumbItem } from '@/types/navigation';
import { esActivo, resolverStatusId } from '@/lib/status';
import {
    MapPin,
    ArrowLeft,
    Pencil,
    Power,
    Globe,
    Building2,
    Plus,
    Calendar,
    Hash,
    Layers,
    Eye,
} from 'lucide-vue-next';

interface MunicipioSubordinado {
    id_mun: number;
    nombre_mun: string;
    status_id?: number;
    status?: boolean | number;
    parroquias_count?: number;
    created_at?: string;
}

interface PaisRef {
    id_pai: number;
    nombre_pai: string;
}

interface EstadoModel {
    id_esd: number;
    nombre_esd: string;
    iso_esd?: string | null;
    pai_id: number;
    status_id?: number;
    status?: boolean | number;
    created_at?: string;
    updated_at?: string;
    pais?: PaisRef;
    municipios?: MunicipioSubordinado[];
}

const props = defineProps<{
    estado: EstadoModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Estados', subtitle: 'Entidades Federales', href: route('geografia.estado.index') },
    { title: props.estado.nombre_esd, subtitle: 'Ficha Detallada', href: route('geografia.estado.show', props.estado.id_esd) },
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
    const accion = esActivo(props.estado) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el estado "${props.estado.nombre_esd}"?`)) {
        router.patch(route('geografia.estado.status', props.estado.id_esd), {}, { preserveScroll: true });
    }
};

const toggleEstatusMunicipio = async (mun: MunicipioSubordinado) => {
    const accion = esActivo(mun) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el municipio "${mun.nombre_mun}"?`)) {
        router.patch(route('geografia.municipio.status', mun.id_mun), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.estado.nombre_esd} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES PRINCIPALES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.estado.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-3">
                            <span>{{ props.estado.nombre_esd }}</span>
                            <StatusSwitch :status="resolverStatusId(props.estado)" @toggle="toggleEstatus" />
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10 flex items-center gap-2">
                        <span>Código territorial: #{{ props.estado.id_esd }}</span>
                        <span v-if="props.estado.iso_esd">• ISO: {{ props.estado.iso_esd }}</span>
                        <span>•</span>
                        <span v-if="props.estado.pais" class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                            <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                            <Link :href="route('geografia.pais.show', props.estado.pais.id_pai)" class="hover:underline text-primary">
                                {{ props.estado.pais.nombre_pai }}
                            </Link>
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.estado.edit', props.estado.id_esd)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar Estado
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="esActivo(props.estado) ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700'"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-2" />
                        {{ esActivo(props.estado) ? 'Desactivar' : 'Activar' }}
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
                            #{{ props.estado.id_esd }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Llave primaria única en tabla <code class="text-xs bg-muted px-1 py-0.5 rounded">estado</code>
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Globe class="h-3.5 w-3.5" /> País Adscrito
                        </CardDescription>
                        <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">
                            <Link v-if="props.estado.pais" :href="route('geografia.pais.show', props.estado.pais.id_pai)" class="text-primary hover:underline">
                                {{ props.estado.pais.nombre_pai }}
                            </Link>
                            <span v-else>No asignado</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Clave foránea <code class="text-xs bg-muted px-1 py-0.5 rounded">pai_id</code>: {{ props.estado.pai_id }}
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Building2 class="h-3.5 w-3.5" /> Municipios
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ props.estado.municipios?.length ?? 0 }} municipios
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Subdivisiones territoriales activas
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Calendar class="h-3.5 w-3.5" /> Registro en Sistema
                        </CardDescription>
                        <CardTitle class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatDate(props.estado.created_at) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Actualizado: {{ formatDate(props.estado.updated_at) }}
                    </CardContent>
                </Card>
            </div>

            <!-- SECCIÓN: ENTIDADES HIJAS (MUNICIPIOS) -->
            <Card class="shadow-xs border border-border">
                <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <Building2 class="h-5 w-5 text-primary" />
                            <span>Municipios Pertenecientes</span>
                        </CardTitle>
                        <CardDescription>
                            Listado de municipios organizados bajo esta entidad federal.
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div v-if="!props.estado.municipios || props.estado.municipios.length === 0" class="text-center py-12 px-4 space-y-3">
                        <Building2 class="h-10 w-10 text-muted-foreground mx-auto stroke-1" />
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                            No hay municipios registrados para este estado
                        </h3>
                        <p class="text-sm text-muted-foreground max-w-sm mx-auto">
                            Los municipios podrán vincularse a este estado una vez habilitado el maestro de municipios.
                        </p>
                    </div>

                    <div v-else class="divide-y dark:divide-slate-800 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted/40 text-xs text-muted-foreground uppercase">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Nombre del Municipio</th>
                                    <th class="px-6 py-3 font-semibold text-center">Parroquias</th>
                                    <th class="px-6 py-3 font-semibold text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="mun in props.estado.municipios"
                                    :key="mun.id_mun"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-6 py-4 font-mono text-xs text-muted-foreground">
                                        #{{ mun.id_mun }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ mun.nombre_mun }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ mun.parroquias_count ?? 0 }} parroquias
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <StatusSwitch :status="resolverStatusId(mun)" @toggle="toggleEstatusMunicipio(mun)" />
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

