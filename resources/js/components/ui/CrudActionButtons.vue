<script setup lang="ts">
/**
 * Componente UI Reutilizable: Botones de Acción por Fila CRUD (CrudActionButtons.vue).
 *
 * Propósito: Estandarizar las acciones horizontales en la columna "Acciones" de las
 * tablas maestras del SOGAC (Ver detalles opcional, Editar en tono advertencia sutil
 * y Eliminar en tono destructivo sutil con confirmación modal via useConfirmDialog),
 * reemplazando botones dispersos o con texto (DataActionsDropdown).
 *
 * Historial:
 * - 2026-11-16: Creación inicial del componente estándar con íconos Lucide compactos,
 *   tooltips accesibles y soporte unificado para rutas Inertia o modales.
 * - 2026-11-16: Ajuste de endurecimiento visual (tamaño explícito `h-8 w-8`, bloqueo de
 *   herencia de atributos `inheritAttrs: false` y refuerzo hover oscuro en amber/red) para
 *   garantizar uniformidad estricta entre todos los maestros del sistema.
 */
import { Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        /** URL directa para ver detalle (alternativa a showRoute). */
        showHref?: string | null;
        /** URL directa para editar (alternativa a editRoute). */
        editHref?: string | null;
        /** URL directa para eliminar vía DELETE (alternativa a deleteRoute). */
        deleteHref?: string | null;
        /** Nombre de ruta Ziggy para detalle. */
        showRoute?: string;
        /** Nombre de ruta Ziggy para edición. */
        editRoute?: string;
        /** Nombre de ruta Ziggy para eliminación. */
        deleteRoute?: string;
        /** Identificador del registro usado al resolver rutas Ziggy o emitir eventos. */
        recordId?: number | string;
        /** Nombre descriptivo del registro mostrado en el diálogo de confirmación. */
        recordName?: string;
        /** Payload adicional enviado en router.delete (ej. { eliminar: 1 }). */
        deletePayload?: Record<string, unknown>;
        /** Modo modal: emite 'show' y 'edit' en lugar de navegar por Inertia Link. */
        isModal?: boolean;
        /** Controla si el maestro dispone de vista o modal de detalle. */
        showView?: boolean;
        /** Permiso para mostrar el botón Ver detalles. */
        canView?: boolean;
        /** Permiso para mostrar el botón Editar. */
        canEdit?: boolean;
        /** Permiso para mostrar el botón Eliminar. */
        canDelete?: boolean;
        /** Título opcional para el modal de confirmación de eliminación. */
        confirmTitle?: string;
        /** Mensaje opcional personalizado para el modal de confirmación. */
        confirmMessage?: string;
        /** Omite el modal interno cuando el componente padre gestiona la confirmación. */
        skipConfirm?: boolean;
        /** Fuerza la emisión de 'delete' incluso si existen rutas configuradas. */
        customDeleteHandler?: boolean;
    }>(),
    {
        showHref: null,
        editHref: null,
        deleteHref: null,
        showRoute: '',
        editRoute: '',
        deleteRoute: '',
        recordId: undefined,
        recordName: '',
        deletePayload: () => ({}),
        isModal: false,
        showView: true,
        canView: true,
        canEdit: true,
        canDelete: true,
        confirmTitle: 'Confirmar Eliminación',
        confirmMessage: undefined,
        skipConfirm: false,
        customDeleteHandler: false,
    }
);

const emit = defineEmits<{
    (e: 'show', id?: number | string): void;
    (e: 'edit', id?: number | string): void;
    (e: 'delete', id?: number | string): void;
    (e: 'deleted', message?: string): void;
    (e: 'error', error: unknown): void;
}>();

const { confirmDialog } = useConfirmDialog();
const isDeleting = ref(false);

const resolvedShowHref = computed(() => {
    if (props.showHref) return props.showHref;
    if (props.showRoute && props.recordId !== undefined && route().has(props.showRoute)) {
        return route(props.showRoute, props.recordId);
    }
    return null;
});

