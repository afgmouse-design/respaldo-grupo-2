<!-- components/ui/DataCard.vue -->
<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  item: any;               // el objeto de datos (producto, usuario, etc.)
  // Opcional: estado activo/inactivo para estilos
  statusKey?: string;      // nombre de la propiedad que indica estado (ej. 'status')
  activeValue?: any;       // valor que representa "activo" (ej. 1)
  // Badges opcionales
  badges?: Array<{ key: string; label: string; variant?: string }>;
}>();

const isActive = computed(() => {
  if (!props.statusKey) return true;
  return props.item[props.statusKey] === props.activeValue;
});
</script>

<template>
  <div
    class="group relative backdrop-blur-md shadow-md bg-white/10 rounded-xl border border-gray-400/20 overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 hover:bg-gray-100/20 dark:border-gray-200/20"
    :class="{ 'opacity-75 grayscale': !isActive }"
  >
    <!-- Link opcional: si se proporciona un slot "link" o se usa la prop href -->
    <slot name="link" :item="item">
      <!-- Por defecto, sin enlace, solo contenido -->
      <div class="flex flex-col sm:flex-row">
        <!-- Slot para la imagen (izquierda) -->
        <div class="relative w-full sm:w-48 md:w-56 shrink-0 bg-linear-to-br from-gray-100/10 to-gray-200/5 p-3">
          <slot name="image" :item="item">
            <!-- Imagen por defecto -->
            <div class="flex items-center justify-center min-h-30 sm:min-h-35">
              <img :src="item.image || '/placeholder-image.png'" class="w-full h-full rounded-xl object-contain transition-transform duration-300 group-hover:scale-105" />
            </div>
          </slot>
          <!-- Badge flotante superior izquierdo (ej. categoría) -->
          <slot name="badge-top-left" :item="item" />
          <!-- Badge flotante inferior izquierdo (ej. sección) -->
          <slot name="badge-bottom-left" :item="item" />
          <!-- Indicador de estado (activo/inactivo) -->
          <div class="absolute top-2 right-2">
            <div
              class="w-2 h-2 rounded-full shadow-lg"
              :class="isActive ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
            ></div>
          </div>
        </div>

        <!-- Contenido principal (derecha) -->
        <div class="flex-1 p-4 flex flex-col">
          <div class="flex items-start justify-between gap-2 mb-2">
            <slot name="title" :item="item">
              <h3 class="font-medium text-foreground text-sm line-clamp-2 flex-1 hover:text-primary transition-colors">
                {{ item.name || item.title || 'Sin título' }}
              </h3>
            </slot>
            <!-- Menú de acciones (dropdown) -->
            <slot name="actions" :item="item" />
          </div>

          <slot name="description" :item="item">
            <p class="text-xs text-muted-foreground line-clamp-2 mb-3">
              {{ item.description || 'Sin descripción' }}
            </p>
          </slot>

          <slot name="meta" :item="item">
            <!-- Metadatos opcionales (vendedor, calificación, etc.) -->
          </slot>

          <slot name="footer" :item="item">
            <div class="flex items-center justify-between pt-2 border-t border-white/10 mt-auto">
              <span class="text-[9px] text-muted-foreground">ID: #{{ item.id }}</span>
            </div>
          </slot>
        </div>
      </div>
    </slot>
    <!-- Efecto de brillo al hover -->
    <div class="absolute inset-0 bg-linear-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
  </div>
</template>