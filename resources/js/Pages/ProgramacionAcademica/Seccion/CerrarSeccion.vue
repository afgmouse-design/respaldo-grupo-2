<script setup lang="ts">
/**
 * Vista Standalone: Cerrar Sección
 *
 * Propósito: Gestión para la consolidación y cierre administrativo de secciones
 * al culminar períodos lectivos o por reubicación de matrícula.
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
    DoorClosed,
    ArrowLeft,
    Search,
    AlertTriangle,
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
    router.get(route('seccion.cerrar'), { search: searchQuery.value || undefined }, { preserveState: true });
};

const cerrarSeccion = (id: number, nombre: string) => {
    if (confirm(`¿Confirma que desea CERRAR la sección '${nombre}'? Los estudiantes no podrán inscribirse.`)) {
        router.post(route('seccion.cambiar_estado', id), { status: false });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Cerrar Sección', href: route('seccion.cerrar') },
    ]">
        <Head title="Cerrar Secciones - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-6xl mx-auto">
            <SuccessAlert v-if="showSuccessAlert" :message="message" @close="closeAlert" />

            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-rose-500/10 rounded-lg text-rose-600">
                            <DoorClosed class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Cierre Administrativo de Secciones
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Inactivación y cierre de secciones abiertas para finalizar períodos académicos o consolidar matrícula.
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
                        placeholder="Buscar sección abierta..."
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
                            <th class="p-4">Sección Abierta</th>
                            <th class="p-4">Malla Curricular</th>
                            <th class="p-4">Nivel</th>
                            <th class="p-4 text-center">Capacidad</th>
                            <th class="p-4 text-right">Operación</th>
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
                            <td class="p-4 text-right">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="text-rose-600 border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                    @click="cerrarSeccion(sec.id_sec, sec.nombre_sec)"
                                >
                                    <DoorClosed class="h-4 w-4 mr-1.5" />
                                    Cerrar Sección
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="props.secciones.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-muted-foreground text-sm">
                                No se encontraron secciones abiertas para cerrar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

