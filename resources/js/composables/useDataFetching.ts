import { ref, watch, computed, toRaw } from 'vue';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { route } from 'ziggy-js';

/**
 * Interfaz que define las opciones de configuración para el comportamiento del composable.
 */
interface FetchOptions {
  routeName: string;       // El nombre de la ruta de Laravel definida en web.php (Ej: 'product.index').
  listRouteName: string;   // El nombre de la propiedad (prop) que el controlador de Laravel envía a la vista (Ej: 'products').
  preserveState?: boolean;  // Determina si se mantiene el estado de los componentes (foco, inputs) al recargar los datos (Por defecto: true).
  preserveScroll?: boolean; // Determina si se mantiene la posición del scroll de la ventana al recargar los datos (Por defecto: true).
  perPageDefault?: number; // Cantidad predeterminada de registros a mostrar por página si no viene especificado en la URL (Por defecto: 10).
  debounceMs?: number;     // Tiempo de espera en milisegundos para aplicar el "debounce" al escribir en filtros (Por defecto: 500ms).
}

/**
 * Composable genérico para la gestión automatizada y reactiva de listados de datos con Inertia.js.
 * Se encarga de centralizar el estado de la paginación, filtros de búsqueda, cargas concurrentes y limpieza de URLs.
 * * @param initialData - Datos iniciales enviados por Laravel (la prop que contiene la estructura del paginador).
 * @param filters - Los filtros iniciales que provienen de la URL actual (inyectados por el controlador).
 * @param options - Objeto de configuración personalizada.
 */
