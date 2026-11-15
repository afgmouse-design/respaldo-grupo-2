<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select, SelectContent, SelectGroup, SelectItem, SelectLabel,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';

/**
 * Props que el componente recibe desde el padre.
 * - total: número total de elementos (ej. productos)
 * - perPage: cantidad de elementos por página
 * - currentPage: página actual (valor inicial)
 * - lastPage: última página disponible
 */
const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    lastPage: number;
    from: number;
    to: number;
    registros_per_page: number;
}>();

/**
 * Eventos que este componente puede emitir hacia el padre.
 * - pageChange: se dispara cuando el usuario cambia de página,
 *   enviando el nuevo número de página.
 */
const emit = defineEmits<{
    (e: 'pageChange', page?: number): void;
    // el ? te permitirá hacer tanto emit('cantidadRegistros', '10') como emit('cantidadRegistros')
  (e: 'cantidadRegistros', cantidad?: string | number | null): void
}>();

/**
 * Variable reactiva interna que almacena la página actual.
 * Se inicializa con el valor de la prop currentPage.
 */
const internalPage = ref(props.currentPage);

/**
 * Observa cambios en la prop currentPage (por ejemplo, si el padre
 * la modifica externamente, como al navegar con el botón atrás).
 * Cuando eso ocurre, actualizamos internalPage para mantener la sincronía.
 */
watch(() => props.currentPage, (newPage) => {
    internalPage.value = newPage;
});

/**
 * Observa cambios en internalPage (cuando el usuario hace clic en la paginación).
 * Si el nuevo valor es diferente al de la prop actual (para evitar bucles),
 * emitimos el evento pageChange para que el padre pueda reaccionar,
 * por ejemplo, haciendo una nueva petición al servidor.
 */
watch(internalPage, (newPage) => {
    if (newPage !== props.currentPage) {
        emit('pageChange', newPage);
    }
});
</script>

<template>
    <div class="flex items-center justify-between md:flex-row flex-col gap-4 mx-5 mt-4">
        <div>
            <span class="text-sm text-muted-foreground">
                Mostrando {{ props.from }} a {{ props.to }} de
                {{ props.total }} resultados </span>
        </div>

        <div class="flex items-center gap-4 sm:flex-row flex-col justify-center">
            <div class="flex items-center gap-2">
                <Label class="text-sm text-muted-foreground">Mostrar</Label>
                <Select @update:modelValue="$emit('cantidadRegistros',  String($event ?? '5'))">
                    <SelectTrigger class="w-20">
                        <SelectValue placeholder="5" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Cantidad</SelectLabel>
                            <SelectItem value="5">5</SelectItem>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="15">15</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                            <SelectItem value="25">25</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
            <!--
    Componente Pagination de shadcn-vue.
    - :total y :items-per-page: necesarios para calcular cuántas páginas hay.
    - v-model:page="internalPage": enlace bidireccional con nuestra variable interna.
      Esto significa que cuando el usuario hace clic en un número, internalPage se actualiza solo.
  -->
            <Pagination :total="total" :items-per-page="perPage" v-model:page="internalPage">
                <!--
      PaginationContent renderiza los botones de página.
      El slot nos da 'items', un array de objetos que representan cada elemento de la paginación
      (números de página o puntos suspensivos).
    -->
                <PaginationContent v-slot="{ items }">
                    <!-- Botón "Anterior": se deshabilita si estamos en la primera página -->
                    <PaginationPrevious :disabled="internalPage === 1" />

                    <!-- Iteramos sobre cada elemento de 'items' -->
                    <template v-for="(item, index) in items" :key="index">
                        <!--
          Si el elemento es de tipo 'page', mostramos un botón con el número de página.
          - :value: el número de página.
          - :is-active: resalta el botón si coincide con la página actual.
        -->
                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                            :is-active="item.value === internalPage">
                            {{ item.value }}
                        </PaginationItem>
                        <!--
          Si no es de tipo 'page', es un separador (elipsis).
          Mostramos PaginationEllipsis.
        -->
                        <PaginationEllipsis v-else :index="index" />
                    </template>

                    <!-- Botón "Siguiente": se deshabilita si estamos en la última página -->
                    <PaginationNext :disabled="internalPage === lastPage" />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
    <!-- 
  Explicación de conceptos clave:
Props: Son datos que el componente padre le pasa al hijo. Aquí, el padre le dice al componente de paginación cuántos elementos hay, cuántos por página, y cuál es la página actual.

Eventos (emit): Son la forma en que el hijo se comunica con el padre. Cuando el usuario cambia de página, el componente emite pageChange con el nuevo número, y el padre puede ejecutar una función (como onPageChange) para actualizar los datos.

ref y watch:

internalPage es una variable reactiva local que mantiene el estado de la página actual.

El primer watch escucha cambios en la prop currentPage (que puede venir del padre) y actualiza internalPage para que estén sincronizados.

El segundo watch escucha cambios en internalPage (cuando el usuario hace clic) y emite el evento si es necesario.

v-model: Es una directiva que crea un enlace bidireccional. Aquí v-model:page="internalPage" sincroniza la página interna con el componente Pagination: si el usuario hace clic, internalPage cambia; si cambiamos internalPage programáticamente, el componente se actualiza. -->
</template>