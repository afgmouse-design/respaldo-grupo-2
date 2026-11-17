<script setup lang="ts">
/**
 * Componente: Selector con Autocompletado en Tiempo Real para Docentes (`DocenteCombobox`).
 *
 * Propósito: Permite buscar docentes activos al instante por cédula o nombre completo,
 * mostrando su dedicación laboral y carga horaria actual/disponible dentro del lapso académico
 * de la sección seleccionada, con soporte accesible de teclado (ARIA combobox) y composición IME segura.
 *
 * Historial:
 *  - 2026-11-16: Creación inicial del componente reutilizable de búsqueda de docentes (SUD).
 */
import { Check, ChevronDown, Search, UserCheck, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    filtrarDocentesCatalogo,
    obtenerCedulaDocente,
    obtenerHorasAsignadasDocenteLapso,
    obtenerIdDocente,
    obtenerNombreDocente,
} from '@/lib/academico';
import type { DocenteSelectOption } from '@/lib/academico';

let docenteComboboxSeq = 0;

const props = withDefaults(
    defineProps<{
        modelValue: number | string | null;
        docentes: DocenteSelectOption[];
        inputId?: string;
        describedbyId?: string;
        invalid?: boolean;
        laaId?: number | string | null;
        horasSolicitadas?: number;
        excluirDocentePepId?: number | null;
        excluirHoras?: number;
        placeholder?: string;
        disabled?: boolean;
    }>(),
    {
        inputId: undefined,
        describedbyId: undefined,
        invalid: false,
        laaId: null,
        horasSolicitadas: 0,
        excluirDocentePepId: null,
        excluirHoras: 0,
        placeholder: 'Buscar docente por cédula (ej. V14...) o nombre...',
        disabled: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string): void;
    (e: 'selected', docente: DocenteSelectOption | null): void;
}>();

docenteComboboxSeq += 1;
const resolvedInputId = props.inputId || `docente-combobox-${docenteComboboxSeq}`;
const listboxId = `${resolvedInputId}-listbox`;

const rootRef = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const searchTerm = ref('');
const activeIndex = ref(0);

const selectedDocente = computed(() => {
    const id = Number(props.modelValue || 0);
    if (!id) return null;
    return props.docentes.find((d) => obtenerIdDocente(d) === id) ?? null;
});

watch(
    selectedDocente,
    (doc) => {
        if (!isOpen.value) {
            searchTerm.value = doc
                ? `${obtenerCedulaDocente(doc)} · ${obtenerNombreDocente(doc, '')}`.replace(/^ · /, '')
                : '';
        }
    },
    { immediate: true }
);

const filteredDocentes = computed(() => filtrarDocentesCatalogo(props.docentes, searchTerm.value));

const activeOptionId = computed(() => {
    if (!isOpen.value || filteredDocentes.value.length === 0) return undefined;
    const item = filteredDocentes.value[activeIndex.value];
    return item ? `${listboxId}-opt-${obtenerIdDocente(item)}` : undefined;
});

const getCargaDocenteInfo = (doc: DocenteSelectOption) => {
    const pepId = obtenerIdDocente(doc);
    const descontar = props.excluirDocentePepId && Number(props.excluirDocentePepId) === pepId
        ? Number(props.excluirHoras || 0)
        : 0;
    const asignadas = obtenerHorasAsignadasDocenteLapso(doc, props.laaId, descontar);
    const maximas = Number(doc.horas_maximas_ded ?? 0);
    const proyectadas = asignadas + Math.max(0, Number(props.horasSolicitadas || 0));
    const excede = maximas > 0 ? proyectadas > maximas : false;

    return {
        asignadas,
        maximas,
        disponibles: Math.max(0, maximas - asignadas),
        proyectadas,
        excede,
    };
};

const openDropdown = () => {
    if (props.disabled) return;
    isOpen.value = true;
    activeIndex.value = 0;
    if (selectedDocente.value) {
        searchTerm.value = '';
    }
};

const closeDropdown = (restoreSelectedLabel = true) => {
    isOpen.value = false;
    if (restoreSelectedLabel) {
        const doc = selectedDocente.value;
        searchTerm.value = doc
            ? `${obtenerCedulaDocente(doc)} · ${obtenerNombreDocente(doc, '')}`.replace(/^ · /, '')
            : '';
    }
};

const selectDocente = (doc: DocenteSelectOption) => {
    const id = obtenerIdDocente(doc);
    emit('update:modelValue', id);
    emit('selected', doc);
    searchTerm.value = `${obtenerCedulaDocente(doc)} · ${obtenerNombreDocente(doc, '')}`.replace(/^ · /, '');
    isOpen.value = false;
};

const clearSelection = () => {
    if (props.disabled) return;
    emit('update:modelValue', '');
    emit('selected', null);
    searchTerm.value = '';
    isOpen.value = true;
    activeIndex.value = 0;
    nextTick(() => inputRef.value?.focus());
};

const scrollActiveOptionIntoView = () => {
    nextTick(() => {
        const id = activeOptionId.value;
        if (!id) return;
        const el = document.getElementById(id);
        el?.scrollIntoView({ block: 'nearest' });
    });
};

