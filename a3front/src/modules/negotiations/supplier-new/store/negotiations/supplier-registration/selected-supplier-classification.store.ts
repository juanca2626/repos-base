import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { PersistenceOptions } from 'pinia-plugin-persistedstate';

export const useSelectedSupplierClassificationStore = defineStore(
  'selectedSupplierClassificationStore',
  () => {
    /** typeCode del tipo de proveedor preseleccionado por navegación desde el listado */
    const selectedClassificationId = ref<string | null>(null);

    const setSelectedClassificationId = (id: string | null) => {
      selectedClassificationId.value = id;
    };

    return {
      selectedClassificationId,
      setSelectedClassificationId,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: ['selectedClassificationId'],
    } as PersistenceOptions,
  }
);
