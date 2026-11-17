<script setup lang="ts">
/**
 * Vista Standalone: Cambiar Capacidad Por Sección
 *
 * Propósito: Ajuste y calibración rápida del cupo máximo estudiantil permitido
 * en cada sección para regular la carga de las aulas y laboratorios.
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
import { esActivo } from '@/lib/status';
import {
    Users,
    ArrowLeft,
    Search,
    Save,
    Check,
} from 'lucide-vue-next';

interface SeccionItem {
    id_sec: number;
    nombre_sec: string;
    codigo_sec?: string | null;
    semestre_sec?: number | null;
    trayecto_sec?: number | null;
    cupo_maximo_sec?: number | null;
    status_id?: number;
    status?: boolean;
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

// Estado de edición reactivo por fila
const capacidades = ref<Record<number, number>>({});
props.secciones.data.forEach(sec => {
    capacidades.value[sec.id_sec] = sec.cupo_maximo_sec || 35;
});

const buscar = () => {
    router.get(route('seccion.capacidad'), { search: searchQuery.value || undefined }, { preserveState: true });
};

const guardarCapacidad = (id: number) => {
    const nuevoCupo = capacidades.value[id];
    if (!nuevoCupo || nuevoCupo < 1) {
        alert('Ingrese una capacidad válida mayor a cero.');
        return;
    }
    router.patch(route('seccion.actualizar_capacidad', id), {
        cupo_maximo_sec: nuevoCupo,
    }, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Secciones', href: route('seccion.index') },
        { title: 'Cambiar Capacidad Por Sección', href: route('seccion.capacidad') },
    ]">
        <Head title="Capacidad de Secciones - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-6xl mx-auto">
            <SuccessAlert v-if="showSuccessAlert" :message="message" @close="closeAlert" />

            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-500/10 rounded-lg text-blue-600">
                            <Users class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Regulación de Capacidad por Sección
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Ajuste de aforo máximo estudiantil admisible para controlar la sobrepoblación en aulas y laboratorios.
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
                        placeholder="Buscar por código o sección..."
                        class="pl-9"
                        @keyup.enter="buscar"
                    />
                </div>
                <Button variant="secondary" @click="buscar">Filtrar</Button>
            </div>

            <!-- TABLA DE EDICIÓN RÁPIDA -->
            <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 border-b text-xs font-semibold text-muted-foreground uppercase">
                        <tr>
                            <th class="p-4">Sección</th>
                            <th class="p-4">Malla Curricular</th>
                            <th class="p-4">Nivel</th>
                            <th class="p-4 text-center">Estado</th>
                            <th class="p-4 text-center w-48">Cupo Máximo (Aforo)</th>
                            <th class="p-4 text-right">Guardar</th>
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
                            <td class="p-4 text-center">
                                <Badge :variant="esActivo(sec) ? 'default' : 'secondary'">
                                    {{ esActivo(sec) ? 'Activa' : 'Inactiva' }}
                                </Badge>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <Input
                                        v-model="capacidades[sec.id_sec]"
                                        type="number"
                                        min="1"
                                        max="200"
                                        class="w-24 text-center font-bold text-sm h-9"
                                    />
                                    <span class="text-xs text-muted-foreground">estudiantes</span>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-9 hover:bg-primary/10 hover:text-primary"
                                    @click="guardarCapacidad(sec.id_sec)"
                                >
                                    <Save class="h-4 w-4 mr-1.5" />
                                    Actualizar
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

