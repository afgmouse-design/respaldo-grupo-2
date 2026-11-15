<script setup lang="ts">
/**
 * Vista Standalone: Editar Sección (Programación Académica).
 *
 * Propósito: Modificación de los parámetros de una sección existente mediante navegación directa SPA.
 */
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Users } from 'lucide-vue-next';

interface SeccionItem {
    id_sec: number;
    codigo_sec?: string | null;
    nombre_sec: string;
    tipo_seccion?: string;
    trayecto_sec?: number;
    semestre_sec?: number;
    nivel_sec?: number;
    turno_sec: string;
    cupo_maximo_sec: number;
    laa_id: number;
    pnf_id: number;
    sed_id: number;
    cos_id?: number | null;
    mac_id?: number | null;
    status?: boolean;
}

const props = defineProps<{
    seccion: SeccionItem;
    lapsos: Array<{ id_la: number; nombre_la: string }>;
    pnfs: Array<{ id_pnf: number; nombre_pnf: string; codigo_pnf?: string }>;
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    cohortes?: Array<{ id_cos: number; nombre_cos: string; sep_id: number }>;
    mallas?: Array<{ id_mac: number; nombre_mac: string; pnf_id: number }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Secciones', subtitle: 'Listado', href: route('seccion.index') },
    { title: `Editar: ${props.seccion.nombre_sec}`, subtitle: 'Actualización', href: route('seccion.edit', props.seccion.id_sec) },
];

const form = useForm({
    codigo_sec: props.seccion.codigo_sec || props.seccion.nombre_sec,
    nombre_sec: props.seccion.nombre_sec || '',
    tipo_seccion: props.seccion.tipo_seccion || 'Regular',
    trayecto_sec: props.seccion.trayecto_sec ?? 1,
    semestre_sec: props.seccion.semestre_sec ?? 1,
    nivel_sec: props.seccion.nivel_sec ?? 1,
    turno_sec: props.seccion.turno_sec || 'Mañana',
    cupo_maximo_sec: props.seccion.cupo_maximo_sec || 35,
    laa_id: props.seccion.laa_id,
    pnf_id: props.seccion.pnf_id,
    sed_id: props.seccion.sed_id,
    cos_id: props.seccion.cos_id || '',
    mac_id: props.seccion.mac_id || '',
});

const mallasDisponibles = computed(() => {
    if (!form.pnf_id || !props.mallas) return props.mallas || [];
    return props.mallas.filter((m) => Number(m.pnf_id) === Number(form.pnf_id));
});

const submit = () => {
    form.put(route('seccion.update', props.seccion.id_sec), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Sección: ${seccion.nombre_sec} - Programación Académica`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('seccion.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Sección Académica
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique la configuración y asignación de la sección #{{ seccion.id_sec }}.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Parámetros de la Sección</CardTitle>
                    </div>
                    <CardDescription>
                        Ajuste denominación, turno, cohorte o malla curricular asociada.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="codigo_sec">Código de la Sección</Label>
                                <Input
                                    id="codigo_sec"
                                    v-model="form.codigo_sec"
                                    placeholder="Ej. INFO-T1-S1-A"
                                    :class="{ 'border-red-500': form.errors.codigo_sec }"
                                />
                                <span v-if="form.errors.codigo_sec" class="text-xs text-red-500">
                                    {{ form.errors.codigo_sec }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="nombre_sec">Nombre Descriptivo *</Label>
                                <Input
                                    id="nombre_sec"
                                    v-model="form.nombre_sec"
                                    :class="{ 'border-red-500': form.errors.nombre_sec }"
                                />
                                <span v-if="form.errors.nombre_sec" class="text-xs text-red-500">
                                    {{ form.errors.nombre_sec }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="tipo_seccion">Tipo de Sección *</Label>
                                <select
                                    id="tipo_seccion"
                                    v-model="form.tipo_seccion"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                    :class="{ 'border-red-500': form.errors.tipo_seccion }"
                                >
                                    <option value="Regular">Regular</option>
                                    <option value="PER">Plan Especial de Referencia (PER)</option>
                                    <option value="Repitiente">Repitiente</option>
                                    <option value="Dirigido">Dirigido</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="turno_sec">Turno de Clases *</Label>
                                <select
                                    id="turno_sec"
                                    v-model="form.turno_sec"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                    :class="{ 'border-red-500': form.errors.turno_sec }"
                                >
                                    <option value="Mañana">Mañana (Matutino)</option>
                                    <option value="Tarde">Tarde (Vespertino)</option>
                                    <option value="Noche">Noche (Nocturno)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4 dark:border-slate-800">
                            <div class="space-y-2">
                                <Label for="laa_id">Lapso Académico *</Label>
                                <select
                                    id="laa_id"
                                    v-model="form.laa_id"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option v-for="lapso in lapsos" :key="lapso.id_la" :value="lapso.id_la">
                                        {{ lapso.nombre_la }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="trayecto_sec">Trayecto *</Label>
                                <select
                                    id="trayecto_sec"
                                    v-model="form.trayecto_sec"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option :value="0">Trayecto Inicial</option>
                                    <option :value="1">Trayecto I</option>
                                    <option :value="2">Trayecto II (Salida TSU)</option>
                                    <option :value="3">Trayecto III (Prosecución)</option>
                                    <option :value="4">Trayecto IV (Grado)</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="semestre_sec">Semestre *</Label>
                                <select
                                    id="semestre_sec"
                                    v-model="form.semestre_sec"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option v-for="n in 8" :key="n" :value="n">
                                        Semestre {{ n }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4 dark:border-slate-800">
                            <div class="space-y-2">
                                <Label for="pnf_id">Programa Nacional de Formación (PNF) *</Label>
                                <select
                                    id="pnf_id"
                                    v-model="form.pnf_id"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option v-for="pnf in pnfs" :key="pnf.id_pnf" :value="pnf.id_pnf">
                                        {{ pnf.nombre_pnf }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="sed_id">Sede Física *</Label>
                                <select
                                    id="sed_id"
                                    v-model="form.sed_id"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option v-for="sede in sedes" :key="sede.id_sed" :value="sede.id_sed">
                                        {{ sede.nombre_sed }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="cos_id">Cohorte por Sede</Label>
                                <select
                                    id="cos_id"
                                    v-model="form.cos_id"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option value="">Sin cohorte específica (Genérica)</option>
                                    <option v-for="coh in cohortes || []" :key="coh.id_cos" :value="coh.id_cos">
                                        {{ coh.nombre_cos }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="mac_id">Malla Curricular Asociada</Label>
                                <select
                                    id="mac_id"
                                    v-model="form.mac_id"
                                    class="w-full h-10 px-3 py-2 rounded-md border border-input bg-background text-sm"
                                >
                                    <option value="">Seleccione malla...</option>
                                    <option v-for="malla in mallasDisponibles" :key="malla.id_mac" :value="malla.id_mac">
                                        {{ malla.nombre_mac }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label for="cupo_maximo_sec">Capacidad de Cupo Máximo *</Label>
                                <Input
                                    id="cupo_maximo_sec"
                                    v-model="form.cupo_maximo_sec"
                                    type="number"
                                    min="1"
                                    max="200"
                                />
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t pt-4 dark:border-slate-800">
                        <Button as-child variant="outline">
                            <Link :href="route('seccion.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Actualizar Sección' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