const resolvedEditHref = computed(() => {
    if (props.editHref) return props.editHref;
    if (props.editRoute && props.recordId !== undefined && route().has(props.editRoute)) {
        return route(props.editRoute, props.recordId);
    }
    return null;
});

const resolvedDeleteHref = computed(() => {
    if (props.deleteHref) return props.deleteHref;
    if (props.deleteRoute && props.recordId !== undefined && route().has(props.deleteRoute)) {
        return route(props.deleteRoute, props.recordId);
    }
    return null;
});

const hasViewAction = computed(
    () => props.showView && props.canView && (props.isModal || Boolean(resolvedShowHref.value))
);

const hasEditAction = computed(
    () => props.canEdit && (props.isModal || Boolean(resolvedEditHref.value))
);

const handleDelete = async () => {
    if (isDeleting.value) return;

    if (!props.skipConfirm) {
        const message =
            props.confirmMessage ??
            (props.recordName
                ? `¿Está seguro de que desea eliminar "${props.recordName}"? Desaparecerá de la lista conservando su historial en base de datos.`
                : '¿Está seguro de que desea eliminar este registro? Desaparecerá de la lista conservando su historial en base de datos.');

        const confirmed = await confirmDialog({
            title: props.confirmTitle,
            message,
            confirmText: 'Eliminar',
            cancelText: 'Cancelar',
            variant: 'danger',
            iconType: 'trash',
        });

        if (!confirmed) return;
    }

    if (props.customDeleteHandler || !resolvedDeleteHref.value) {
        emit('delete', props.recordId);
        return;
    }

    isDeleting.value = true;
    router.delete(resolvedDeleteHref.value, {
        data: props.deletePayload as Record<string, any>,
        preserveScroll: true,
        onSuccess: () => {
            emit('deleted', 'Registro eliminado correctamente.');
        },
        onError: (errors) => {
            emit('error', errors);
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <div class="inline-flex items-center justify-center gap-2">
        <!-- Ver detalles (opcional) -->
        <Button
            v-if="hasViewAction && isModal"
            type="button"
            variant="ghost"
            size="icon-sm"
            class="h-8 w-8 cursor-pointer text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
            title="Ver detalles"
            aria-label="Ver detalles"
            @click="emit('show', recordId)"
        >
            <Eye class="h-4 w-4" />
        </Button>
        <Button
            v-else-if="hasViewAction && resolvedShowHref"
            as-child
            variant="ghost"
            size="icon-sm"
            class="h-8 w-8 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
            title="Ver detalles"
            aria-label="Ver detalles"
        >
            <Link :href="resolvedShowHref">
                <Eye class="h-4 w-4" />
            </Link>
        </Button>

        <!-- Editar (advertencia sutil) -->
        <Button
            v-if="hasEditAction && isModal"
            type="button"
            variant="ghost"
            size="icon-sm"
            class="h-8 w-8 cursor-pointer bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 dark:hover:bg-amber-900/50 dark:hover:text-amber-300"
            title="Editar"
            aria-label="Editar"
            @click="emit('edit', recordId)"
        >
            <Pencil class="h-4 w-4" />
        </Button>
        <Button
            v-else-if="hasEditAction && resolvedEditHref"
            as-child
            variant="ghost"
            size="icon-sm"
            class="h-8 w-8 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 dark:hover:bg-amber-900/50 dark:hover:text-amber-300"
            title="Editar"
            aria-label="Editar"
        >
            <Link :href="resolvedEditHref">
                <Pencil class="h-4 w-4" />
            </Link>
        </Button>

        <!-- Eliminar (destructivo sutil con papelera) -->
        <Button
            v-if="canDelete"
            type="button"
            variant="ghost"
            size="icon-sm"
            :disabled="isDeleting"
            class="h-8 w-8 cursor-pointer bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 dark:bg-red-950/50 dark:text-red-400 dark:hover:bg-red-900/50 dark:hover:text-red-300"
            title="Eliminar"
            aria-label="Eliminar"
            @click="handleDelete"
        >
            <Trash2 class="h-4 w-4" />
        </Button>
    </div>
</template>
