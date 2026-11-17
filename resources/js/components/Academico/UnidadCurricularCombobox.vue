<script setup lang="ts">
/**
 * Componente: Buscador / Autocompletado de Unidad Curricular (UnidadCurricularCombobox.vue).
 *
 * Propósito: Reemplaza la dupla separada `<input>` + `<select>` por un único campo interactivo
 * accesible (`role="combobox"`) con lupa, limpieza rápida (`×`), filtrado instantáneo insensible
 * a mayúsculas/acentos prioritario por código, navegación por teclado (`↑`/`↓`/`Enter`/`Esc`)
 * segura para IME y lista flotante limitada a los primeros resultados para soportar cientos de materias.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial para el constructor de Malla Curricular (`create.vue` y `edit.vue`).
 */
import { Search, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { filtrarUnidadesCatalogo } from '@/lib/academico';
import type { UnidadCatalogoItem } from '@/lib/academico';

const props = withDefaults(
    defineProps<{
        inputId: string;
        modelValue: number | string;
        query: string;
        catalogo: UnidadCatalogoItem[];
        excludedIds: Set<number>;
        semestreGlobal?: number;
        codigoPrefix?: string | null;
        limit?: number;
        placeholder?: string;
    }>(),
    {
        semestreGlobal: 1,
        codigoPrefix: null,
        limit: 30,
        placeholder: 'Buscar por código (ej. 1785, INF-1101) o nombre...',
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string): void;
    (e: 'update:query', value: string): void;
    (e: 'select', item: UnidadCatalogoItem | null): void;
}>();

const rootRef = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const activeIndex = ref(0);
const searchText = ref('');

const listboxId = computed(() => `${props.inputId}-listbox`);
const optionId = (idx: number) => `${props.inputId}-option-${idx}`;

const formatearEtiqueta = (u: UnidadCatalogoItem): string => `${u.codigo_unc} — ${u.nombre_unc}`;

const selectedItem = computed<UnidadCatalogoItem | null>(
    () => props.catalogo.find((u) => Number(u.id_unc) === Number(props.modelValue)) ?? null
);

watch(
    () => props.modelValue,
    (newVal) => {
        if (!newVal) {
            if (!props.query) {
                searchText.value = '';
            }
            return;
        }
        const match = props.catalogo.find((u) => Number(u.id_unc) === Number(newVal));
        if (match) {
            searchText.value = formatearEtiqueta(match);
        }
    },
    { immediate: true }
);

const effectiveQuery = computed(() => {
    if (selectedItem.value && searchText.value === formatearEtiqueta(selectedItem.value)) {
        return '';
    }
    return searchText.value;
});

const busqueda = computed(() =>
    filtrarUnidadesCatalogo(
        props.catalogo,
        props.excludedIds,
        effectiveQuery.value,
        props.semestreGlobal,
        props.codigoPrefix,
        props.limit
    )
);

const activeOptionId = computed(() =>
    isOpen.value && busqueda.value.items[activeIndex.value] ? optionId(activeIndex.value) : undefined
);

const scrollActiveOptionIntoView = () => {
    nextTick(() => {
        const el = document.getElementById(optionId(activeIndex.value));
        el?.scrollIntoView({ block: 'nearest' });
    });
};

const seleccionarUnidad = (item: UnidadCatalogoItem) => {
    searchText.value = formatearEtiqueta(item);
    isOpen.value = false;
    activeIndex.value = 0;
    emit('update:modelValue', item.id_unc);
    emit('update:query', '');
    emit('select', item);
};

const limpiarSeleccion = () => {
    searchText.value = '';
    activeIndex.value = 0;
    isOpen.value = true;
    emit('update:modelValue', '');
    emit('update:query', '');
    emit('select', null);
    nextTick(() => {
        inputRef.value?.focus();
    });
};

const onFocus = () => {
    activeIndex.value = 0;
    isOpen.value = true;
};

const onInput = (event: Event) => {
    const val = (event.target as HTMLInputElement).value;
    searchText.value = val;

    if (selectedItem.value && val !== formatearEtiqueta(selectedItem.value)) {
        emit('update:modelValue', '');
        emit('select', null);
    }

    emit('update:query', val);
    activeIndex.value = 0;
    isOpen.value = true;
};

const cerrarLista = () => {
    isOpen.value = false;
    if (selectedItem.value) {
        searchText.value = formatearEtiqueta(selectedItem.value);
    }
};

const onKeydown = (event: KeyboardEvent) => {
    const total = busqueda.value.items.length;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (!isOpen.value) {
            isOpen.value = true;
            activeIndex.value = 0;
        } else if (total > 0) {
            activeIndex.value = (activeIndex.value + 1) % total;
        }
        scrollActiveOptionIntoView();
        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (!isOpen.value) {
            isOpen.value = true;
            activeIndex.value = Math.max(0, total - 1);
        } else if (total > 0) {
            activeIndex.value = (activeIndex.value - 1 + total) % total;
        }
        scrollActiveOptionIntoView();
        return;
    }

    if (event.key === 'Enter') {
        event.preventDefault();
        // Evitar confirmar selección mientras el usuario compone caracteres con IME
        if (event.isComposing || event.keyCode === 229) {
            return;
        }
        if (isOpen.value && busqueda.value.items[activeIndex.value]) {
            seleccionarUnidad(busqueda.value.items[activeIndex.value]);
        }
        return;
    }

    if (event.key === 'Escape') {
        if (isOpen.value) {
            event.preventDefault();
            cerrarLista();
        }
        return;
    }

    if (event.key === 'Tab') {
        cerrarLista();
    }
};

