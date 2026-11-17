<script setup lang="ts">
/**
 * Vista Standalone: Edición de País (Módulo 1 - Estructura Geográfica).
 *
 * Propósito: Página independiente con formulario precargado para actualizar
 * la información de un país existente en el SOGAC. Cumple estrictamente la regla de CERO MODALES.
 *
 * Historial:
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Eye, Globe } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { esActivo, resolverStatusId } from '@/lib/status';
import { formatNombre } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types/navigation';

interface PaisModel {
    id_pai: number;
    nombre_pai: string;
    status_id?: number;
    status?: boolean | number;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    pais: PaisModel;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Geográfica', subtitle: 'Módulo Territorial', href: route('geografia.pais.index') },
    { title: 'Países', subtitle: 'Catálogo Maestro', href: route('geografia.pais.index') },
    { title: props.pais.nombre_pai, subtitle: 'Modificación', href: route('geografia.pais.edit', props.pais.id_pai) },
];

const form = useForm({
    nombre_pai: props.pais.nombre_pai,
    status_id: resolverStatusId(props.pais),
});

const submit = () => {
    form.put(route('geografia.pais.update', props.pais.id_pai), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.pais.nombre_pai} - Estructura Geográfica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO Y RETORNO -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-slate-900">
                            <Link :href="route('geografia.pais.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar País: {{ props.pais.nombre_pai }}
                        </h1>
                        <Badge :variant="esActivo(props.pais) ? 'successCustom' : 'destructiveCustom'" class="ml-2">
                            {{ esActivo(props.pais) ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique la denominación oficial o el estado operativo del registro.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('geografia.pais.show', props.pais.id_pai)">
                            <Eye class="h-4 w-4 mr-2" />
                            Ver Ficha
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FORMULARIO STANDALONE -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-amber-500/10 rounded-lg text-amber-600 dark:text-amber-400">
                            <Globe class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Actualización de Datos</CardTitle>
                            <CardDescription>
                                Identificador único del sistema: #{{ props.pais.id_pai }}
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-5 pt-2">
                        <!-- MENSAJE DE ERROR GLOBAL -->
                        <div
                            v-if="form.errors.error"
                            class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2 border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <!-- CAMPO: NOMBRE DEL PAÍS -->
                        <div class="space-y-2">
                            <Label for="nombre_pai" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Nombre Oficial del País <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="nombre_pai"
                                v-model="form.nombre_pai"
                                type="text"
                                maxlength="255"
                                required
                                :class="{ 'border-destructive focus-visible:ring-destructive/20': form.errors.nombre_pai }"
                                autocomplete="off"
                                @input="form.nombre_pai = formatNombre(($event.target as HTMLInputElement).value)"
                            />
                            <p v-if="form.errors.nombre_pai" class="text-xs text-destructive font-medium">
                                {{ form.errors.nombre_pai }}
                            </p>
                        </div>

                        <!-- CAMPO: ESTATUS (`status_id`) -->
                        <div class="space-y-2 pt-2 border-t dark:border-slate-800">
                            <Label for="status" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Estatus Operativo
                            </Label>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input
                                        id="status"
                                        type="checkbox"
                                        :checked="form.status_id === 1"
                                        @change="form.status_id = ($event.target as HTMLInputElement).checked ? 1 : 2"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 dark:bg-slate-700"></div>
                                    <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ form.status_id === 1 ? 'Registro Activo' : 'Registro Inactivo' }}
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Desactivar un país inhabilita su selección en formularios de inscripción y personas.
                            </p>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('geografia.pais.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

