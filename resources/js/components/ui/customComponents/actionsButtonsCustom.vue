<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { CirclePlus, Trash2, PencilLine, Eye, MoreVertical } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { ref } from 'vue';
import AlertDialog from '../alert-dialog/AlertDialog.vue';
import AlertDialogContent from '../alert-dialog/AlertDialogContent.vue';
import AlertDialogHeader from '../alert-dialog/AlertDialogHeader.vue';
import AlertDialogTitle from '../alert-dialog/AlertDialogTitle.vue';
import AlertDialogDescription from '../alert-dialog/AlertDialogDescription.vue';
import AlertDialogFooter from '../alert-dialog/AlertDialogFooter.vue';
import AlertDialogCancel from '../alert-dialog/AlertDialogCancel.vue';
import AlertDialogAction from '../alert-dialog/AlertDialogAction.vue';
import DropdownMenuSeparator from '../dropdown-menu/DropdownMenuSeparator.vue';
import { route } from 'ziggy-js';


const props = defineProps<{
  edit: string;
  show: string;
  delete: string;
  id: number;
  status: number;
}>();

const emit = defineEmits<{
  success: [message?: string];
  error: [error: any];
}>();

// Estado para diálogo de confirmación
const isDeleting = ref(false);
const showDeleteDialog = ref(false);
const registroSeleccionado = ref<{ id: number; status: number } | null>(null);

const prepararEliminacion = () => {
  registroSeleccionado.value = { id: props.id, status: props.status };
  showDeleteDialog.value = true;
};

const cancelarEliminacion = () => {
  showDeleteDialog.value = false;
  registroSeleccionado.value = null;
};

const ejecutarAccion = () => {
  if (!registroSeleccionado.value) return;

  const { id, status } = registroSeleccionado.value;
  isDeleting.value = true;

  router.delete(route(props.delete, id), {
    onSuccess: () => {
      toast.success(status == 1 ? 'Registro eliminado correctamente' : 'Registro activado correctamente');
      showDeleteDialog.value = false;
      isDeleting.value = false;
    },
    onError: () => {
      toast.error('Hubo un error al procesar la solicitud');
      isDeleting.value = false;
    },
    onFinish: () => {
      registroSeleccionado.value = null;
    }
  });
};
</script>

<template>
  <!-- Diálogo de confirmación -->
  <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
    <AlertDialogContent>
      <div :class="[
        'mx-auto mb-1 flex h-12 w-12 items-center justify-center rounded-full',
        status == 1 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'
      ]">
        <Trash2 v-if="status == 1" class="h-8 w-8" />
        <CirclePlus v-else class="h-8 w-8" />
      </div>

      <AlertDialogHeader>
        <AlertDialogTitle class="text-xl font-bold text-center">
          {{ status == 1 ? '¿Eliminar registro?' : '¿Activar registro?' }}
        </AlertDialogTitle>
        <AlertDialogDescription class="text-sm text-muted-foreground pt-2 text-center">
          {{ status == 1
            ? 'Esta acción desactivará el registro. Podrás reactivarlo más tarde si es necesario.'
            : 'El registro volverá a estar disponible para todas las operaciones.'
          }}
        </AlertDialogDescription>
      </AlertDialogHeader>

      <AlertDialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-center gap-2 mt-4">
        <AlertDialogCancel @click="cancelarEliminacion" class="sm:w-32 border-none hover:bg-secondary">
          Cancelar
        </AlertDialogCancel>
        <AlertDialogAction @click="ejecutarAccion" :class="[
          'sm:w-32 font-semibold transition-all shadow-sm',
          status == 1 ? 'bg-red-700 hover:bg-red-700 text-white' : 'bg-green-700 hover:bg-green-700 text-white'
        ]">
          {{ status == 1 ? 'Confirmar' : 'Activar' }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>

  <!-- Dropdown Menu de acciones -->
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon-lg" class="focus:outline-none">
        <MoreVertical class="h-5 w-5" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-40">
      <!-- Ver detalles (solo activo) -->
      <DropdownMenuItem as-child>
        <Link :href="route(props.show, props.id)" class="flex items-center gap-2 cursor-pointer">
          <!-- <Eye class="h-4 w-4" /> -->
          <span>Ver</span>
        </Link>
      </DropdownMenuItem>

      <!-- Editar (solo activo) -->
      <DropdownMenuItem v-if="status == 1" as-child>
        <Link :href="route(props.edit, props.id)" class="flex items-center gap-2 cursor-pointer">
          <!-- <PencilLine class="h-4 w-4" /> -->
          <span>Editar</span>
        </Link>
      </DropdownMenuItem>
      <DropdownMenuSeparator v-if="status == 1"/>

      <!-- Activar / Desactivar -->
      <DropdownMenuItem
        @click="prepararEliminacion"
        class="cursor-pointer"
        :class="status == 1 ? 'text-destructive font-semibold focus:text-red-500' : 'text-green-600 font-semibold focus:text-green-600 focus:bg-green-950/70'"
      >
        <!-- <Trash2 v-if="status == 1" class="h-4 w-4 mr-2" />
        <CirclePlus v-else class="h-4 w-4 mr-2" /> -->
        <span>{{ status == 1 ? 'Desactivar' : 'Activar' }}</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>