<script setup lang="ts">
/**
 * Vista Standalone: Edición de Programa / PNF (Módulo: Estructura Académica / Oferta Académica).
 *
 * Propósito: Permite modificar un programa existente en `programa` enviando `codigo_prg`,
 * `codigo_ministerio_prg`, `nombre_prg`, `siglas_prg`, `titulo_prg`, `intermedia_prg` y sus
 * claves foráneas de clasificación académica.
 *
 * Historial:
 *  - 2026-11-14: Alineado a `sogac_mio.sql` (`programa`, `id_prg`) y a `PnfController::edit/update`.
 *  - 2026-11-16: Estandarización del pie de formulario con <CrudFormFooter />.
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, GraduationCap } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import CrudFormFooter from '@/components/ui/CrudFormFooter.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/navigation';

interface SelectItem {
    id_tpg?: number;
    nombre_tpg?: string;
    id_ree?: number;
    nombre_ree?: string;
    id_moe?: number;
    nombre_moe?: string;
    id_esm?: number;
    nombre_esm?: string;
}

interface ProgramaItem {
    id_prg: number;
    codigo_prg?: string | null;
    codigo_ministerio_prg?: string | null;
    nombre_prg: string;
    siglas_prg?: string | null;
    titulo_prg?: string | null;
    intermedia_prg?: string | null;
    tpg_id?: number | null;
    ree_id?: number | null;
    moe_id?: number | null;
    esm_id?: number | null;
    status_id?: number;
}

const props = defineProps<{
    programa?: ProgramaItem;
    pnf?: ProgramaItem;
    tiposPrograma?: SelectItem[];
    regimenesEstudio?: SelectItem[];
    modalidadesEstudio?: SelectItem[];
    escalasMedicion?: SelectItem[];
}>();

const programaActual = computed<ProgramaItem>(() => (props.programa ?? props.pnf) as ProgramaItem);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', subtitle: 'Panel Principal', href: route('dashboard') },
    { title: 'Estructura Académica', subtitle: 'Currículo', href: '#' },
    { title: 'PNF / Programas', subtitle: 'Oferta Académica', href: route('academico.pnf.index') },
    { title: `Editar: ${programaActual.value.nombre_prg}`, subtitle: 'Modificación', href: route('academico.pnf.edit', programaActual.value.id_prg) },
];

const form = useForm({
    codigo_prg: programaActual.value.codigo_prg || '',
    codigo_ministerio_prg: programaActual.value.codigo_ministerio_prg || '',
    nombre_prg: programaActual.value.nombre_prg,
    siglas_prg: programaActual.value.siglas_prg || '',
    titulo_prg: programaActual.value.titulo_prg || '',
    intermedia_prg: programaActual.value.intermedia_prg || 'N',
    tpg_id: (programaActual.value.tpg_id ?? '') as string | number,
    ree_id: (programaActual.value.ree_id ?? '') as string | number,
    moe_id: (programaActual.value.moe_id ?? '') as string | number,
    esm_id: (programaActual.value.esm_id ?? '') as string | number,
});

const submit = () => {
    form.put(route('academico.pnf.update', programaActual.value.id_prg), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar Programa: ${programaActual.nombre_prg} - SOGAC`" />

        <div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- ENCABEZADO -->
            <div class="flex items-center justify-between border-b pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-foreground">
                            <Link :href="route('academico.pnf.index')">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Editar Programa / PNF
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground ml-10">
                        Actualice los datos del programa #{{ programaActual.id_prg }}: {{ programaActual.nombre_prg }}.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <Card class="shadow-xs border border-border">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        <CardTitle class="text-lg">Modificar Programa Académico</CardTitle>
                    </div>
                    <CardDescription>
                        Ajuste el código, la denominación oficial y los catálogos asociados del programa.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div v-if="form.errors.error" class="p-3 text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span>{{ form.errors.error }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="codigo_prg" class="text-sm font-medium">Código Interno</Label>
                                <Input
                                    id="codigo_prg"
                                    v-model="form.codigo_prg"
                                    type="text"
                                    placeholder="Ej: PNF-INF"
                                    :class="{ 'border-destructive': form.errors.codigo_prg }"
                                />
                                <p v-if="form.errors.codigo_prg" class="text-xs text-destructive">{{ form.errors.codigo_prg }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="codigo_ministerio_prg" class="text-sm font-medium">Código Ministerial</Label>
                                <Input
                                    id="codigo_ministerio_prg"
                                    v-model="form.codigo_ministerio_prg"
                                    type="text"
                                    maxlength="10"
                                    placeholder="Ej: 1024"
                                    :class="{ 'border-destructive': form.errors.codigo_ministerio_prg }"
                                />
                                <p v-if="form.errors.codigo_ministerio_prg" class="text-xs text-destructive">{{ form.errors.codigo_ministerio_prg }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="siglas_prg" class="text-sm font-medium">Siglas</Label>
                                <Input
                                    id="siglas_prg"
                                    v-model="form.siglas_prg"
                                    type="text"
                                    maxlength="10"
                                    placeholder="Ej: INF"
                                    :class="{ 'border-destructive': form.errors.siglas_prg }"
                                />
                                <p v-if="form.errors.siglas_prg" class="text-xs text-destructive">{{ form.errors.siglas_prg }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="nombre_prg" class="text-sm font-medium">
                                    Nombre del Programa / Carrera <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="nombre_prg"
                                    v-model="form.nombre_prg"
                                    type="text"
                                    placeholder="Ej: PNF en Informática"
                                    :class="{ 'border-destructive': form.errors.nombre_prg }"
                                    required
                                />
                                <p v-if="form.errors.nombre_prg" class="text-xs text-destructive">{{ form.errors.nombre_prg }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="titulo_prg" class="text-sm font-medium">Título que Otorga</Label>
                                <Input
                                    id="titulo_prg"
                                    v-model="form.titulo_prg"
                                    type="text"
                                    placeholder="Ej: Ingeniero en Informática"
                                    :class="{ 'border-destructive': form.errors.titulo_prg }"
                                />
                                <p v-if="form.errors.titulo_prg" class="text-xs text-destructive">{{ form.errors.titulo_prg }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="space-y-2">
                                <Label for="tpg_id" class="text-sm font-medium">Tipo de Programa</Label>
                                <select
                                    id="tpg_id"
                                    v-model="form.tpg_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                                >
                                    <option value="">Sin especificar</option>
                                    <option v-for="t in tiposPrograma" :key="t.id_tpg" :value="t.id_tpg">
                                        {{ t.nombre_tpg }}
                                    </option>
                                </select>
                                <p v-if="form.errors.tpg_id" class="text-xs text-destructive">{{ form.errors.tpg_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="ree_id" class="text-sm font-medium">Régimen de Estudio</Label>
                                <select
                                    id="ree_id"
                                    v-model="form.ree_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                                >
                                    <option value="">Sin especificar</option>
                                    <option v-for="r in regimenesEstudio" :key="r.id_ree" :value="r.id_ree">
                                        {{ r.nombre_ree }}
                                    </option>
                                </select>
                                <p v-if="form.errors.ree_id" class="text-xs text-destructive">{{ form.errors.ree_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="moe_id" class="text-sm font-medium">Modalidad</Label>
                                <select
                                    id="moe_id"
                                    v-model="form.moe_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                                >
                                    <option value="">Sin especificar</option>
                                    <option v-for="m in modalidadesEstudio" :key="m.id_moe" :value="m.id_moe">
                                        {{ m.nombre_moe }}
                                    </option>
                                </select>
                                <p v-if="form.errors.moe_id" class="text-xs text-destructive">{{ form.errors.moe_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="esm_id" class="text-sm font-medium">Escala de Medición</Label>
                                <select
                                    id="esm_id"
                                    v-model="form.esm_id"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                                >
                                    <option value="">Sin especificar</option>
                                    <option v-for="e in escalasMedicion" :key="e.id_esm" :value="e.id_esm">
                                        {{ e.nombre_esm }}
                                    </option>
                                </select>
                                <p v-if="form.errors.esm_id" class="text-xs text-destructive">{{ form.errors.esm_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="intermedia_prg" class="text-sm font-medium">Salida Intermedia</Label>
                                <select
                                    id="intermedia_prg"
                                    v-model="form.intermedia_prg"
                                    class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs"
                                >
                                    <option value="S">Sí</option>
                                    <option value="N">No</option>
                                </select>
                                <p v-if="form.errors.intermedia_prg" class="text-xs text-destructive">{{ form.errors.intermedia_prg }}</p>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="border-t border-slate-100 bg-muted/20 px-6 py-4 dark:border-slate-800/60">
                        <CrudFormFooter
                            :cancel-href="route('academico.pnf.index')"
                            :is-edit="true"
                            :processing="form.processing"
                        />
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
