<!-- components/ui/DataActionsDropdown.vue -->
<script setup lang="ts">
/**
 * Componente Reutilizable de Acciones de Fila: Ver, Editar y Eliminar.
 * Actualización: Muestra las opciones de acción (Ver, Editar y Eliminar en rojo)
 * directamente afuera como botones visibles en la fila, conservando el modal de confirmación
 * para la acción de Eliminar.
 */
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Trash2, Eye, PencilLine } from 'lucide-vue-next';
import { ref } from 'vue';
import { useFlashAlert } from '@/composables/useFlashAlert';
import AlertDialog from '@/components/ui/alert-dialog/AlertDialog.vue';
import AlertDialogContent from '@/components/ui/alert-dialog/AlertDialogContent.vue';
import AlertDialogHeader from '@/components/ui/alert-dialog/AlertDialogHeader.vue';
import AlertDialogTitle from '@/components/ui/alert-dialog/AlertDialogTitle.vue';
import AlertDialogDescription from '@/components/ui/alert-dialog/AlertDialogDescription.vue';
import AlertDialogFooter from '@/components/ui/alert-dialog/AlertDialogFooter.vue';
import AlertDialogCancel from '@/components/ui/alert-dialog/AlertDialogCancel.vue';
import AlertDialogAction from '@/components/ui/alert-dialog/AlertDialogAction.vue';
import { route } from 'ziggy-js';

const props = withDefaults(defineProps<{
  editRoute?: string;
  showRoute?: string;
  deleteRoute: string;
  id: number | string;
  status: number | string | boolean;
  isModal?: boolean;
}>(), {
  editRoute: '',
  showRoute: '',
  isModal: false,
});

const emit = defineEmits<{
  (e: 'success', message?: string): void;
  (e: 'error', error: any): void;
  (e: 'edit', id: number | string): void;
  (e: 'show', id: number | string): void;
}>();

const { showAlert } = useFlashAlert();

const isDeleting = ref(false);
const showDeleteDialog = ref(false);

const prepararEliminacion = () => {
  showDeleteDialog.value = true;
};

const cancelarEliminacion = () => {
  showDeleteDialog.value = false;
};

const ejecutarAccion = () => {
  isDeleting.value = true;
  router.delete(route(props.deleteRoute, props.id), {
    data: { eliminar: 1 },
    preserveScroll: true,
    onSuccess: () => {
      const msg = 'Registro eliminado correctamente';
      showAlert(null, msg, 'success');
      emit('success', msg);
      showDeleteDialog.value = false;
      isDeleting.value = false;
    },
    onError: (error) => {
      showAlert(null, 'Hubo un error al procesar la solicitud', 'error');
      emit('error', error);
      isDeleting.value = false;
    },
  });
};
</script>

<template>
  <!-- Diálogo de confirmación de eliminación -->
  <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
    <AlertDialogContent>
      <div class="mx-auto mb-1 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
        <Trash2 class="h-8 w-8" />
      </div>
      <AlertDialogHeader>
        <AlertDialogTitle class="text-xl font-bold text-center">
          ¿Eliminar registro?
        </AlertDialogTitle>
        <AlertDialogDescription class="text-sm text-muted-foreground pt-2 text-center">
          Esta acción eliminará el registro seleccionado.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-center gap-2 mt-4">
        <AlertDialogCancel @click="cancelarEliminacion" class="sm:w-32 border-none hover:bg-secondary">
          Cancelar
        </AlertDialogCancel>
        <AlertDialogAction
          @click="ejecutarAccion"
          class="sm:w-32 font-semibold transition-all shadow-sm bg-red-700 hover:bg-red-700 text-white"
        >
          Eliminar
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>

  <!-- Botones de acción directamente afuera (Ver, Editar, Eliminar en rojo) -->
  <div class="inline-flex items-center justify-center gap-1.5">
    <!-- Ver -->
    <Button
      v-if="isModal"
      type="button"
      variant="outline"
      size="sm"
      class="h-8 px-2.5 text-xs gap-1 cursor-pointer"
      @click.prevent="emit('show', id)"
    >
      <Eye class="h-3.5 w-3.5" />
      <span>Ver</span>
    </Button>
    <Button
      v-else-if="showRoute"
      variant="outline"
      size="sm"
      as-child
      class="h-8 px-2.5 text-xs gap-1"
    >
      <Link :href="route(showRoute, id)">
        <Eye class="h-3.5 w-3.5" />
        <span>Ver</span>
      </Link>
    </Button>

    <!-- Editar -->
    <Button
      v-if="isModal"
      type="button"
      variant="outline"
      size="sm"
      class="h-8 px-2.5 text-xs gap-1 cursor-pointer"
      @click.prevent="emit('edit', id)"
    >
      <PencilLine class="h-3.5 w-3.5" />
      <span>Editar</span>
    </Button>
    <Button
      v-else-if="editRoute"
      variant="outline"
      size="sm"
      as-child
      class="h-8 px-2.5 text-xs gap-1"
    >
      <Link :href="route(editRoute, id)">
        <PencilLine class="h-3.5 w-3.5" />
        <span>Editar</span>
      </Link>
    </Button>

    <!-- Eliminar (en rojo) -->
    <Button
      type="button"
      variant="outline"
      size="sm"
      class="h-8 px-2.5 text-xs gap-1 border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
      @click="prepararEliminacion"
    >
      <Trash2 class="h-3.5 w-3.5" />
      <span>Eliminar</span>
    </Button>
  </div>
</template>
