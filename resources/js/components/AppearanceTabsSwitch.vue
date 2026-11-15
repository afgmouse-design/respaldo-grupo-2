<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import Switch from './ui/switch/Switch.vue';

const { appearance, updateAppearance } = useAppearance();

// Reactividad para preferencia del sistema
// ref: crea una referencia reactiva. Cuando su valor cambia, Vue actualiza la interfaz.
// window.matchMedia(): devuelve un objeto MediaQueryList que representa si el documento coincide con la consulta de medios.
// '(prefers-color-scheme: dark)' pregunta al sistema operativo si el usuario prefiere tema oscuro.
// .matches: propiedad booleana que indica si la consulta se cumple (true = modo oscuro activado).
const systemPrefersDark = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

// Tema efectivo actual (considerando 'system')
// computed: crea un valor derivado que se actualiza automáticamente cuando sus dependencias cambian.
// Depende de appearance.value (de useAppearance) y systemPrefersDark.value.
const currentEffectiveTheme = computed(() => {
  // Si el usuario eligió 'system', usamos la preferencia real del sistema operativo.
  if (appearance.value === 'system') {
    return systemPrefersDark.value ? 'dark' : 'light';
  }
  // Si eligió explícitamente 'dark' o 'light', se devuelve ese valor.
  return appearance.value;
});

// Estado del Switch (true = oscuro, false = claro)
// computed con getter y setter: permite usar v-model en el Switch.
const isDarkMode = computed({
  // getter: devuelve true si el tema efectivo es oscuro.
  get: () => currentEffectiveTheme.value === 'dark',
  // setter: se ejecuta cuando el usuario cambia el Switch.
  set: (newValue: boolean) => {
    // Al cambiar el toggle, salimos del modo 'system' y fijamos explícitamente 'dark' o 'light'.
    // updateAppearance es una función que actualiza el estado global del tema.
    updateAppearance(newValue ? 'dark' : 'light');
  }
});

// Escuchar cambios del sistema (modo oscuro/claro)
// MediaQueryList: objeto que representa la consulta de medios.
let mediaQuery: MediaQueryList | null = null;

// handleSystemChange: función que se ejecuta cuando el sistema cambia de tema (ej. usuario cambia ajuste).
// Recibe un evento MediaQueryListEvent, que contiene .matches con el nuevo estado.
const handleSystemChange = (e: MediaQueryListEvent) => {
  // Actualizamos la referencia reactiva con el nuevo valor oscuro/claro.
  systemPrefersDark.value = e.matches;
};

// onMounted: hook del ciclo de vida de Vue; se ejecuta cuando el componente está montado en el DOM.
// Es el lugar adecuado para agregar event listeners que requieran el DOM.
onMounted(() => {
  // Creamos el MediaQueryList para el tema oscuro.
  mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  // addEventListener: registra la función handleSystemChange para que se ejecute cada vez que cambie la preferencia del sistema.
  mediaQuery.addEventListener('change', handleSystemChange);
});

// onUnmounted: hook que se ejecuta justo antes de desmontar el componente.
// Es importante limpiar los event listeners para evitar fugas de memoria.
onUnmounted(() => {
  // removeEventListener: elimina el listener previamente agregado.
  // El operador ?. asegura que no se llame si mediaQuery es null.
  mediaQuery?.removeEventListener('change', handleSystemChange);
});


</script>

<template>
  <div>
    <Switch
      v-model="isDarkMode"
      id="theme-switch"
      class="w-9 h-5 pl-0.5 scale-110 origin-center"
    >
      <template #thumb>
        <div class="flex items-center justify-center h-full">
          <!-- Mostramos el icono del tema EFECTIVO -->
          <Moon v-if="currentEffectiveTheme === 'dark'" class="size-3 text-primary" />
          <Sun v-else class="size-3 text-primary" />
        </div>
      </template>
    </Switch>
  </div>
</template>
