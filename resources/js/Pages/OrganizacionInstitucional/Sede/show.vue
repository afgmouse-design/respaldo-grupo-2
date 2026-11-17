<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Sede Universitaria (Módulo 2 - Organización Institucional y Sedes).
 *
 * Propósito: Página independiente dedicada a visualizar la ficha integral de un campus o núcleo,
 * incluyendo su vinculación institucional, marco normativo, ubicación geográfica y auditoría.
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado de la sede.
 * - [Actualización sogac_mio]: Uso de `condicion_sede`, `status_id` (`@/lib/status`) y `users.username`.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { esActivo } from '@/lib/status';
import {
    Building,
    ArrowLeft,
    Pencil,
    Power,
    MapPin,
    GraduationCap,
    Phone,
    Mail,
    FileText,
    Clock,
    User as UserIcon,
    Tag,
} from 'lucide-vue-next';

interface UniversidadRelacion {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
}

interface LocalidadRelacion {
    id_loc: number;
    nombre_loc: string;
    parroquia?: {
        id_par: number;
        nombre_par: string;
        municipio?: {
            id_mun: number;
            nombre_mun: string;
            estado?: {
                id_esd: number;
                nombre_esd: string;
                pais?: {
                    id_pai: number;
                    nombre_pai: string;
                };
            };
        };
    };
}

interface CondicionRelacion {
    id_cse: number;
    nombre_cse: string;
}

interface UserRelacion {
    id: number;
    username: string;
    name?: string;
    email: string;
}

interface SedeModel {
    id_sed: number;
    nombre_sed: string;
    siglas_sed?: string | null;
    codigo_ministerio_sed?: string | null;
    direccion_sed?: string | null;
    telefono_sed?: string | null;
    correo_sed?: string | null;
    gaceta_sed?: string | null;
    fecha_gaceta_sed?: string | null;
    resolucion_sed?: string | null;
    fecha_resolucion_sed?: string | null;
    fecha_inicio_sed?: string | null;
    fecha_fin_sed?: string | null;
    status_id?: number;
    status?: boolean | number;
    universidad?: UniversidadRelacion | null;
    localidad?: LocalidadRelacion | null;
    condicion_sede?: CondicionRelacion | null;
    condicion?: CondicionRelacion | null;
    creador?: UserRelacion | null;
    actualizador?: UserRelacion | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    sede: SedeModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('organizacion.sede.index') },
    { title: 'Sedes', subtitle: 'Catálogo de Sedes', href: route('organizacion.sede.index') },
    { title: props.sede.siglas_sed || props.sede.nombre_sed, subtitle: 'Ficha Técnica', href: route('organizacion.sede.show', props.sede.id_sed) },
];

