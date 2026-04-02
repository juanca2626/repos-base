import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

interface Currency {
  // Define la estructura de una moneda aquí
  // Por ejemplo:
  code: string;
  name: string;
}

export const useCurrencyStore = defineStore('currency', () => {
  const currencies = ref<Currency[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function fetchCurrencies() {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await supportApi.get('support/resources', {
        params: { keys: ['currencies'] },
      });
      currencies.value = response.data.data.currencies;
    } catch (err) {
      error.value = 'Error al cargar las monedas';
      console.error('Error fetching currencies:', err);
    } finally {
      isLoading.value = false;
    }
  }

  return {
    currencies,
    isLoading,
    error,
    fetchCurrencies,
  };
});
