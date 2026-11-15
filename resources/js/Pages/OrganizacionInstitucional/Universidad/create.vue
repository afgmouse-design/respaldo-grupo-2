<script setup lang="ts">
/**
 * Vista Standalone: Creación de Universidad (Módulo 2 - Organización Institucional).
 *
 * Propósito: Página independiente con formulario estructurado para registrar la institución
 * matriz de educación universitaria en el SOGAC. Cumple estrictamente la regla de CERO MODALES.
 *
 * Historial de Modificaciones:
 * - [Ajuste de Navegación]: Se actualizaron breadcrumbs y botones de retorno hacia Dashboard
 *   para evitar bucles de redirección hacia index al ser el formulario de entrada directo.
 * - [Ajuste de Campos]: Eliminación de los campos 'Fecha de Inicio de Actividades' y 'Fecha de Cese'
 *   del formulario de creación por solicitud de simplificación.
 */
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
    GraduationCap,
    ArrowLeft,
    Save,
    AlertCircle,
    Building2,
    FileText,
    Calendar,
    Phone,
    Mail,
    Globe,
    MapPin,
} from 'lucide-vue-next';

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

const props = defineProps<{
    localidades: LocalidadItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('dashboard') },
    { title: 'Universidad', subtitle: 'Institución Matriz', href: route('organizacion.universidad.create') },
    { title: 'Registrar Universidad', subtitle: 'Nuevo Registro', href: route('organizacion.universidad.create') },
];

const form = useForm({
    nombre_uni: '',
    siglas_uni: '',
    codigo_ministerio_uni: '',
    rif_uni: '',
    telefono_uni: '',
    correo_uni: '',
    sitio_web_uni: '',
    gaceta_uni: '',
    fecha_gaceta_uni: '',
    decreto_uni: '',
    fecha_decreto_uni: '',
    loc_id: '' as string | number,
});

const submit = () => {
    form.post(route('organizacion.universidad.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Universidad - Organización Institucional" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
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
                            Registrar Institución Universitaria
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Complete los datos legales, normativos y de contacto de la universidad matriz.
                    </p>
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
                                    placeholder="Ej. Universidad Politécnica Territorial Del Estado Portuguesa"
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
                                <Label for="sitio_web_uni" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Portal Web Oficial
                                </Label>
                                <Input
                                    id="sitio_web_uni"
                                    v-model="form.sitio_web_uni"
                                    type="url"
                                    placeholder="Ej. https://www.uptp.edu.ve"
                                    :class="{ 'border-red-500': form.errors.sitio_web_uni }"
                                />
                                <span v-if="form.errors.sitio_web_uni" class="text-xs text-red-500 block">
                                    {{ form.errors.sitio_web_uni }}
                                </span>
                            </div>
                        </CardContent>

                        <CardFooter class="flex items-center justify-end gap-3 border-t pt-4">
                            <Button as-child variant="outline">
                                <Link :href="route('dashboard')">
                                    Cancelar
                                </Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="shadow-sm">
                                <Save class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Guardando...' : 'Guardar' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

