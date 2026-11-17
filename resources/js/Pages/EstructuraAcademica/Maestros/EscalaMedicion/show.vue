<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Escala de Medición (Estructura Académica / Currículo).
 *
 * Propósito: Visualizar información detallada y auditoría de una escala de calificación académica.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { esActivo } from '@/lib/status';
import type { BreadcrumbItem } from '@/types/navigation';
import {
    Calculator,
    ArrowLeft,
    Pencil,
    CheckCircle2,
    Clock,
    User,
    Sliders,
    Layers,
} from 'lucide-vue-next';

interface EscalaDetalle {
    id_esm: number;
    nombre_esm: string;
    nota_aprobatoria_esm: number;
    nota_minima_esm: number | null;
    nota_maxima_esm: number | null;
    status_id?: number;
    status?: boolean;
    est_esm?: boolean;
    created_at?: string;
    updated_at?: string;
    creador?: { id: number; username?: string; name?: string; email: string } | null;
    actualizador?: { id: number; username?: string; name?: string; email: string } | null;
}

const props = defineProps<{
    escala: EscalaDetalle;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'Escalas de Medición', subtitle: 'Modelos de Evaluación', href: route('estructura_academica.escalas_medicion.index') },
    { title: `Ficha: ${props.escala.nombre_esm}`, subtitle: 'Detalle', href: route('estructura_academica.escalas_medicion.show', props.escala.id_esm) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Detalle de Escala: ${escala.nombre_esm} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('estructura_academica.escalas_medicion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ escala.nombre_esm }}
                        </h1>
                        <Badge :variant="esActivo(escala) ? 'default' : 'secondary'" class="ml-2">
                            {{ esActivo(escala) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Identificador interno #{{ escala.id_esm }} en el catálogo de modelos de evaluación.
                    </p>
                </div>
                <div>
                    <Button as-child class="gap-2 shadow-xs">
                        <Link :href="route('estructura_academica.escalas_medicion.edit', escala.id_esm)">
                            <Pencil class="h-4 w-4" />
                            Editar Escala
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FICHA TÉCNICA -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- NOTA APROBATORIA -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Nota Aprobatoria</CardDescription>
                        <CardTitle class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                            {{ Number(escala.nota_aprobatoria_esm).toFixed(2) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Puntaje mínimo requerido para dar por aprobada la unidad curricular.
                    </CardContent>
                </Card>

                <!-- NOTA MÍNIMA -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Nota Mínima</CardDescription>
                        <CardTitle class="text-3xl font-extrabold text-slate-700 dark:text-slate-300">
                            {{ escala.nota_minima_esm !== null ? Number(escala.nota_minima_esm).toFixed(2) : '0.00' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Límite inferior absoluto permitido en la escala.
                    </CardContent>
                </Card>

                <!-- NOTA MÁXIMA -->
                <Card class="shadow-2xs border border-border">
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs uppercase font-semibold">Nota Máxima</CardDescription>
                        <CardTitle class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">
                            {{ escala.nota_maxima_esm !== null ? Number(escala.nota_maxima_esm).toFixed(2) : '20.00' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-muted-foreground">
                        Límite superior absoluto o calificación máxima posible.
                    </CardContent>
                </Card>
            </div>

            <!-- INFORMACIÓN DE AUDITORÍA -->
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
                                {{ escala.creador ? (escala.creador.username ?? escala.creador.name) : 'Sistema / Inicial' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Fecha de Registro</p>
                            <p class="font-medium text-foreground">
                                {{ escala.created_at || 'No registrada' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Última modificación por</p>
                            <p class="font-medium text-foreground">
                                {{
                                    escala.actualizador
                                        ? (escala.actualizador.username ?? escala.actualizador.name)
                                        : (escala.creador ? (escala.creador.username ?? escala.creador.name) : 'Sin cambios')
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-muted/20 rounded-lg">
                        <Clock class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <p class="text-xs text-muted-foreground">Última Actualización</p>
                            <p class="font-medium text-foreground">
                                {{ escala.updated_at || 'Sin cambios posteriores' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

