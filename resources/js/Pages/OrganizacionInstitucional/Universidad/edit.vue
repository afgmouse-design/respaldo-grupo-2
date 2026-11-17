<script setup lang="ts">
/**
 * Vista Standalone: Edición de Universidad (Módulo 2 - Organización Institucional).
 *
 * Propósito: Página independiente con formulario precargado para actualizar
 * la información de la institución universitaria matriz en el SOGAC. Cumple estrictamente la regla de CERO MODALES.
 *
 * Historial de Modificaciones:
 * - [Ajuste de Navegación y Alertas]: Integración de SuccessAlert y useFlashAlert para retroalimentación
 *   tras la actualización, y ajuste de enlaces de retorno hacia Dashboard.
 * - [Ajuste de Campos]: Eliminación de los campos 'Fecha de Inicio de Actividades' y 'Fecha de Cese'
 *   del formulario de edición por solicitud de simplificación.
 * - [Estandarización de Título y Breadcrumb]: Se acortaron los títulos y migas de pan a 'Universidad' eliminando la concatenación de nombres institucionales extensos.
 * - [Alineación sogac_mio]: Uso de `url_pagina_uni`, `cse_id`, `dpi_id`, `esd_id` y `status_id` mediante `@/lib/status`.
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Building2,
    Eye,
    FileText,
    Phone,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import { useFlashAlert } from '@/composables/useFlashAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo, resolverStatusId } from '@/lib/status';
import { formatNombre } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types/navigation';

interface LocalidadItem {
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
            };
        };
    };
}

interface UniversidadModel {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
    codigo_ministerio_uni?: string | null;
    rif_uni?: string | null;
    telefono_uni?: string | null;
    fax_uni?: string | null;
    correo_uni?: string | null;
    url_pagina_uni?: string | null;
    gaceta_uni?: string | null;
    fecha_gaceta_uni?: string | null;
    decreto_uni?: string | null;
    fecha_decreto_uni?: string | null;
    fecha_inicio_uni?: string | null;
    fecha_fin_uni?: string | null;
    loc_id?: number | null;
    esd_id?: number | null;
    dpi_id?: number | null;
    cse_id?: number | null;
    status_id?: number;
    status?: boolean | number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    universidad: UniversidadModel;
    localidades: LocalidadItem[];
    condiciones?: Array<{ id_cse: number; nombre_cse: string }>;
    dependencias?: Array<{ id_dpi: number; nombre_dpi: string }>;
    estados?: Array<{ id_esd: number; nombre_esd: string }>;
}>();

const { showSuccessAlert, message, closeAlert } = useFlashAlert();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('dashboard') },
    { title: 'Universidad', subtitle: 'Modificación', href: route('organizacion.universidad.edit', props.universidad.id_uni) },
];

const form = useForm({
    nombre_uni: props.universidad.nombre_uni,
    siglas_uni: props.universidad.siglas_uni ?? '',
    codigo_ministerio_uni: props.universidad.codigo_ministerio_uni ?? '',
    rif_uni: props.universidad.rif_uni ?? '',
    telefono_uni: props.universidad.telefono_uni ?? '',
    correo_uni: props.universidad.correo_uni ?? '',
    url_pagina_uni: props.universidad.url_pagina_uni ?? '',
    gaceta_uni: props.universidad.gaceta_uni ?? '',
    fecha_gaceta_uni: props.universidad.fecha_gaceta_uni ?? '',
    decreto_uni: props.universidad.decreto_uni ?? '',
    fecha_decreto_uni: props.universidad.fecha_decreto_uni ?? '',
    loc_id: props.universidad.loc_id ?? '',
    esd_id: props.universidad.esd_id ?? '',
    dpi_id: props.universidad.dpi_id ?? '',
    cse_id: props.universidad.cse_id ?? '',
    status_id: resolverStatusId(props.universidad),
});

const submit = () => {
    form.put(route('organizacion.universidad.update', props.universidad.id_uni), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Universidad - Editar Información" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ALERTA FLASH -->
            <SuccessAlert
                v-if="showSuccessAlert"
                :message="message"
                @close="closeAlert"
            />

            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900" title="Volver al Dashboard">
                            <Link :href="route('dashboard')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Universidad
                        </h1>
                        <Badge
                            :variant="esActivo(props.universidad) ? 'default' : 'outline'"
                            :class="[
                                'ml-2',
                                esActivo(props.universidad)
                                    ? 'bg-emerald-600 text-white'
                                    : 'border-rose-300 text-rose-600 bg-rose-50 dark:bg-rose-950/20'
                            ]"
                        >
                            {{ esActivo(props.universidad) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los atributos institucionales, normativos o de contacto de la universidad matriz.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('organizacion.universidad.show', props.universidad.id_uni)">
                            <Eye class="h-4 w-4 mr-1.5" />
                            Ver Ficha
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <!-- MENSAJE DE ERROR GLOBAL -->
                    <div
                        v-if="form.errors.error"
                        class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>{{ form.errors.error }}</span>
                    </div>

                    <!-- SECCIÓN 1: DATOS INSTITUCIONALES -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Building2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Identificación Institucional</CardTitle>
                                    <CardDescription>
                                        Nombre oficial y códigos de identificación ministerial y fiscal.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- NOMBRE LEGAL (Con filtro de números y Title Case) -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="nombre_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Nombre Oficial de la Universidad <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_uni"
                                    :model-value="form.nombre_uni"
                                    type="text"
                                    required
                                    placeholder="Nombre oficial..."
                                    :class="{ 'border-red-500': form.errors.nombre_uni }"
                                    @input="form.nombre_uni = formatNombre(($event.target as HTMLInputElement).value)"
                                />
                                <span v-if="form.errors.nombre_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.nombre_uni }}
                                </span>
                            </div>

                            <!-- SIGLAS -->
                            <div class="space-y-2">
                                <Label for="siglas_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Siglas Institucionales
                                </Label>
                                <Input
                                    id="siglas_uni"
                                    v-model="form.siglas_uni"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ej. UPTP"
                                    :class="{ 'border-red-500': form.errors.siglas_uni }"
                                    @input="form.siglas_uni = form.siglas_uni.toUpperCase()"
                                />
                                <span v-if="form.errors.siglas_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.siglas_uni }}
                                </span>
                            </div>

                            <!-- RIF -->
                            <div class="space-y-2">
                                <Label for="rif_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    RIF Institucional
                                </Label>
                                <Input
                                    id="rif_uni"
                                    v-model="form.rif_uni"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ej. G-20000000-0"
                                    :class="{ 'border-red-500': form.errors.rif_uni }"
                                    @input="form.rif_uni = form.rif_uni.toUpperCase()"
                                />
                                <span v-if="form.errors.rif_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.rif_uni }}
                                </span>
                            </div>

                            <!-- CÓDIGO MINISTERIAL -->
                            <div class="space-y-2">
                                <Label for="codigo_ministerio_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Código OPSU / MPPEU
                                </Label>
                                <Input
                                    id="codigo_ministerio_uni"
                                    v-model="form.codigo_ministerio_uni"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ej. MPPEU-045"
                                    :class="{ 'border-red-500': form.errors.codigo_ministerio_uni }"
                                />
                                <span v-if="form.errors.codigo_ministerio_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.codigo_ministerio_uni }}
                                </span>
                            </div>

                            <!-- LOCALIDAD DE RECTORADO -->
                            <div class="space-y-2">
                                <Label for="loc_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Localidad de la Sede Rectoral
                                </Label>
                                <select
                                    id="loc_id"
                                    v-model="form.loc_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.loc_id }"
                                >
                                    <option value="">Seleccione una localidad...</option>
                                    <option
                                        v-for="loc in props.localidades"
                                        :key="loc.id_loc"
                                        :value="loc.id_loc"
                                    >
                                        {{ loc.nombre_loc }}
                                        <template v-if="loc.parroquia?.municipio?.estado">
                                            ({{ loc.parroquia.municipio.estado.nombre_esd }})
                                        </template>
                                    </option>
                                </select>
                                <span v-if="form.errors.loc_id" class="text-xs text-red-500 block">
                                    {{ form.errors.loc_id }}
                                </span>
                            </div>

                            <!-- ESTADO -->
                            <div v-if="props.estados?.length" class="space-y-2">
                                <Label for="esd_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Estado Geográfico
                                </Label>
                                <select
                                    id="esd_id"
                                    v-model="form.esd_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.esd_id }"
                                >
                                    <option value="">Seleccione un estado...</option>
                                    <option v-for="est in props.estados" :key="est.id_esd" :value="est.id_esd">
                                        {{ est.nombre_esd }}
                                    </option>
                                </select>
                                <span v-if="form.errors.esd_id" class="text-xs text-red-500 block">
                                    {{ form.errors.esd_id }}
                                </span>
                            </div>

                            <!-- DEPENDENCIA INSTITUCIONAL -->
                            <div v-if="props.dependencias?.length" class="space-y-2">
                                <Label for="dpi_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Dependencia Institucional
                                </Label>
                                <select
                                    id="dpi_id"
                                    v-model="form.dpi_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.dpi_id }"
                                >
                                    <option value="">Seleccione dependencia...</option>
                                    <option v-for="dpi in props.dependencias" :key="dpi.id_dpi" :value="dpi.id_dpi">
                                        {{ dpi.nombre_dpi }}
                                    </option>
                                </select>
                                <span v-if="form.errors.dpi_id" class="text-xs text-red-500 block">
                                    {{ form.errors.dpi_id }}
                                </span>
                            </div>

                            <!-- CONDICIÓN DE SEDE -->
                            <div v-if="props.condiciones?.length" class="space-y-2">
                                <Label for="cse_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Condición de Sede
                                </Label>
                                <select
                                    id="cse_id"
                                    v-model="form.cse_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.cse_id }"
                                >
                                    <option value="">Seleccione condición...</option>
                                    <option v-for="cse in props.condiciones" :key="cse.id_cse" :value="cse.id_cse">
                                        {{ cse.nombre_cse }}
                                    </option>
                                </select>
                                <span v-if="form.errors.cse_id" class="text-xs text-red-500 block">
                                    {{ form.errors.cse_id }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECCIÓN 2: BASE LEGAL Y NORMATIVA -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Marco Legal y Fechas Oficiales</CardTitle>
                                    <CardDescription>
                                        Gaceta Oficial, Decreto de creación o transformación y cronología.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- GACETA -->
                            <div class="space-y-2">
                                <Label for="gaceta_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Gaceta Oficial N°
                                </Label>
                                <Input
                                    id="gaceta_uni"
                                    v-model="form.gaceta_uni"
                                    type="text"
                                    placeholder="Ej. Gaceta Extraordinaria N° 40.123"
                                    :class="{ 'border-red-500': form.errors.gaceta_uni }"
                                />
                                <span v-if="form.errors.gaceta_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.gaceta_uni }}
                                </span>
                            </div>

                            <!-- FECHA GACETA -->
                            <div class="space-y-2">
                                <Label for="fecha_gaceta_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha de la Gaceta Oficial
                                </Label>
                                <Input
                                    id="fecha_gaceta_uni"
                                    v-model="form.fecha_gaceta_uni"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_gaceta_uni }"
                                />
                                <span v-if="form.errors.fecha_gaceta_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_gaceta_uni }}
                                </span>
                            </div>

                            <!-- DECRETO -->
                            <div class="space-y-2">
                                <Label for="decreto_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Decreto de Creación N°
                                </Label>
                                <Input
                                    id="decreto_uni"
                                    v-model="form.decreto_uni"
                                    type="text"
                                    placeholder="Ej. Decreto Presidencial N° 1.234"
                                    :class="{ 'border-red-500': form.errors.decreto_uni }"
                                />
                                <span v-if="form.errors.decreto_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.decreto_uni }}
                                </span>
                            </div>

                            <!-- FECHA DECRETO -->
                            <div class="space-y-2">
                                <Label for="fecha_decreto_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha del Decreto
                                </Label>
                                <Input
                                    id="fecha_decreto_uni"
                                    v-model="form.fecha_decreto_uni"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_decreto_uni }"
                                />
                                <span v-if="form.errors.fecha_decreto_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_decreto_uni }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECCIÓN 3: CANALES DE CONTACTO -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Canales de Contacto y Web</CardTitle>
                                    <CardDescription>
                                        Teléfono, correo electrónico institucional y dirección web.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- TELÉFONO -->
                            <div class="space-y-2">
                                <Label for="telefono_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Teléfono Central
                                </Label>
                                <Input
                                    id="telefono_uni"
                                    v-model="form.telefono_uni"
                                    type="text"
                                    placeholder="Ej. +58 257 2511234"
                                    :class="{ 'border-red-500': form.errors.telefono_uni }"
                                />
                                <span v-if="form.errors.telefono_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.telefono_uni }}
                                </span>
                            </div>

                            <!-- CORREO -->
                            <div class="space-y-2">
                                <Label for="correo_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Correo Institucional
                                </Label>
                                <Input
                                    id="correo_uni"
                                    v-model="form.correo_uni"
                                    type="email"
                                    placeholder="Ej. rectorado@uptp.edu.ve"
                                    :class="{ 'border-red-500': form.errors.correo_uni }"
                                />
                                <span v-if="form.errors.correo_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.correo_uni }}
                                </span>
                            </div>

                            <!-- SITIO WEB -->
                            <div class="space-y-2">
                                <Label for="url_pagina_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Portal Web Oficial
                                </Label>
                                <Input
                                    id="url_pagina_uni"
                                    v-model="form.url_pagina_uni"
                                    type="url"
                                    placeholder="Ej. https://www.uptp.edu.ve"
                                    :class="{ 'border-red-500': form.errors.url_pagina_uni }"
                                />
                                <span v-if="form.errors.url_pagina_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.url_pagina_uni }}
                                </span>
                            </div>
                        </CardContent>

                        <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                            <CrudFormFooter
                                :cancel-href="route('dashboard')"
                                :is-edit="true"
                                :processing="form.processing"
                            />
                        </CardFooter>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

