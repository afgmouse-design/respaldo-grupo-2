<script setup lang="ts">
/**
 * Vista Standalone: Listado De Secciones (Informes)
 *
 * Propósito: Informe consolidado de todas las secciones registradas con su
 * respectiva cohorte, sede, malla y cantidad de asignaturas docentes.
 */
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Badge from '@/components/ui/badge/Badge.vue';
import {
    Layers,
    Printer,
    Search,
    School,
} from 'lucide-vue-next';

const props = defineProps<{
    secciones: {
        data: any[];
        total: number;
    };
    filters: {
        search?: string;
    };
}>();

const searchQuery = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('informes.secciones'), { search: searchQuery.value || undefined }, { preserveState: true });
};

const imprimir = () => {
    window.print();
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Programación Académica', href: route('seccion.index') },
        { title: 'Informes', href: route('informes.secciones') },
        { title: 'Listado De Secciones', href: route('informes.secciones') },
    ]">
        <Head title="Listado De Secciones - SOGAC" />

        <div class="space-y-6 p-4 md:p-8 max-w-7xl mx-auto">
            <!-- CABECERA -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-4 print:hidden">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <Layers class="h-6 w-6" />
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            Informe General de Secciones
                        </h1>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Catálogo consolidado de oferta académica, capacidad y distribución por cohorte y sede.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative w-64">
                        <Search class="h-4 w-4 absolute left-3 top-3 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Buscar sección..."
                            class="pl-9 h-9"
                            @keyup.enter="buscar"
                        />
                    </div>
                    <Button variant="outline" @click="imprimir">
                        <Printer class="h-4 w-4 mr-2" />
                        Imprimir
                    </Button>
                </div>
            </div>

            <!-- TABLA -->
            <div class="bg-card border rounded-xl overflow-hidden shadow-xs">
                <table class="w-full text-sm text-left">
                    <thead class="bg-muted/50 border-b text-xs font-semibold text-muted-foreground uppercase">
                        <tr>
                            <th class="p-4">Código / Nombre</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Malla Curricular</th>
                            <th class="p-4">Cohorte / Sede</th>
                            <th class="p-4 text-center">Nivel</th>
                            <th class="p-4 text-center">Turno</th>
                            <th class="p-4 text-center">Capacidad</th>
                            <th class="p-4 text-center">Materias</th>
                            <th class="p-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="sec in props.secciones.data" :key="sec.id_sec" class="hover:bg-muted/30">
                            <td class="p-4 font-medium">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ sec.nombre_sec }}</div>
                                <div class="text-xs text-muted-foreground font-mono">{{ sec.codigo_sec || 'S/C' }}</div>
                            </td>
                            <td class="p-4">
                                <Badge variant="outline" class="text-xs">
                                    {{ sec.tipo_seccion || 'Regular' }}
                                </Badge>
                            </td>
                            <td class="p-4 text-xs text-muted-foreground">
                                {{ sec.malla_curricular?.nombre_mac || 'Sin Malla' }}
                            </td>
                            <td class="p-4 text-xs text-muted-foreground">
                                {{ sec.cohorte_sede?.cohorte?.nombre_coh || 'Sin Cohorte' }}
                            </td>
                            <td class="p-4 text-center font-medium">
                                T{{ sec.trayecto_sec ?? 1 }} / S{{ sec.semestre_sec ?? 1 }}
                            </td>
                            <td class="p-4 text-center text-xs">
                                {{ sec.turno_sec || 'Matutino' }}
                            </td>
                            <td class="p-4 text-center font-mono font-bold">
                                {{ sec.cupo_maximo_sec || 35 }}
                            </td>
                            <td class="p-4 text-center font-bold text-primary">
                                {{ sec.unidades_docentes?.length || 0 }}
                            </td>
                            <td class="p-4 text-center">
                                <Badge :variant="sec.status ? 'default' : 'secondary'">
                                    {{ sec.status ? 'Activa' : 'Inactiva' }}
                                </Badge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

