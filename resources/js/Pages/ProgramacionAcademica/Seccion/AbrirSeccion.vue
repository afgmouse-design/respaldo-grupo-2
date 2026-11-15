<script setup lang="ts">
/**
 * Vista Standalone: Abrir Sección
 *
 * Propósito: Gestión operativa para aperturar y activar secciones académicas
 * en el período vigente, habilitándolas para la asignación de horarios y matrícula.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Badge from '@/components/ui/badge/Badge.vue';
import SuccessAlert from '@/components/ui/SuccessAlert.vue';
import { useFlashAlert } from '@/composables/useFlashAlert';
import {
    DoorOpen,
    ArrowLeft,
    Search,
    CheckCircle2,
    Calendar,
    Users,
} from 'lucide-vue-next';

interface SeccionItem {
    id_sec: number;
    nombre_sec: string;
    codigo_sec?: string | null;
    tipo_seccion?: string | null;
    semestre_sec?: number | null;
    trayecto_sec?: number | null;
    cupo_maximo_sec?: number | null;
    status: boolean;
    cohorte_sede?: {
        cohorte?: {
            nombre_coh?: string;
        };
    };
    malla_curricular?: {
        nombre_mac: string;
    };
}

const props = defineProps<{
    secciones: {
        data: SeccionItem[];
        total: number;
    };
    filters: {
        search?: string;
    };
}>();

const { showSuccessAlert, message, closeAlert } = useFlashAlert();
const searchQuery = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('seccion.abrir'), { search: searchQuery.value || undefined }, { preserveState: true });
};

const abrirSeccion = (id: number) => {
    if (confirm('¿Está seguro de aperturar y activar esta sección para el período?')) {
        router.post(route('seccion.cambiar_estado', id), { status: true });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Abrir Sección', href: route('seccion.abrir') },
    ]">
        <Head title="Abrir Secciones - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-6xl mx-auto">
            <SuccessAlert v-if="showSuccessAlert" :message="message" @close="closeAlert" />

            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-600">
                            <DoorOpen class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Apertura Operativa de Secciones
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Habilitación formal de secciones para la asignación docente, espacios y recepción de inscripciones.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('seccion.index')">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Volver a Secciones
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- FILTRO DE BÚSQUEDA -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <Search class="h-4 w-4 absolute left-3 top-3 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Buscar por código o nombre de sección..."
                        class="pl-9"
                        @keyup.enter="buscar"
                    />
                </div>
                <Button variant="secondary" @click="buscar">Filtrar</Button>
            </div>

            <!-- TABLA DE SECCIONES -->
            <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 border-b text-xs font-semibold text-muted-foreground uppercase">
                        <tr>
                            <th class="p-4">Sección</th>
                            <th class="p-4">Malla Curricular</th>
                            <th class="p-4">Nivel</th>
                            <th class="p-4 text-center">Cupo</th>
                            <th class="p-4 text-center">Estado Actual</th>
                            <th class="p-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="sec in props.secciones.data" :key="sec.id_sec" class="hover:bg-muted/30">
                            <td class="p-4 font-medium">
                                <div class="text-slate-900 dark:text-slate-100 font-semibold">{{ sec.nombre_sec }}</div>
                                <div class="text-xs text-muted-foreground font-mono">{{ sec.codigo_sec || 'S/C' }}</div>
                            </td>
                            <td class="p-4 text-muted-foreground text-xs">
                                {{ sec.malla_curricular?.nombre_mac || 'Sin Malla' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                    T{{ sec.trayecto_sec ?? 1 }} / S{{ sec.semestre_sec ?? 1 }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-mono">
                                {{ sec.cupo_maximo_sec || 35 }}
                            </td>
                            <td class="p-4 text-center">
                                <Badge :variant="sec.status ? 'default' : 'secondary'">
                                    {{ sec.status ? 'Abierta / Activa' : 'Cerrada / Inactiva' }}
                                </Badge>
                            </td>
                            <td class="p-4 text-right">
                                <Button
                                    v-if="!sec.status"
                                    size="sm"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white"
                                    @click="abrirSeccion(sec.id_sec)"
                                >
                                    <DoorOpen class="h-4 w-4 mr-1.5" />
                                    Abrir Sección
                                </Button>
                                <span v-else class="text-xs text-emerald-600 font-medium inline-flex items-center">
                                    <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                                    Ya Abierta
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

