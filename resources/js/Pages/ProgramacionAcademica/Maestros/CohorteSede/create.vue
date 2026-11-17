<script setup lang="ts">
/**
 * Vista Standalone: Registrar Cohorte por Sede (Programación Académica).
 *
 * Propósito: Registro de oferta de cohorte en una sede/programa mediante navegación directa SPA.
 *
 * Actualización 2026-11-14: Alineado a `sede_programa(id_spr)` preservando compatibilidad con `id_sep`.
 * - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, MapPin } from 'lucide-vue-next';
import { watch } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    cohortes: Array<{ id_coh: number; nombre_coh: string }>;
    ofertasSedePnf: Array<{ id_spr?: number; id_sep?: number; descripcion_oferta: string }>;
}>();

const resolverOfertaId = (o: { id_spr?: number; id_sep?: number }) => o.id_spr ?? o.id_sep ?? 0;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Cohortes por Sede', subtitle: 'Listado', href: route('cohorte_sede.index') },
    { title: 'Registrar Cohorte por Sede', subtitle: 'Nuevo Registro', href: route('cohorte_sede.create') },
];

const form = useForm({
    nombre_cos: '',
    coh_id: '',
    sep_id: '',
});

// Auto-sugerencia del nombre descriptivo si el usuario no ha ingresado uno manualmente
watch([() => form.coh_id, () => form.sep_id], ([cohId, sepId]) => {
    if (!form.nombre_cos && cohId && sepId) {
        const coh = props.cohortes.find((c) => c.id_coh === Number(cohId));
        const sep = props.ofertasSedePnf.find((s) => resolverOfertaId(s) === Number(sepId));
        if (coh && sep) {
            form.nombre_cos = `Cohorte ${coh.nombre_coh} - ${sep.descripcion_oferta}`;
        }
    }
});

const submit = () => {
    form.post(route('cohorte_sede.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Cohorte por Sede - Programación Académica" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('cohorte_sede.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Registrar Cohorte por Sede
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Asigne una cohorte generacional a la oferta académica de una sede específica.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Datos de la Cohorte por Sede</CardTitle>
                    </div>
                    <CardDescription>
                        Seleccione la cohorte matriz y el programa sede correspondiente.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="coh_id">
                                    Cohorte Matriz <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="coh_id"
                                    v-model="form.coh_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.coh_id }"
                                >
                                    <option value="" disabled>Seleccione la cohorte...</option>
                                    <option v-for="c in cohortes" :key="c.id_coh" :value="c.id_coh">
                                        {{ c.nombre_coh }}
                                    </option>
                                </select>
                                <p v-if="cohortes.length === 0" class="text-xs text-amber-600 dark:text-amber-400">
                                    No hay cohortes registradas. Primero debe crear una cohorte en el menú <strong>Cohortes</strong>.
                                </p>
                                <span v-if="form.errors.coh_id" class="text-xs text-destructive">
                                    {{ form.errors.coh_id }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <Label for="sep_id">
                                    Oferta Sede - PNF <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="sep_id"
                                    v-model="form.sep_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.sep_id }"
                                >
                                    <option value="" disabled>Seleccione sede y programa...</option>
                                    <option v-for="o in ofertasSedePnf" :key="resolverOfertaId(o)" :value="resolverOfertaId(o)">
                                        {{ o.descripcion_oferta }}
                                    </option>
                                </select>
                                <p v-if="ofertasSedePnf.length === 0" class="text-xs text-amber-600 dark:text-amber-400">
                                    No hay ofertas Sede-PNF registradas. Primero debe asociar una sede con un programa en el menú <strong>Sede PNF</strong>.
                                </p>
                                <span v-if="form.errors.sep_id" class="text-xs text-destructive">
                                    {{ form.errors.sep_id }}
                                </span>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="nombre_cos">
                                    Nombre Descriptivo de la Asignación <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_cos"
                                    v-model="form.nombre_cos"
                                    type="text"
                                    placeholder="Ej. Cohorte 2026-I Informática Acarigua"
                                    :class="{ 'border-destructive': form.errors.nombre_cos }"
                                />
                                <span v-if="form.errors.nombre_cos" class="text-xs text-destructive">
                                    {{ form.errors.nombre_cos }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('cohorte_sede.index')"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

