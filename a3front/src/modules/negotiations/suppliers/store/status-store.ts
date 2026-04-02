import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

export const useStatusStore = defineStore('statusStore', () => {
  const isLoading = ref<boolean>(false);

  const setInactive = async (resource: string, id: number) => {
    isLoading.value = true;
    try {
      await supplierApi.patch(`suppliers/${resource}/inactive`, { id });
    } catch (error) {
      console.error(`Error al inactivar ${resource}:`, error);
      throw error;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    setInactive,
  };
});
