<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Universidad (Módulo 2 - Organización Institucional).
 *
 * Propósito: Página independiente dedicada exclusivamente a visualizar los atributos
 * detallados, marco jurídico, canales de contacto y trazabilidad de la universidad matriz.
 *
 * Historial de Modificaciones:
 * - [Actualización]: Integración del componente `<StatusSwitch>` en el encabezado de la universidad.
 * - [Ajuste de Navegación]: Actualización de breadcrumbs y retorno hacia el formulario de edición.
 * - [Ajuste de Campos]: Eliminación de visualización de inicio de actividades y cese de operaciones.
 * - [Eliminación de Botón Redundante]: Se removió el botón "Desactivar Institución" para centralizar el control de estatus exclusivamente en el componente `<StatusSwitch>`.
 * - [Estandarización de Título]: Se simplificó el título principal de la vista a 'Universidad' por directiva del usuario, manteniendo la denominación completa institucional en el bloque de datos generales.
 */
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import StatusSwitch from '@/components/ui/StatusSwitch.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useTableExport } from '@/composables/useTableExport';
import {
    GraduationCap,
    ArrowLeft,
    Pencil,
    Printer,
    MapPin,
    Calendar,
    Phone,
    Mail,
    Globe,
    FileText,
    Building2,
    Clock,
    User as UserIcon,
    ExternalLink,
} from 'lucide-vue-next';

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

interface UserRelacion {
    id: number;
    name: string;
    email: string;
}

interface UniversidadModel {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
    codigo_ministerio_uni?: string | null;
    rif_uni?: string | null;
    telefono_uni?: string | null;
    correo_uni?: string | null;
    sitio_web_uni?: string | null;
    gaceta_uni?: string | null;
    fecha_gaceta_uni?: string | null;
    decreto_uni?: string | null;
    fecha_decreto_uni?: string | null;
    fecha_inicio_uni?: string | null;
    fecha_fin_uni?: string | null;
    status: boolean;
    localidad?: LocalidadRelacion | null;
    creador?: UserRelacion | null;
    actualizador?: UserRelacion | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    universidad: UniversidadModel;
}>();

const { confirmDialog } = useConfirmDialog();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('dashboard') },
    { title: 'Universidad', subtitle: 'Ficha Detallada', href: route('organizacion.universidad.show', props.universidad.id_uni) },
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
    const accion = props.universidad.status ? 'desactivar' : 'activar';
    if (await confirmDialog(`¿Está seguro de que desea ${accion} la universidad "${props.universidad.nombre_uni}"?`)) {
        router.patch(route('organizacion.universidad.status', props.universidad.id_uni), {}, {
            preserveScroll: true,
        });
    }
};

