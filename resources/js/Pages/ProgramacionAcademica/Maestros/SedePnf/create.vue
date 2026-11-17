<script setup lang="ts">
/**
 * Vista Standalone: Registrar Oferta Sede PNF (Programación Académica).
 *
 * Propósito: Vincular un programa académico (`programa`) a una sede universitaria en `sede_programa`.
 * Historial:
 *  - 2026-11-14: Alineado a `prg_id -> programa.id_prg`, `codigo_ministerio_spr` y `cantidad_asignados_spr`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Building2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    obtenerCodigoPrograma,
    obtenerIdPrograma,
    obtenerNombrePrograma,
} from '@/lib/academico';
import type { ProgramaCatalogoItem } from '@/lib/academico';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    programas?: ProgramaCatalogoItem[];
    pnfs?: ProgramaCatalogoItem[];
}>();

const catalogoProgramas = computed(() => props.programas ?? props.pnfs ?? []);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Oferta Sede - Programa', subtitle: 'Listado', href: route('sede_pnf.index') },
    { title: 'Registrar Oferta', subtitle: 'Nuevo Registro', href: route('sede_pnf.create') },
];

const form = useForm({
    sed_id: '',
    prg_id: '',
    codigo_ministerio_spr: '',
    cantidad_asignados_spr: '' as string | number,
});

const submit = () => {
    form.post(route('sede_pnf.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Oferta Sede PNF - Programación Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('sede_pnf.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Oferta Sede PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Habilite un programa académico para ser impartido en una sede territorial.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos de la Oferta Académica</CardTitle>
                    </div>
                    <CardDescription>
                        Seleccione la sede universitaria y el programa correspondiente.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="sed_id">
                                    Sede Universitaria <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="sed_id"
                                    v-model="form.sed_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.sed_id }"
                                >
                                    <option value="" disabled>Seleccione una sede...</option>
                                    <option v-for="s in sedes" :key="s.id_sed" :value="s.id_sed">
                                        {{ s.nombre_sed }}
                                    </option>
                                </select>
                                <span v-if="form.errors.sed_id" class="text-xs text-destructive">
                                    {{ form.errors.sed_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="prg_id">
                                    Programa Académico <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="prg_id"
                                    v-model="form.prg_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.prg_id || form.errors.pnf_id }"
                                >
                                    <option value="" disabled>Seleccione un programa...</option>
                                    <option v-for="p in catalogoProgramas" :key="obtenerIdPrograma(p)" :value="obtenerIdPrograma(p)">
                                        {{ obtenerCodigoPrograma(p) ? `[${obtenerCodigoPrograma(p)}] ` : '' }}{{ obtenerNombrePrograma(p) }}
                                    </option>
                                </select>
                                <span v-if="form.errors.prg_id || form.errors.pnf_id" class="text-xs text-destructive">
                                    {{ form.errors.prg_id || form.errors.pnf_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="codigo_ministerio_spr">
                                    Código Ministerio (Opcional)
                                </Label>
                                <Input
                                    id="codigo_ministerio_spr"
                                    v-model="form.codigo_ministerio_spr"
                                    type="text"
                                    maxlength="5"
                                    placeholder="Ej. 00123"
                                    :class="{ 'border-destructive': form.errors.codigo_ministerio_spr }"
                                />
                                <span v-if="form.errors.codigo_ministerio_spr" class="text-xs text-destructive">
                                    {{ form.errors.codigo_ministerio_spr }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="cantidad_asignados_spr">
                                    Cantidad Asignados OPSU (Opcional)
                                </Label>
                                <Input
                                    id="cantidad_asignados_spr"
                                    v-model="form.cantidad_asignados_spr"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    :class="{ 'border-destructive': form.errors.cantidad_asignados_spr }"
                                />
                                <span v-if="form.errors.cantidad_asignados_spr" class="text-xs text-destructive">
                                    {{ form.errors.cantidad_asignados_spr }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('sede_pnf.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

