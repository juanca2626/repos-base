import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchCustomers } from '@ordercontrol/api';

interface CustomerFromApi {
  _id: string;
  code: string;
  name: string;
  [key: string]: any; // Para el resto de propiedades
}

interface CustomerOption {
  value: string;
  label: string;
  title?: string;
}

export const useCustomerStore = defineStore('customerStore', () => {
  const isLoading = ref(false);
  const customers = ref<CustomerOption[]>([]);
  const error = ref<string | null>(null);
  const getCustomers = computed(() => customers.value);

  const getCustomersWithTruncatedLabel = computed(() => {
    return customers.value.map((customer) => {
      const code = customer.value;
      const name = customer.label.split(' - ')[1] || '';
      const fullLabel = `${code} - ${name}`;
      const maxLabelLength = 19;

      let label = fullLabel;
      if (fullLabel.length > maxLabelLength) {
        const prefix = `${code} - `;
        const remainingLength = maxLabelLength - prefix.length;
        const truncatedName = name.slice(0, remainingLength - 3) + '...';
        label = `${prefix}${truncatedName}`;
      }

      return {
        value: code,
        label,
        title: fullLabel,
      };
    });
  });

  const getCustomersWithFullLabel = computed(() => customers.value);

  /**
   * Establece los clientes en el estado del store.
   * Esta acción es utilizada por el store orquestador para poblar los datos.
   * @param {CustomerFromApi[]} customersData - El array de clientes crudos desde la API.
   */
  const setCustomers = (customersData: CustomerFromApi[]) => {
    if (customersData && Array.isArray(customersData)) {
      customers.value = customersData.map((customer) => ({
        value: customer.code,
        label: `${customer.code} - ${customer.name}`,
      }));
    } else {
      customers.value = [];
    }
  };

  /**
   * Fetches customers from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAll = async (params: any = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchCustomers(params);
      setCustomers(response?.data || []);
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching customers.';
      customers.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getCustomers,
    getCustomersWithTruncatedLabel,
    getCustomersWithFullLabel,
    error,
    setCustomers,
    fetchAll,
  };
});