const { printCurrentPage } = useTableExport();
const imprimirFicha = () => {
    printCurrentPage();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Universidad - Ficha Detallada" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6 printable-area">
            <!-- ENCABEZADO Y ACCIONES -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900 print:hidden" title="Volver al Formulario">
                            <Link :href="route('organizacion.universidad.edit', props.universidad.id_uni)">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Universidad
                        </h1>
                        <StatusSwitch class="print:hidden" :status="props.universidad.status" @toggle="toggleEstatus" />
                    </div>
                    <p class="text-sm text-muted-foreground ml-10 print:ml-0">
                        Ficha integral de la institución rectora y datos del decreto fundacional.
                    </p>
                </div>

                <div class="flex items-center gap-2 print:hidden">
                    <Button variant="outline" size="sm" class="gap-1.5" @click="imprimirFicha">
                        <Printer class="h-4 w-4" />
                        Imprimir Ficha
                    </Button>

                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('organizacion.universidad.edit', props.universidad.id_uni)">
                            <Pencil class="h-4 w-4 mr-1.5" />
                            Editar Información
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- CUADÍCULA PRINCIPAL DE INFORMACIÓN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- COLUMNA 1 & 2: INFORMACIÓN CENTRAL Y MARCO LEGAL -->
                <div class="md:col-span-2 space-y-6">
                    <!-- TARJETA: IDENTIFICACIÓN GENERAL -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Building2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Datos Generales e Identificación</CardTitle>
                                    <CardDescription>Códigos ministeriales y localización geográfica del rectorado</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <div class="sm:col-span-2">
                                    <dt class="font-medium text-muted-foreground">Nombre Institucional</dt>
                                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.nombre_uni }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Siglas Oficiales</dt>
                                    <dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.siglas_uni ?? 'No especificadas' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">RIF Institucional</dt>
                                    <dd class="mt-1 font-semibold font-mono text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.rif_uni ?? 'No registrado' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Código MPPEU / OPSU</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.codigo_ministerio_uni ?? 'No asignado' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Ubicación Sede Rectoral</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <MapPin class="h-4 w-4 text-muted-foreground shrink-0" />
                                        <template v-if="props.universidad.localidad">
                                            <span>
                                                {{ props.universidad.localidad.nombre_loc }}
                                                <template v-if="props.universidad.localidad.parroquia?.municipio?.estado">
                                                    , {{ props.universidad.localidad.parroquia.municipio.estado.nombre_esd }}
                                                </template>
                                            </span>
                                        </template>
                                        <span v-else class="text-muted-foreground italic">No asignada</span>
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- TARJETA: MARCO JURÍDICO -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Marco Legal y Normativo</CardTitle>
                                    <CardDescription>Resoluciones y decretos en Gaceta Oficial</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <dt class="font-medium text-muted-foreground">Gaceta Oficial</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.gaceta_uni ?? 'No registrada' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Fecha de Gaceta Oficial</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.universidad.fecha_gaceta_uni) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Decreto de Creación</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.decreto_uni ?? 'No registrado' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-muted-foreground">Fecha del Decreto</dt>
                                    <dd class="mt-1 text-slate-900 dark:text-slate-100">
                                        {{ formatDate(props.universidad.fecha_decreto_uni) }}
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <!-- COLUMNA 3: CONTACTO Y TRAZABILIDAD -->
                <div class="space-y-6">
                    <!-- TARJETA: CONTACTO INSTITUCIONAL -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Canales de Contacto</CardTitle>
                                    <CardDescription>Vías de comunicación directa</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <Phone class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div>
                                    <div class="font-medium text-muted-foreground text-xs">Teléfono</div>
                                    <div class="text-slate-900 dark:text-slate-100">
                                        {{ props.universidad.telefono_uni ?? 'No disponible' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <Mail class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div>
                                    <div class="font-medium text-muted-foreground text-xs">Correo Electrónico</div>
                                    <div class="text-slate-900 dark:text-slate-100">
                                        <a
                                            v-if="props.universidad.correo_uni"
                                            :href="`mailto:${props.universidad.correo_uni}`"
                                            class="text-primary hover:underline"
                                        >
                                            {{ props.universidad.correo_uni }}
                                        </a>
                                        <span v-else class="text-muted-foreground italic">No disponible</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2">
                                <Globe class="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                                <div>
                                    <div class="font-medium text-muted-foreground text-xs">Sitio Web Oficial</div>
                                    <div>
                                        <a
                                            v-if="props.universidad.sitio_web_uni"
                                            :href="props.universidad.sitio_web_uni"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-primary hover:underline flex items-center gap-1"
                                        >
                                            <span class="truncate">{{ props.universidad.sitio_web_uni }}</span>
                                            <ExternalLink class="h-3 w-3 shrink-0" />
                                        </a>
                                        <span v-else class="text-muted-foreground italic">No registrado</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- TARJETA: AUDITORÍA Y TRAZABILIDAD -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Clock class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Auditoría</CardTitle>
                                    <CardDescription>Registro histórico del sistema</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-3 text-xs">
                            <div>
                                <span class="font-medium text-muted-foreground block">Fecha de Registro:</span>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ formatDateTime(props.universidad.created_at) }}
                                </span>
                            </div>
                            <div v-if="props.universidad.creador">
                                <span class="font-medium text-muted-foreground block">Registrado por:</span>
                                <span class="text-slate-800 dark:text-slate-200 flex items-center gap-1 mt-0.5">
                                    <UserIcon class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ props.universidad.creador.name }} ({{ props.universidad.creador.email }})
                                </span>
                            </div>
                            <div v-if="props.universidad.updated_at">
                                <span class="font-medium text-muted-foreground block">Última Modificación:</span>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ formatDateTime(props.universidad.updated_at) }}
                                </span>
                            </div>
                            <div v-if="props.universidad.actualizador">
                                <span class="font-medium text-muted-foreground block">Actualizado por:</span>
                                <span class="text-slate-800 dark:text-slate-200 flex items-center gap-1 mt-0.5">
                                    <UserIcon class="h-3.5 w-3.5 text-muted-foreground" />
                                    {{ props.universidad.actualizador.name }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    nav, aside, header, .print\:hidden {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .printable-area {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>