const handlePointerDownOutside = (event: PointerEvent) => {
    if (!isOpen.value) return;
    if (rootRef.value && !rootRef.value.contains(event.target as Node)) {
        cerrarLista();
    }
};

onMounted(() => {
    document.addEventListener('pointerdown', handlePointerDownOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handlePointerDownOutside);
});
</script>

<template>
    <div ref="rootRef" class="relative w-full min-w-0">
        <div class="relative flex items-center w-full min-w-0">
            <Search
                class="h-3.5 w-3.5 text-muted-foreground absolute left-2.5 pointer-events-none shrink-0"
                aria-hidden="true"
            />

            <input
                :id="inputId"
                ref="inputRef"
                type="text"
                role="combobox"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="none"
                spellcheck="false"
                aria-autocomplete="list"
                :aria-expanded="isOpen ? 'true' : 'false'"
                :aria-controls="listboxId"
                :aria-activedescendant="activeOptionId"
                :value="searchText"
                :placeholder="placeholder"
                class="flex h-8 w-full max-w-full min-w-0 rounded-md border border-input bg-background pl-8 pr-8 py-1 text-xs shadow-2xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                @focus="onFocus"
                @input="onInput"
                @keydown="onKeydown"
            />

            <button
                v-if="searchText || modelValue"
                type="button"
                aria-label="Limpiar selección de unidad curricular"
                title="Limpiar selección"
                class="absolute right-1 inline-flex h-6 w-6 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                @mousedown.prevent
                @click="limpiarSeleccion"
            >
                <X class="h-3.5 w-3.5" aria-hidden="true" />
            </button>
        </div>

        <!-- Lista desplegable de coincidencias en tiempo real -->
        <div
            v-if="isOpen"
            class="absolute left-0 right-0 top-full mt-1 rounded-md border border-border bg-popover text-popover-foreground max-h-56 overflow-y-auto shadow-lg z-50"
        >
            <ul
                v-if="busqueda.items.length > 0"
                :id="listboxId"
                role="listbox"
                class="py-1 divide-y divide-border/40"
            >
                <li
                    v-for="(item, idx) in busqueda.items"
                    :id="optionId(idx)"
                    :key="item.id_unc"
                    role="option"
                    :aria-selected="Number(modelValue) === Number(item.id_unc)"
                >
                    <button
                        type="button"
                        tabindex="-1"
                        :class="[
                            'w-full px-3 py-2 text-left text-xs flex items-center justify-between gap-2 transition-colors',
                            idx === activeIndex
                                ? 'bg-accent text-accent-foreground'
                                : 'hover:bg-accent/60 hover:text-accent-foreground',
                        ]"
                        @mousedown.prevent
                        @mouseenter="activeIndex = idx"
                        @click="seleccionarUnidad(item)"
                    >
                        <span class="min-w-0 flex-1 leading-snug">
                            <span class="font-mono font-bold text-primary">{{ item.codigo_unc }}</span>
                            <span class="text-muted-foreground mx-1">—</span>
                            <span class="font-medium text-foreground">{{ item.nombre_unc }}</span>
                        </span>

                        <Badge
                            variant="secondary"
                            class="text-[10px] font-semibold px-1.5 py-0 shrink-0 whitespace-nowrap"
                        >
                            {{ item.uc }} UC
                        </Badge>
                    </button>
                </li>
            </ul>

            <div
                v-else
                :id="listboxId"
                role="listbox"
                class="px-3 py-4 text-center text-xs text-muted-foreground"
            >
                {{
                    busqueda.totalDisponibles === 0
                        ? 'Todas las unidades curriculares del catálogo ya están asignadas en esta malla.'
                        : 'No se encontraron unidades curriculares con ese código o nombre'
                }}
            </div>

            <div
                v-if="busqueda.totalCoincidencias > busqueda.items.length"
                class="border-t bg-muted/30 px-3 py-1.5 text-[11px] text-muted-foreground"
            >
                Mostrando {{ busqueda.items.length }} de {{ busqueda.totalCoincidencias }} coincidencias. Escribe para afinar la búsqueda.
            </div>
        </div>
    </div>
</template>
