import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchOrderStatus } from '@ordercontrol/api';

interface OrderStatusFromApi {
  _id: string;
  code: string;
  name: string;
  [key: string]: any;
}

interface OrderStatusOption {
  value: string;
  label: string;
}

export const useOrderStatusStore = defineStore('orderStatusStore', () => {
  const isLoading = ref(false);
  const orderStatuses = ref<OrderStatusOption[]>([]);
  const error = ref<string | null>(null);

  /**
   * Establece los estados de la orden en el store.
   * Esta acción es utilizada por el store orquestador para poblar los datos.
   * @param {OrderStatusFromApi[]} data - El array de estados de orden crudos desde la API.
   */
  const setOrderStatuses = (data: OrderStatusFromApi[]) => {
    if (data && Array.isArray(data)) {
      orderStatuses.value = data.map((status) => ({ value: status._id, label: status.name }));
    } else {
      orderStatuses.value = [];
    }
  };

  /**
   * Fetches order statuses from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAll = async () => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchOrderStatus();
      console.log('🚀 ~ fetchAll ~ response:', response);
      setOrderStatuses(response?.data || []);
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching order statuses.';
      orderStatuses.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getOrderStatuses: computed(() => orderStatuses.value),
    setOrderStatuses, // Exponer la nueva acción
    error,
    fetchAll,
  };
});
