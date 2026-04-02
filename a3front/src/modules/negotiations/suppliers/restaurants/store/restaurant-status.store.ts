import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

export const useRestaurantStatusStore = defineStore('restaurantStatus', () => {
  const isLoading = ref<boolean>(false);

  const setInactive = async (id: number) => {
    isLoading.value = true;
    try {
      await supplierApi.patch('suppliers/restaurants/inactive', { id });
    } catch (error) {
      console.error('Error al inactivar el proveedor:', error);
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
