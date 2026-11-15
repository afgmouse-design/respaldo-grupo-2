<script setup lang="ts">
/**
 * Vista Standalone: Detalle de País (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados de un país y explorar su árbol territorial descendente (estados vinculados).
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado del país y en la tabla de estados subordinados.
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
    Globe,
    ArrowLeft,
    Pencil,
    Power,
    MapPin,
    Plus,
    Calendar,
    Hash,
    Layers,
    Eye,
} from 'lucide-vue-next';

interface EstadoSubordinado {
    id_esd: number;
    nombre_esd: string;
    status: boolean;
    municipios_count?: number;
    created_at?: string;
}

interface PaisModel {
    id_pai: number;
    nombre_pai: string;
    status: boolean;
    created_at?: string;
    updated_at?: string;
    estados?: EstadoSubordinado[];
}

const props = defineProps<{
    pais: PaisModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Países', subtitle: 'Catálogo Maestro', href: route('geografia.pais.index') },
    { title: props.pais.nombre_pai, subtitle: 'Ficha Detallada', href: route('geografia.pais.show', props.pais.id_pai) },
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
    const accion = props.pais.status ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el país "${props.pais.nombre_pai}"?`)) {
        router.delete(route('geografia.pais.destroy', props.pais.id_pai));
    }
};

const toggleEstatusEstado = async (estado: EstadoSubordinado) => {
    const accion = estado.status ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} el estado "${estado.nombre_esd}"?`)) {
        router.patch(route('geografia.estado.status', estado.id_esd), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.pais.nombre_pai} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES PRINCIPALES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.pais.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-3">
                            <span>{{ props.pais.nombre_pai }}</span>
                            <StatusSwitch :status="props.pais.status" @toggle="toggleEstatus" />
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Código de identificación territorial: #{{ props.pais.id_pai }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.pais.edit', props.pais.id_pai)">
                            <Pencil class="h-4 w-4 mr-2" />
                            Editar País
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="props.pais.status ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700'"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-2" />
                        {{ props.pais.status ? 'Desactivar' : 'Activar' }}
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
                            #{{ props.pais.id_pai }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Llave primaria única en la tabla <code class="text-xs bg-muted px-1 py-0.5 rounded">pais</code>
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Layers class="h-3.5 w-3.5" /> Estados Federados
                        </CardDescription>
                        <CardTitle class="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ props.pais.estados?.length ?? 0 }} entidades
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Total de estados vinculados en la jerarquía
                    </CardContent>
                </Card>

                <Card class="shadow-xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="flex items-center gap-1.5 text-xs">
                            <Calendar class="h-3.5 w-3.5" /> Registro en Sistema
                        </CardDescription>
                        <CardTitle class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatDate(props.pais.created_at) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Actualizado: {{ formatDate(props.pais.updated_at) }}
                    </CardContent>
                </Card>
            </div>

            <!-- SECCIÓN: ENTIDADES HIJAS (ESTADOS FEDERADOS) -->
            <Card class="shadow-xs border border-border">
                <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <MapPin class="h-5 w-5 text-primary" />
                            <span>Estados Federados Pertenecientes</span>
                        </CardTitle>
                        <CardDescription>
                            Listado de provincias y estados adscritos a este país.
                        </CardDescription>
                    </div>

                    <Button as-child size="sm">
                        <Link :href="route('geografia.estado.create')">
                            <Plus class="h-4 w-4 mr-1.5" />
                            Nuevo Estado
                        </Link>
                    </Button>
                </CardHeader>

                <CardContent class="p-0">
                    <div v-if="!props.pais.estados || props.pais.estados.length === 0" class="text-center py-12 px-4 space-y-3">
                        <MapPin class="h-10 w-10 text-muted-foreground mx-auto stroke-1" />
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                            No hay estados registrados para este país
                        </h3>
                        <p class="text-sm text-muted-foreground max-w-sm mx-auto">
                            Comience agregando el primer estado o entidad federal para este país.
                        </p>
                        <Button as-child variant="outline" size="sm" class="mt-2">
                            <Link :href="route('geografia.estado.create')">
                                <Plus class="h-4 w-4 mr-1.5" /> Registrar Estado
                            </Link>
                        </Button>
                    </div>

                    <div v-else class="divide-y dark:divide-slate-800 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted/40 text-xs text-muted-foreground uppercase">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">ID</th>
                                    <th class="px-6 py-3 font-semibold">Nombre del Estado</th>
                                    <th class="px-6 py-3 font-semibold text-center">Municipios</th>
                                    <th class="px-6 py-3 font-semibold text-center">Estatus</th>
                                    <th class="px-6 py-3 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="estado in props.pais.estados"
                                    :key="estado.id_esd"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-6 py-4 font-mono text-xs text-muted-foreground">
                                        #{{ estado.id_esd }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ estado.nombre_esd }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ estado.municipios_count ?? 0 }} municipios
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <StatusSwitch :status="estado.status" @toggle="toggleEstatusEstado(estado)" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-primary" title="Ver ficha del estado">
                                                <Link :href="route('geografia.estado.show', estado.id_esd)">
                                                    <Eye class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-amber-600 hover:text-amber-700" title="Editar estado">
                                                <Link :href="route('geografia.estado.edit', estado.id_esd)">
                                                    <Pencil class="h-4 w-4" />
                                                </Link>
                                            </Button>
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

