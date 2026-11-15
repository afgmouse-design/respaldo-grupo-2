<script setup lang="ts">
/**
 * Vista Standalone: Creación de Sede Universitaria (Módulo 2 - Organización Institucional y Sedes).
 *
 * Propósito: Página independiente con formulario estructurado para dar de alta una nueva
 * sede, campus o núcleo institucional en el SOGAC. Cumple con la arquitectura Zero-Modal.
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
    Building,
    ArrowLeft,
    Save,
    AlertCircle,
    GraduationCap,
    FileText,
    Phone,
    MapPin,
    Tag,
} from 'lucide-vue-next';

interface UniversidadItem {
    id_uni: number;
    nombre_uni: string;
    siglas_uni?: string | null;
}

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

interface CondicionItem {
    id_cdn: number;
    nombre_cdn: string;
}

const props = defineProps<{
    universidades: UniversidadItem[];
    localidades: LocalidadItem[];
    condiciones: CondicionItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Organización Institucional', subtitle: 'Módulo Institucional', href: route('organizacion.sede.index') },
    { title: 'Sedes', subtitle: 'Catálogo de Sedes', href: route('organizacion.sede.index') },
    { title: 'Registrar Sede', subtitle: 'Nuevo Registro', href: route('organizacion.sede.create') },
];

const form = useForm({
    nombre_sed: '',
    siglas_sed: '',
    codigo_ministerio_sed: '',
    uni_id: (props.universidades && props.universidades.length > 0 ? props.universidades[0].id_uni : 1) as string | number,
    loc_id: '' as string | number,
    cdn_id: '' as string | number,
    direccion_sed: '',
    telefono_sed: '',
    correo_sed: '',
    gaceta_sed: '',
    fecha_gaceta_sed: '',
    resolucion_sed: '',
    fecha_resolucion_sed: '',
    fecha_inicio_sed: '',
    fecha_fin_sed: '',
});

const submit = () => {
    form.post(route('organizacion.sede.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Sede - Organización Institucional" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('organizacion.sede.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Sede / Núcleo
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Complete los datos de identificación, vinculación institucional y ubicación de la sede.
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

                    <!-- SECCIÓN 1: VINCULACIÓN INSTITUCIONAL Y UBICACIÓN -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Vinculación Institucional y Ubicación</CardTitle>
                                    <CardDescription>
                                        Asigne la ubicación geográfica y la condición funcional del núcleo.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- LOCALIDAD -->
                            <div class="space-y-2">
                                <Label for="loc_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Localidad Geográfica <span class="text-red-500">*</span>
                                </Label>
                                <select
                                    id="loc_id"
                                    v-model="form.loc_id"
                                    required
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

                            <!-- CONDICIÓN INSTITUCIONAL -->
                            <div class="space-y-2">
                                <Label for="cdn_id" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Condición / Ámbito
                                </Label>
                                <select
                                    id="cdn_id"
                                    v-model="form.cdn_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.cdn_id }"
                                >
                                    <option value="">Sin especificar...</option>
                                    <option
                                        v-for="cdn in props.condiciones"
                                        :key="cdn.id_cdn"
                                        :value="cdn.id_cdn"
                                    >
                                        {{ cdn.nombre_cdn }}
                                    </option>
                                </select>
                                <span v-if="form.errors.cdn_id" class="text-xs text-red-500 block">
                                    {{ form.errors.cdn_id }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECCIÓN 2: IDENTIFICACIÓN DE LA SEDE -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Building class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Identificación de la Sede</CardTitle>
                                    <CardDescription>
                                        Denominación oficial, siglas y códigos asignados.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- NOMBRE DE LA SEDE (Con filtro de números y Title Case) -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="nombre_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Denominación Oficial de la Sede <span class="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nombre_sed"
                                    :model-value="form.nombre_sed"
                                    type="text"
                                    required
                                    placeholder="Ej. Sede Acarigua - Araure"
                                    :class="{ 'border-red-500': form.errors.nombre_sed }"
                                    @input="form.nombre_sed = formatNombre(($event.target as HTMLInputElement).value)"
                                />
                                <span v-if="form.errors.nombre_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.nombre_sed }}
                                </span>
                            </div>

                            <!-- SIGLAS -->
                            <div class="space-y-2">
                                <Label for="siglas_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Siglas / Código Corto
                                </Label>
                                <Input
                                    id="siglas_sed"
                                    v-model="form.siglas_sed"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ej. SED-ACA"
                                    :class="{ 'border-red-500': form.errors.siglas_sed }"
                                    @input="form.siglas_sed = form.siglas_sed.toUpperCase().replace(/\s+/g, '')"
                                />
                                <span v-if="form.errors.siglas_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.siglas_sed }}
                                </span>
                            </div>

                            <!-- CÓDIGO MINISTERIAL -->
                            <div class="space-y-2">
                                <Label for="codigo_ministerio_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Código OPSU / MPPEU
                                </Label>
                                <Input
                                    id="codigo_ministerio_sed"
                                    v-model="form.codigo_ministerio_sed"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ej. NUC-01"
                                    :class="{ 'border-red-500': form.errors.codigo_ministerio_sed }"
                                />
                                <span v-if="form.errors.codigo_ministerio_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.codigo_ministerio_sed }}
                                </span>
                            </div>

                            <!-- DIRECCIÓN FÍSICA -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="direccion_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Dirección Física Detallada
                                </Label>
                                <textarea
                                    id="direccion_sed"
                                    v-model="form.direccion_sed"
                                    rows="2"
                                    placeholder="Avenida, calle, sector, punto de referencia..."
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs focus:outline-none focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-red-500': form.errors.direccion_sed }"
                                />
                                <span v-if="form.errors.direccion_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.direccion_sed }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECCIÓN 3: MARCO LEGAL Y FECHAS -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Marco Legal y Fechas de Operación</CardTitle>
                                    <CardDescription>
                                        Resoluciones del Consejo Universitario, CNU o Gaceta Oficial.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- RESOLUCIÓN -->
                            <div class="space-y-2">
                                <Label for="resolucion_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Resolución de Aprobación
                                </Label>
                                <Input
                                    id="resolucion_sed"
                                    v-model="form.resolucion_sed"
                                    type="text"
                                    placeholder="Ej. Resolución CU N° 12-2020"
                                    :class="{ 'border-red-500': form.errors.resolucion_sed }"
                                />
                                <span v-if="form.errors.resolucion_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.resolucion_sed }}
                                </span>
                            </div>

                            <!-- FECHA RESOLUCIÓN -->
                            <div class="space-y-2">
                                <Label for="fecha_resolucion_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha de la Resolución
                                </Label>
                                <Input
                                    id="fecha_resolucion_sed"
                                    v-model="form.fecha_resolucion_sed"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_resolucion_sed }"
                                />
                                <span v-if="form.errors.fecha_resolucion_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_resolucion_sed }}
                                </span>
                            </div>

                            <!-- GACETA -->
                            <div class="space-y-2">
                                <Label for="gaceta_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Gaceta Oficial N°
                                </Label>
                                <Input
                                    id="gaceta_sed"
                                    v-model="form.gaceta_sed"
                                    type="text"
                                    placeholder="Ej. Gaceta N° 41.500"
                                    :class="{ 'border-red-500': form.errors.gaceta_sed }"
                                />
                                <span v-if="form.errors.gaceta_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.gaceta_sed }}
                                </span>
                            </div>

                            <!-- FECHA GACETA -->
                            <div class="space-y-2">
                                <Label for="fecha_gaceta_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha de la Gaceta
                                </Label>
                                <Input
                                    id="fecha_gaceta_sed"
                                    v-model="form.fecha_gaceta_sed"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_gaceta_sed }"
                                />
                                <span v-if="form.errors.fecha_gaceta_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_gaceta_sed }}
                                </span>
                            </div>

                            <!-- FECHA INICIO -->
                            <div class="space-y-2">
                                <Label for="fecha_inicio_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Inicio de Actividades
                                </Label>
                                <Input
                                    id="fecha_inicio_sed"
                                    v-model="form.fecha_inicio_sed"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_inicio_sed }"
                                />
                                <span v-if="form.errors.fecha_inicio_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_inicio_sed }}
                                </span>
                            </div>

                            <!-- FECHA FIN -->
                            <div class="space-y-2">
                                <Label for="fecha_fin_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha de Cierre (si aplica)
                                </Label>
                                <Input
                                    id="fecha_fin_sed"
                                    v-model="form.fecha_fin_sed"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.fecha_fin_sed }"
                                />
                                <span v-if="form.errors.fecha_fin_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.fecha_fin_sed }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECCIÓN 4: CANALES DE CONTACTO -->
                    <Card class="shadow-xs border border-border">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">Canales de Contacto Directo</CardTitle>
                                    <CardDescription>
                                        Teléfono de la administración y correo oficial de la sede.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- TELÉFONO -->
                            <div class="space-y-2">
                                <Label for="telefono_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Teléfono de Contacto
                                </Label>
                                <Input
                                    id="telefono_sed"
                                    v-model="form.telefono_sed"
                                    type="text"
                                    placeholder="Ej. +58 256 1234567"
                                    :class="{ 'border-red-500': form.errors.telefono_sed }"
                                />
                                <span v-if="form.errors.telefono_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.telefono_sed }}
                                </span>
                            </div>

                            <!-- CORREO -->
                            <div class="space-y-2">
                                <Label for="correo_sed" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Correo Electrónico de la Sede
                                </Label>
                                <Input
                                    id="correo_sed"
                                    v-model="form.correo_sed"
                                    type="email"
                                    placeholder="Ej. sede.acarigua@uptp.edu.ve"
                                    :class="{ 'border-red-500': form.errors.correo_sed }"
                                />
                                <span v-if="form.errors.correo_sed" class="text-xs text-red-500 block">
                                    {{ form.errors.correo_sed }}
                                </span>
                            </div>
                        </CardContent>

                        <CardFooter class="flex items-center justify-end gap-3 border-t pt-4">
                            <Button as-child variant="outline">
                                <Link :href="route('organizacion.sede.index')">
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

