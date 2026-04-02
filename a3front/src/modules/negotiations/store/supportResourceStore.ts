import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

export const useSupportResourcesStore = defineStore('supportResources', () => {
  const resources = ref([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Función para convertir array de keys y otros parámetros en query params
  function buildQueryParams(
    resourceKeys: string[], // Array de strings
    extraParams: { [key: string]: string | number | undefined } = {}
  ) {
    const params = new URLSearchParams();

    // Agregar resourceKeys al query
    resourceKeys.forEach((key) => {
      params.append('keys[]', key);
    });

    if (extraParams) {
      Object.keys(extraParams).forEach((param) => {
        const value = extraParams[param];
        if (value !== undefined) {
          params.append(param, String(value)); // Convertir a string para evitar el error
        }
      });
    }
    return params.toString();
  }

  async function fetchSupportResources(
    resourceKeys: string[],
    extraParams: { [key: string]: string | number | undefined } = {}
  ) {
    isLoading.value = true;
    error.value = null;

    const queryParams = buildQueryParams(resourceKeys, extraParams);

    try {
      const response = await supportApi.get(`support/resources?${queryParams}`);
      resources.value = response.data.data;
      return response.data.data; // Aquí retornamos explícitamente los datos
    } catch (err) {
      error.value = 'Error al cargar los recursos de soporte';
      console.error('Error fetching support resources:', err);
      throw err; // Lanzar el error para manejarlo en la función que lo llama
    } finally {
      isLoading.value = false;
    }
  }

  return {
    resources,
    isLoading,
    error,
    fetchSupportResources,
  };
});
