<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Trayecto (Módulo: Estructura Académica / Régimen).
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`tla_id`, `tipo_lapso.nombre_tla`, `status_id` vía
 *    `@/lib/status` y `users.username`).
 */
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    GraduationCap,
    ArrowLeft,
    Pencil,
    Calendar,
    Clock,
    User,
    Sliders,
} from 'lucide-vue-next';

interface TipoLapsoItem {
    id_tla: number;
    nombre_tla: string;
    codigo_tla?: string;
}

interface TrayectoDetalle {
    id_tra: number;
    codigo_tra?: string;
    nombre_tra: string;
    numero_orden_tra: number;
    tla_id: number;
    nombre_tla?: string | null;
    tipo_lapso?: TipoLapsoItem | null;
    status_id?: number;
    created_at?: string;
    updated_at?: string;
    creador?: { id: number; username?: string; name?: string; email: string } | null;
    actualizador?: { id: number; username?: string; name?: string; email: string } | null;
}

const props = defineProps<{
    trayecto: TrayectoDetalle;
}>();

const activo = computed(() => esActivo(props.trayecto));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Trayectos', subtitle: 'Niveles Académicos', href: route('academico.trayecto.index') },
    { title: props.trayecto.nombre_tra, subtitle: 'Ficha Técnica', href: route('academico.trayecto.show', props.trayecto.id_tra) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Detalle de Trayecto: ${trayecto.nombre_tra} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.trayecto.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ trayecto.nombre_tra }}
                        </h1>
                        <Badge :variant="activo ? 'default' : 'secondary'" class="ml-2">
                            {{ activo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Identificador interno #{{ trayecto.id_tra }} en la estructura académica curricular.
                    </p>
                </div>
                <div>
                    <Button as-child class="gap-2 shadow-xs">
                        <Link :href="route('academico.trayecto.edit', trayecto.id_tra)">
                            <Pencil class="h-4 w-4" />
                            Editar Trayecto
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHAS DE RESUMEN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- MODALIDAD ACADÉMICA -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Modalidad Académica</CardDescription>
                        <CardTitle class="text-xl font-bold text-primary flex items-center gap-2 mt-1">
                            <Calendar class="h-5 w-5" />
                            {{ trayecto.tipo_lapso?.nombre_tla || trayecto.nombre_tla || 'No asignada' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Tipo de período formativo al que pertenece este trayecto.
                    </CardContent>
                </Card>

                <!-- NÚMERO DE ORDEN -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Orden Cronológico</CardDescription>
                        <CardTitle class="text-3xl font-extrabold text-slate-700 dark:text-slate-300">
                            Nivel #{{ trayecto.numero_orden_tra }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Posición secuencial dentro del plan de estudios.
                    </CardContent>
                </Card>

                <!-- CÓDIGO REFERENCIAL -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Código Referencial</CardDescription>
                        <CardTitle class="text-2xl font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ trayecto.codigo_tra || 'N/A' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Abreviatura técnica para actas y asignaciones.
                    </CardContent>
                </Card>
            </div>

            <!-- AUDITORÍA -->
            <Card class="shadow-2xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Sliders class="h-5 w-5 text-primary" />
                        <CardTitle class="text-base">Datos de Trazabilidad y Auditoría</CardTitle>
                    </div>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Registrado por</p>
                            <p class="font-medium text-foreground">
                                {{ trayecto.creador ? (trayecto.creador.username ?? trayecto.creador.name) : 'Sistema / Inicial' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Fecha de Registro</p>
                            <p class="font-medium text-foreground">
                                {{ trayecto.created_at || 'No registrada' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Última modificación por</p>
                            <p class="font-medium text-foreground">
                                {{ trayecto.actualizador ? (trayecto.actualizador.username ?? trayecto.actualizador.name) : (trayecto.creador ? (trayecto.creador.username ?? trayecto.creador.name) : 'Sin cambios') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Última Actualización</p>
                            <p class="font-medium text-foreground">
                                {{ trayecto.updated_at || 'Sin cambios posteriores' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