const handleInput = (e: Event) => {
    searchTerm.value = (e.target as HTMLInputElement).value;
    if (!isOpen.value) {
        isOpen.value = true;
    }
    activeIndex.value = 0;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (props.disabled) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!isOpen.value) {
            openDropdown();
            return;
        }
        if (filteredDocentes.value.length > 0) {
            activeIndex.value = (activeIndex.value + 1) % filteredDocentes.value.length;
            scrollActiveOptionIntoView();
        }
        return;
    }

    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!isOpen.value) {
            openDropdown();
            return;
        }
        if (filteredDocentes.value.length > 0) {
            activeIndex.value =
                (activeIndex.value - 1 + filteredDocentes.value.length) % filteredDocentes.value.length;
            scrollActiveOptionIntoView();
        }
        return;
    }

    if (e.key === 'Enter') {
        if (e.isComposing || e.keyCode === 229) {
            return;
        }
        if (isOpen.value && filteredDocentes.value[activeIndex.value]) {
            e.preventDefault();
            selectDocente(filteredDocentes.value[activeIndex.value]);
        }
        return;
    }

    if (e.key === 'Escape') {
        if (isOpen.value) {
            e.preventDefault();
            closeDropdown(true);
        }
    }
};

const handleDocumentPointerDown = (e: PointerEvent) => {
    if (!rootRef.value) return;
    if (!rootRef.value.contains(e.target as Node)) {
        closeDropdown(true);
    }
};

onMounted(() => {
    document.addEventListener('pointerdown', handleDocumentPointerDown);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handleDocumentPointerDown);
});
</script>

<template>
    <div ref="rootRef" class="relative w-full">
        <div class="relative flex items-center">
            <Search
                aria-hidden="true"
                class="pointer-events-none absolute left-3 h-4 w-4 text-muted-foreground"
            />

            <input
                :id="resolvedInputId"
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
                :aria-describedby="describedbyId"
                :aria-invalid="invalid ? 'true' : 'false'"
                :value="searchTerm"
                :placeholder="placeholder"
                :disabled="disabled"
                class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-16 text-sm text-foreground ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-red-500 focus-visible:ring-red-500': invalid }"
                @focus="openDropdown"
                @input="handleInput"
                @keydown="handleKeydown"
            />

            <div class="absolute right-1.5 flex items-center gap-0.5">
                <button
                    v-if="(selectedDocente || searchTerm) && !disabled"
                    type="button"
                    aria-label="Limpiar docente seleccionado"
                    class="inline-flex min-h-6 min-w-6 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    @click.stop="clearSelection"
                >
                    <X class="h-3.5 w-3.5" aria-hidden="true" />
                </button>
                <button
                    type="button"
                    aria-label="Desplegar lista de docentes"
                    :disabled="disabled"
                    class="inline-flex min-h-6 min-w-6 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none"
                    @click.stop="isOpen ? closeDropdown(true) : (openDropdown(), inputRef?.focus())"
                >
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-150"
                        :class="{ 'rotate-180': isOpen }"
                        aria-hidden="true"
                    />
                </button>
            </div>
        </div>

        <div
            v-if="isOpen"
            :id="listboxId"
            role="listbox"
            class="absolute left-0 right-0 z-50 mt-1 max-h-64 overflow-y-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-lg"
        >
            <div v-if="filteredDocentes.length === 0" class="px-3 py-4 text-center text-xs text-muted-foreground">
                No se encontraron docentes activos que coincidan con "{{ searchTerm }}".
            </div>

            <button
                v-for="(doc, idx) in filteredDocentes"
                :id="`${listboxId}-opt-${obtenerIdDocente(doc)}`"
                :key="obtenerIdDocente(doc)"
                type="button"
                role="option"
                :aria-selected="obtenerIdDocente(doc) === Number(modelValue || 0) ? 'true' : 'false'"
                class="flex w-full items-start justify-between gap-2 rounded-sm px-2.5 py-2 text-left text-sm transition-colors"
                :class="[
                    idx === activeIndex ? 'bg-accent text-accent-foreground' : 'hover:bg-accent/60',
                    obtenerIdDocente(doc) === Number(modelValue || 0) ? 'font-medium' : '',
                ]"
                @mouseenter="activeIndex = idx"
                @mousedown.prevent="selectDocente(doc)"
            >
                <div class="min-w-0 flex-1 space-y-0.5">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                            {{ obtenerCedulaDocente(doc) || 'S/C' }}
                        </span>
                        <span class="truncate text-slate-900 dark:text-slate-100">
                            {{ obtenerNombreDocente(doc) }}
                        </span>
                        <Check
                            v-if="obtenerIdDocente(doc) === Number(modelValue || 0)"
                            class="h-3.5 w-3.5 shrink-0 text-primary"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                        <span class="inline-flex items-center gap-1 font-medium text-primary/90">
                            <UserCheck class="h-3 w-3" aria-hidden="true" />
                            {{ doc.dedicacion_nombre || 'Sin dedicación' }}
                        </span>
                        <span aria-hidden="true">·</span>
                        <span
                            :class="getCargaDocenteInfo(doc).excede ? 'font-semibold text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400'"
                        >
                            Carga en lapso: {{ getCargaDocenteInfo(doc).asignadas }}h / {{ getCargaDocenteInfo(doc).maximas }}h máx.
                            <template v-if="getCargaDocenteInfo(doc).excede">
                                (Excede límite)
                            </template>
                            <template v-else>
                                ({{ getCargaDocenteInfo(doc).disponibles }}h disp.)
                            </template>
                        </span>
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>
