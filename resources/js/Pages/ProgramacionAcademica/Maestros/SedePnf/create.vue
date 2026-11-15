<script setup lang="ts">
/**
 * Vista Standalone: Registrar Oferta Sede PNF (Programación Académica).
 *
 * Propósito: Vincular un programa nacional de formación (PNF) a una sede universitaria.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Building2 } from 'lucide-vue-next';

defineProps<{
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    pnfs: Array<{ id_pnf: number; nombre_pnf: string; codigo_pnf?: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Oferta Sede PNF', subtitle: 'Listado', href: route('sede_pnf.index') },
    { title: 'Registrar Oferta', subtitle: 'Nuevo Registro', href: route('sede_pnf.create') },
];

const form = useForm({
    sed_id: '',
    pnf_id: '',
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
                                <Label for="pnf_id">
                                    Programa (PNF) <span class="text-destructive">*</span>
                                </Label>
                                <select
                                    id="pnf_id"
                                    v-model="form.pnf_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-hidden focus:ring-2 focus:ring-ring"
                                    :class="{ 'border-destructive': form.errors.pnf_id }"
                                >
                                    <option value="" disabled>Seleccione un PNF...</option>
                                    <option v-for="p in pnfs" :key="p.id_pnf" :value="p.id_pnf">
                                        {{ p.codigo_pnf ? `[${p.codigo_pnf}] ` : '' }}{{ p.nombre_pnf }}
                                    </option>
                                </select>
                                <span v-if="form.errors.pnf_id" class="text-xs text-destructive">
                                    {{ form.errors.pnf_id }}
                                </span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <Button as-child variant="outline" :disabled="form.processing">
                            <Link :href="route('sede_pnf.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="gap-2">
                            <Save class="h-4 w-4" />
                            <span>{{ form.processing ? 'Guardando...' : 'Habilitar Oferta' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

