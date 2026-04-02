import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchTypes } from '@ordercontrol/api';

interface TypeFromApi {
  _id: string;
  code: string;
  name: string;
  [key: string]: any; // Para el resto de propiedades
}

interface TypeOption {
  value: string;
  label: string;
}

export const useTypeStore = defineStore('typeStore', () => {
  const isLoading = ref(false);
  const types = ref<TypeOption[]>([]);
  const error = ref<string | null>(null);
  const getTypes = computed(() => types.value);

  /**
   * Fetches types from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAll = async (params: any = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchTypes(params);
      if (response && response.data) {
        types.value = response.data.map((type: TypeFromApi) => ({
          value: type.code,
          label: type.name,
        }));
      } else {
        types.value = [];
      }
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching types.';
      types.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getTypes,
    error,
    fetchAll,
  };
});
