<script setup lang="ts">
/**
 * Componente UI Reutilizable: Interruptor de Estado Integrado (StatusSwitch.vue)
 *
 * Propósito: Renderizar un suiche interactivo controlado con el texto de estado ("Activo" / "Inactivo")
 * integrado dentro del mismo interruptor (verde cuando está activado y gris cuando está desactivado).
 *
 * Historial de Modificaciones:
 * - [Actualización]: Se integró el indicador circular del suiche directamente dentro de la píldora
 *   de estado ("Activo" / "Inactivo"), aplicando fondo verde esmeralda para registros activos
 *   y fondo gris neutro para registros inactivos.
 */
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        status: boolean | number | string | null | undefined;
        disabled?: boolean;
        activeLabel?: string;
        inactiveLabel?: string;
        showBadge?: boolean;
        title?: string;
    }>(),
    {
        disabled: false,
        activeLabel: 'Activo',
        inactiveLabel: 'Inactivo',
        showBadge: true,
        title: undefined,
    }
);

const emit = defineEmits<{
    (e: 'toggle'): void;
}>();

const isActive = computed(() => {
    const val = props.status;
    return val === true || val === 1 || val === '1' || val === 'true' || val === 'A';
});

const tooltipText = computed(() => {
    if (props.title) return props.title;
    return isActive.value ? 'Clic para desactivar este registro' : 'Clic para activar este registro';
});

const handleClick = () => {
    if (props.disabled) return;
    emit('toggle');
};
</script>

<template>
    <div class="inline-flex items-center justify-center">
        <button
            type="button"
            role="switch"
            :aria-checked="isActive"
            :disabled="disabled"
            :title="tooltipText"
            @click.stop.prevent="handleClick"
            class="relative inline-flex h-7 w-24 shrink-0 cursor-pointer items-center rounded-full p-1 text-xs font-semibold shadow-xs transition-all duration-200 select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50"
            :class="
                isActive
                    ? 'bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500'
                    : 'bg-slate-300 text-slate-700 hover:bg-slate-400/80 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600'
            "
        >
            <!-- Texto interno posicionado al lado opuesto de la perilla -->
            <span
                class="w-full text-center leading-none transition-all duration-200"
                :class="isActive ? 'pr-5 pl-1.5' : 'pl-5 pr-1.5'"
            >
                {{ isActive ? activeLabel : inactiveLabel }}
            </span>

            <!-- Perilla circular deslizable dentro de la píldora -->
            <span
                class="pointer-events-none absolute top-1 h-5 w-5 rounded-full bg-white shadow-md ring-0 transition-all duration-200"
                :class="isActive ? 'left-[calc(100%-1.5rem)]' : 'left-1'"
            />
        </button>
    </div>
</template>