export function useDataFetching<T>(
  initialData: any,
  filters: Record<string, any>,
  options: FetchOptions
) {
  // --------------------------------------------------------------------------
  // DESESTRUCTURACIÓN Y ASIGNACIÓN DE VALORES POR DEFECTO
  // --------------------------------------------------------------------------
  const {
    routeName,
    listRouteName,
    preserveState = true,
    preserveScroll = true,
    perPageDefault = 10,
    debounceMs = 500,
  } = options;

  // --------------------------------------------------------------------------
  // ESTADOS REACTIVOS (REFS)
  // --------------------------------------------------------------------------
  
  // 'data' almacena los registros actuales de la base de datos (y la info de paginación).
  const getInitialValue = () => typeof initialData === 'function' ? initialData() : initialData;
  const data = ref(getInitialValue());

  /**
   * SINCRONIZACIÓN CRUCIAL CON EL BACKEND:
   * Cuando Inertia.js refresca la página o actualiza las props desde el servidor,
   * este watch detecta los nuevos datos en 'initialData' y los vuelca en nuestro ref local.
   * Sin esto, la interfaz ignoraría los cambios del backend tras la primera carga.
   */
  watch(
    typeof initialData === 'function' ? initialData : () => initialData,
    (newData) => {
      data.value = newData;
    },
    { deep: true }
  );

  // Bandera para controlar el estado visual de carga (skeletons, spinners) y evitar peticiones duplicadas.
  const isLoading = ref(false);

  // Cantidad de registros por página actual (se extrae del filtro de Laravel o se asigna el default).
  const perPage = ref(String(filters.per_page || filters.registros_per_page || perPageDefault));

  // Clon local reactivo de los filtros iniciales para que el usuario interactúe en el frontend de forma aislada.
  const filterValues = ref<Record<string, any>>({ ...filters });

  // --------------------------------------------------------------------------
  // NÚCLEO: PETICIÓN HTTP (fetchData)
  // --------------------------------------------------------------------------
  
  /**
   * Recolecta los filtros, limpia los valores nulos o vacíos, y hace la petición HTTP GET con Inertia.
   * @param extraParams - Parámetros adicionales de un solo uso (por ejemplo: la nueva página `{ page: 2 }`).
   */
  const fetchData = (extraParams: Record<string, any> = {}) => {
    // Patrón de guarda: Evita que si una petición está en proceso, el usuario dispare otra idéntica (spam de clics).
    if (isLoading.value) return;
    isLoading.value = true;

    try {
      /**
       * PASO 1: LIMPIEZA RADICAL
       * Se convierte el objeto reactivo de Vue a string JSON y de vuelta a objeto plano de JS.
       * Esto rompe proxies reactivos, referencias en memoria y limpia funciones o estructuras circulares complejas.
       */
      const cleanFilters = JSON.parse(JSON.stringify(filterValues.value));

      /**
       * PASO 2: LIMPIEZA DE URL (Evitar parámetros vacíos)
       * Recorremos las propiedades y omitimos strings vacíos, null o undefined.
       * Esto evita urls sucias como: `perfiles?search=&status=&fecha_inicio=`
       */
      const params: Record<string, any> = {};
      Object.keys(cleanFilters).forEach(key => {
        const val = cleanFilters[key];
        if (val !== '' && val !== null && val !== undefined && key !== 'per_page' && key !== 'registros_per_page') {
          params[key] = val;
        }
      });

      // Combinamos los filtros limpios, el selector de cantidad de registros y parámetros temporales como el número de página.
      const finalParams = {
        ...params,
        per_page: perPage.value,
        registros_per_page: perPage.value,
        ...extraParams,
      };

      /**
       * PASO 3: EJECUCIÓN DEL VIAJE CON INERTIA.JS
       * Realiza la recarga parcial de la URL mapeada por Ziggy-js.
       */
      router.get(
        route(routeName), // Genera la URL dinámica (Ej: http://localhost/perfil)
        finalParams,      // Envía los filtros y la paginación como Query Params (?search=admin&page=1)
        {
          preserveState: preserveState,   // Mantiene el estado de Vue (los inputs no pierden el foco que tiene el usuario).
          preserveScroll: preserveScroll, // Evita que la pantalla salte bruscamente al inicio de la página al filtrar.
          replace: true,                  // Reemplaza el historial del navegador para no acumular un registro "Atrás/Adelante" por cada tecla presionada.
          
          /**
           * CONFIGURACIÓN VITAL: 'forceFormData: false'
           * Por defecto, si Inertia detecta un objeto complejo, podría intentar evaluar si hay archivos adjuntos de forma recursiva
           * mediante la función interna 'hasFiles'. Forzarlo a false optimiza drásticamente el rendimiento en el envío de JSON.
           */
          forceFormData: false, 
          
          onSuccess: (page) => {
            // Si Laravel responde exitosamente con la propiedad esperada, actualizamos el estado local de forma inmediata.
            if (page.props[listRouteName]) {
              data.value = page.props[listRouteName] as any;
            }
          },
          // Tanto si termina con éxito o con error, liberamos el estado bloqueante de carga.
          onFinish: () => (isLoading.value = false),
          onError: () => (isLoading.value = false),
        }
      );
    } catch (error) {
      console.error("Error procesando filtros:", error);
      isLoading.value = false;
    }
  };

  // --------------------------------------------------------------------------
  // AUTOMATIZACIÓN Y DEBOUNCE
  // --------------------------------------------------------------------------
  
  /**
   * Retrasa la ejecución de la búsqueda. Cada vez que el usuario escribe, el reloj se reinicia.
   * Cuando el usuario deja de escribir durante el tiempo estipulado (debounceMs), se dispara 'fetchData'
   * forzando a regresar a la página 1.
   */
  const debouncedFetch = useDebounceFn(() => fetchData({ page: 1 }), debounceMs);

  /**
   * Observador profundo en los filtros:
   * Si cambia el buscador, select de estado, fechas, etc., se ejecuta la búsqueda con retraso (debounce).
   */
  watch(() => filterValues.value, () => debouncedFetch(), { deep: true });



  // --------------------------------------------------------------------------
  // MÉTODOS AUXILIARES DE INTERFAZ (API Pública)
  // --------------------------------------------------------------------------
  
  /**
   * Asigna un valor de forma segura a un campo de filtro específico.
   */
  const setFilter = (key: string, value: any) => {
    filterValues.value[key] = value;
  };

  /**
   * Resetea todos los campos de filtros a string vacío y recarga los datos desde la página 1.
   */
  const clearFilters = () => {
    Object.keys(filterValues.value).forEach(key => {
      filterValues.value[key] = '';
    });
    fetchData({ page: 1 });
  };

  /**
   * Propiedad Computada: Evalúa si hay algún filtro de búsqueda activo actualmente.
   * Omite de la evaluación las variables 'page' y 'registros_per_page'.
   * Útil en el frontend para mostrar/ocultar condicionalmente el botón de "Limpiar filtros" (la X roja).
   */
  const hasActiveFilters = computed(() => {
    const ignore = ['registros_per_page', 'page'];
    return Object.entries(filterValues.value).some(([key, v]) => 
      !ignore.includes(key) && v !== '' && v !== null && v !== undefined
    );
  });

  // --------------------------------------------------------------------------
  // RETORNO DEL COMPOSABLE (Exposición al componente <script setup>)
  // --------------------------------------------------------------------------
  return {
    data,              // El objeto paginado con los registros actuales de la BD.
    isLoading,         // Estado booleano de la carga HTTP.
    filterValues,      // Objeto reactivo vinculado con los v-model de los inputs.
    perPage,           // Cantidad de registros por página actual (Ref string).
    fetchData,         // Ejecutor manual de peticiones en caso de requerirse.
    setFilter,         // Función para mutar filtros individuales de forma segura.
    clearFilters,      // Función para resetear todos los inputs.
    hasActiveFilters,  // Computed boolean para control de visibilidad en UI.
    
    // Cambia la cantidad de filas en la tabla y ejecuta la recarga con la nueva paginación.
    changePerPage: (v: string | number) => {
      perPage.value = String(v);
      fetchData({ page: 1 });
    },
    
    // Método disparado por los clics del componente de paginación (Ej: Página Anterior, Siguiente o Número específico).
    onPageChange: (page: number) => fetchData({ page }),
  };
}