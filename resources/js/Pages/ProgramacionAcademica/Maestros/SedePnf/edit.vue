<script setup lang="ts">
/**
 * Vista Standalone: Editar Oferta Sede PNF (Programación Académica).
 *
 * Propósito: Modificar la asignación de sede o PNF para una oferta académica.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Save, Building2 } from 'lucide-vue-next';

const props = defineProps<{
    sedePnf: {
        id_sep: number;
        sed_id: number;
        pnf_id: number;
        status?: boolean;
    };
    sedes: Array<{ id_sed: number; nombre_sed: string }>;
    pnfs: Array<{ id_pnf: number; nombre_pnf: string; codigo_pnf?: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Oferta Sede PNF', subtitle: 'Listado', href: route('sede_pnf.index') },
    { title: 'Editar Oferta', subtitle: 'Actualización', href: route('sede_pnf.edit', props.sedePnf.id_sep) },
];

const form = useForm({
    sed_id: props.sedePnf.sed_id,
    pnf_id: props.sedePnf.pnf_id,
});

const submit = () => {
    form.put(route('sede_pnf.update', props.sedePnf.id_sep), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Oferta Sede PNF - Programación Académica" />

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
                            Editar Oferta Sede PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Modifique los parámetros de la oferta académica por sede.
                    </p>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg font-semibold">Oferta Académica #{{ sedePnf.id_sep }}</CardTitle>
                    </div>
                    <CardDescription>
                        Actualice la sede o el programa según corresponda.
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
                            <span>{{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

