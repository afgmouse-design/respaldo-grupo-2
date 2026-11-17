<script setup lang="ts">
/**
 * Vista Standalone: Editar Asignación Sección - Unidad - Docente (`carga_academica` / SUD).
 *
 * Propósito: Permite actualizar el docente, horas semanales, tipo de contratación o unidad
 * de una asignación existente respetando unicidad y disponibilidad horaria en el lapso.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial usando el formulario compartido `SeccionUnidadDocenteForm`.
 */
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import SeccionUnidadDocenteForm from '@/components/Academico/SeccionUnidadDocenteForm.vue';
import type {
    AsignacionInicialSud,
    SeccionSudFormOption,
    UnidadMallaSemestreOption,
} from '@/components/Academico/SeccionUnidadDocenteForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { DocenteSelectOption } from '@/lib/academico';
import type { BreadcrumbItem } from '@/types/navigation';

const props = defineProps<{
    asignacion: AsignacionInicialSud;
    secciones: SeccionSudFormOption[];
    unidadesCurriculares: UnidadMallaSemestreOption[];
    docentes: DocenteSelectOption[];
    tiposContratacion: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Programación Académica', subtitle: 'Operaciones', href: '#' },
    { title: 'Sección - Unidad - Docente', subtitle: 'Listado', href: route('seccion_unidad_docente.index') },
    {
        title: `Editar Asignación #${props.asignacion.id_caa}`,
        subtitle: 'Modificar',
        href: route('seccion_unidad_docente.edit', props.asignacion.id_caa),
    },
];
</script>

<template>
    <Head title="Editar Asignación SUD | Programación Académica" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon"
                            aria-label="Volver al listado"
                            class="h-8 w-8 text-muted-foreground hover:text-foreground"
                        >
                            <Link :href="route('seccion_unidad_docente.index')">
                                <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Asignación Sección - Unidad - Docente
                        </h1>
                    </div>
                    <p class="ml-10 text-sm text-muted-foreground">
                        Modifique el docente asignado, la dedicación semanal o el tipo de contratación de la carga #{{ asignacion.id_caa }}.
                    </p>
                </div>
            </div>

            <div class="mx-auto max-w-4xl">
                <SeccionUnidadDocenteForm
                    :asignacion="asignacion"
                    :secciones="secciones"
                    :unidades-curriculares="unidadesCurriculares"
                    :docentes="docentes"
                    :tipos-contratacion="tiposContratacion"
                    :is-edit="true"
                />
            </div>
        </div>
    </AppLayout>
</template>
