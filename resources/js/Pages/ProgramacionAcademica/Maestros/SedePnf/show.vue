<script setup lang="ts">
/**
 * Vista Standalone: Detalle de Oferta Sede PNF (Programación Académica).
 *
 * Propósito: Ficha técnica de consulta y detalle de la oferta académica por sede.
 */
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types/navigation';
import { ArrowLeft, Pencil, Building2, CheckCircle2, XCircle, BookOpen } from 'lucide-vue-next';

const props = defineProps<{
    sedePnf: {
        id_sep: number;
        status?: boolean;
        sede?: { id_sed: number; nombre_sed: string; codigo_sed?: string };
        pnf?: { id_pnf: number; nombre_pnf: string; codigo_pnf?: string };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Maestros', href: '#' },
    { title: 'Oferta Sede PNF', subtitle: 'Listado', href: route('sede_pnf.index') },
    { title: 'Detalle de Oferta', subtitle: 'Consulta', href: route('sede_pnf.show', props.sedePnf.id_sep) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Detalle de Oferta Sede PNF - Programación Académica" />

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
                            Ficha de Oferta Sede PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Información y vinculación territorial del programa académico.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" class="gap-2">
                        <Link :href="route('sede_pnf.edit', sedePnf.id_sep)">
                            <Pencil class="h-4 w-4" />
                            <span>Editar</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <Card class="border-slate-200 dark:border-slate-800 shadow-xs max-w-4xl mx-auto">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg font-semibold">Oferta #{{ sedePnf.id_sep }}</CardTitle>
                        </div>
                        <Badge :variant="sedePnf.status ? 'default' : 'secondary'" class="gap-1.5 py-1 px-3">
                            <CheckCircle2 v-if="sedePnf.status" class="h-3.5 w-3.5" />
                            <XCircle v-else class="h-3.5 w-3.5" />
                            {{ sedePnf.status ? 'Habilitada' : 'Inactiva' }}
                        </Badge>
                    </div>
                    <CardDescription>Vinculación de carrera por sede universitaria</CardDescription>
                </CardHeader>

                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Sede Universitaria</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <Building2 class="h-4 w-4 text-primary" />
                                {{ sedePnf.sede?.nombre_sed || 'No asignada' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Programa Nacional de Formación</span>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-1 flex items-center gap-1.5">
                                <BookOpen class="h-4 w-4 text-primary" />
                                {{ sedePnf.pnf?.nombre_pnf || 'No asignado' }}
                            </p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-end border-t border-slate-100 dark:border-slate-800/60 pt-4">
                    <Button as-child variant="outline">
                        <Link :href="route('sede_pnf.index')">Volver al Listado</Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>