const formatDate = (dateString?: string | null) => {
    if (!dateString) return 'No registrado';
    return new Date(dateString).toLocaleDateString('es-VE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (isoString?: string | null) => {
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
    const accion = esActivo(props.sede) ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la sede "${props.sede.nombre_sed}"?`)) {
        router.patch(route('organizacion.sede.status', props.sede.id_sed), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Ficha: ${props.sede.nombre_sed} - Organización Institucional`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y ACCIONES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('organizacion.sede.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ props.sede.nombre_sed }}
                        </h1>
                        <StatusSwitch :status="esActivo(props.sede)" @toggle="toggleEstatus" />
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Ficha descriptiva completa del núcleo o campus universitario.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('organizacion.sede.edit', props.sede.id_sed)">
                            <Pencil class="h-4 w-4 mr-1.5" />
                            Editar Información
                        </Link>
                    </Button>

                    <Button
                        variant="outline"
                        size="sm"
                        :class="[
                            esActivo(props.sede)
                                ? 'text-rose-600 border-rose-200 hover:bg-rose-50'
                                : 'text-emerald-600 border-emerald-200 hover:bg-emerald-50'
                        ]"
                        @click="toggleEstatus"
                    >
                        <Power class="h-4 w-4 mr-1.5" />
                        {{ esActivo(props.sede) ? 'Desactivar Sede' : 'Activar Sede' }}
                    </Button>
                </div>
            </div>

            <!-- CUADRÍCULA DE INFORMACIÓN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- COLUMNA 1 Y 2: DATOS GENERALES Y MARCO LEGAL -->
                <div class="md:col-span-2 space-y-6">
                    <!-- IDENTIFICACIÓN Y VINCULACIÓN -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Building class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Identificación y Dependencia Institucional</CardTitle>
                                    <CardDescription>Universidad matriz, condición funcional y ubicación</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <dt class="font-medium text-muted-foreground">Universidad Matriz</dt>
                                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <GraduationCap class="h-4 w-4 text-muted-foreground shrink-0" />
                                        <span>{{ props.sede.universidad?.nombre_uni ?? 'Sin asignar' }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Siglas de la Sede</dt>
                                    <dd class="mt-1 font-mono font-semibold text-slate-900 dark:text-slate-100">
                                        {{ props.sede.siglas_sed ?? 'No asignadas' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Condición / Ámbito</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <Tag class="h-4 w-4 text-muted-foreground shrink-0" />
                                        <span>{{ (props.sede.condicion_sede ?? props.sede.condicion)?.nombre_cse ?? 'No especificada' }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Código MPPEU / OPSU</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.sede.codigo_ministerio_sed ?? 'No asignado' }}
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="font-medium text-muted-foreground">Localización Geográfica</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <MapPin class="h-4 w-4 text-muted-foreground shrink-0" />
                                        <template v-if="props.sede.localidad">
                                            <span>
                                                {{ props.sede.localidad.nombre_loc }}
                                                <template v-if="props.sede.localidad.parroquia?.municipio?.estado">
                                                    , {{ props.sede.localidad.parroquia.municipio.estado.nombre_esd }}
                                                </template>
                                            </span>
                                        </template>
                                        <span v-else class="text-muted-foreground italic">No asignada</span>
                                    </dd>
                                </div>
                                <div v-if="props.sede.direccion_sed" class="sm:col-span-2">
                                    <dt class="font-medium text-muted-foreground">Dirección Física</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.sede.direccion_sed }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- MARCO LEGAL Y NORMATIVO -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Marco Legal y Normativo</CardTitle>
                                    <CardDescription>Resoluciones y decretos que autorizan el funcionamiento</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <dt class="font-medium text-muted-foreground">Resolución de Creación</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.sede.resolucion_sed ?? 'No registrada' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Fecha de la Resolución</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.sede.fecha_resolucion_sed) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Gaceta Oficial</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.sede.gaceta_sed ?? 'No registrada' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Fecha de Gaceta</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.sede.fecha_gaceta_sed) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Inicio de Actividades</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.sede.fecha_inicio_sed) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Cese de Operaciones</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.sede.fecha_fin_sed) }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <!-- COLUMNA 3: CONTACTO Y AUDITORÍA -->
                <div class="space-y-6">
                    <!-- CONTACTO -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Canales de Contacto</CardTitle>
                                    <CardDescription>Comunicación directa con la sede</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <Phone class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div>
                                    <div class="font-medium text-muted-foreground text-xs">Teléfono</div>
                                    <div class="text-slate-900 dark:text-slate-100">
                                        {{ props.sede.telefono_sed ?? 'No disponible' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <Mail class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div>
                                    <div class="font-medium text-muted-foreground text-xs">Correo Electrónico</div>
                                    <div class="text-slate-900 dark:text-slate-100">
                                        <a
                                            v-if="props.sede.correo_sed"
                                            :href="`mailto:${props.sede.correo_sed}`"
                                            class="text-primary hover:underline"
                                        >
                                            {{ props.sede.correo_sed }}
                                        </a>
                                        <span v-else class="text-muted-foreground italic">No disponible</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- AUDITORÍA -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Clock class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Auditoría</CardTitle>
                                    <CardDescription>Trazabilidad histórica</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3 text-xs">
                            <div>
                                <span class="font-medium text-muted-foreground block">Fecha de Registro:</span>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ formatDateTime(props.sede.created_at) }}
                                </span>
                            </div>
                            <div v-if="props.sede.creador">
                                <span class="font-medium text-muted-foreground block">Registrado por:</span>
                                <span class="text-slate-800 dark:text-slate-200 flex items-center gap-1 mt-0.5">
                                    <UserIcon class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ props.sede.creador.username ?? props.sede.creador.name }} ({{ props.sede.creador.email }})
                                </span>
                            </div>
                            <div v-if="props.sede.updated_at">
                                <span class="font-medium text-muted-foreground block">Última Modificación:</span>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ formatDateTime(props.sede.updated_at) }}
                                </span>
                            </div>
                            <div v-if="props.sede.actualizador">
                                <span class="font-medium text-muted-foreground block">Actualizado por:</span>
                                <span class="text-slate-800 dark:text-slate-200 flex items-center gap-1 mt-0.5">
                                    <UserIcon class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ props.sede.actualizador.username ?? props.sede.actualizador.name }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

