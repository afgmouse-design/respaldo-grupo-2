<script setup lang="ts">
/**
 * Componente UI Reutilizable: Pie Estandarizado de Formularios CRUD (CrudFormFooter.vue).
 *
 * Propósito: Estandarizar los botones del footer en formularios y modales de creación/edición
 * manteniendo orden estricto ("Cancelar" sin ícono a la izquierda, "Guardar" / "Guardar Cambios"
 * con ícono <Save /> y spinner <Loader2 /> a la derecha), jerarquía visual coherente y clases
 * bloqueadas internamente para evitar divergencias entre maestros.
 *
 * Historial:
 * - 2026-11-16: Creación inicial reutilizando <Button>, Inertia <Link> e íconos Lucide.
 * - 2026-11-16: Endurecimiento visual removiendo props de personalización de ícono/etiquetas
 *   (estricto <Save />, "Cancelar", "Guardar" / "Guardar Cambios") y bloqueando `inheritAttrs`.
 */
import { Link } from '@inertiajs/vue3';
import { Loader2, Save } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        /** Ruta destino al cancelar mediante Inertia Link (opcional; si se omite emite 'cancel'). */
        cancelHref?: string | null;
        /** Define si el formulario corresponde a edición ("Guardar Cambios") o creación ("Guardar"). */
        isEdit?: boolean;
        /** Estado de envío en curso: deshabilita botones y muestra spinner. */
        processing?: boolean;
        /** Deshabilitación adicional sobre el botón de guardado. */
        disabled?: boolean;
        /** Tipo HTML del botón primario. */
        submitType?: 'submit' | 'button';
        /** Agrega borde superior y espaciado cuando no está dentro de un CardFooter con border-t. */
        bordered?: boolean;
    }>(),
    {
        cancelHref: null,
        isEdit: false,
        processing: false,
        disabled: false,
        submitType: 'submit',
        bordered: false,
    }
);

const emit = defineEmits<{
    (e: 'cancel'): void;
    (e: 'submit'): void;
}>();

const resolvedSaveLabel = computed(() => {
    if (props.processing) {
        return props.isEdit ? 'Guardando cambios...' : 'Guardando...';
    }
    return props.isEdit ? 'Guardar Cambios' : 'Guardar';
});

const handleSubmitClick = () => {
    if (props.submitType === 'button') {
        emit('submit');
    }
};
</script>

<template>
    <div
        :class="
            cn(
                'flex w-full items-center justify-end gap-3',
                bordered && 'border-t border-slate-100 pt-4 dark:border-slate-800/60'
            )
        "
    >
        <!-- Cancelar (izquierda, secundario neutro sin ícono) -->
        <Button
            v-if="cancelHref"
            as-child
            variant="secondary"
            :class="
                cn(
                    'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700',
                    processing && 'pointer-events-none opacity-50'
                )
            "
        >
            <Link :href="cancelHref">Cancelar</Link>
        </Button>
        <Button
            v-else
            type="button"
            variant="secondary"
            :disabled="processing"
            class="cursor-pointer bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
            @click="emit('cancel')"
        >
            Cancelar
        </Button>

        <!-- Guardar / Guardar Cambios (derecha, primario sólido con Save y spinner) -->
        <Button
            :type="submitType"
            variant="default"
            :disabled="processing || disabled"
            class="cursor-pointer gap-2 shadow-sm"
            @click="handleSubmitClick"
        >
            <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
            <Save v-else class="h-4 w-4" />
            <span>{{ resolvedSaveLabel }}</span>
        </Button>
    </div>
</template>
