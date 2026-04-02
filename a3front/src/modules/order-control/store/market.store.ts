import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchMarkets } from '@ordercontrol/api'; // Se asume que esta es la función API

interface MarketFromApi {
  code: string;
  name: string;
  [key: string]: any; // Para el resto de propiedades no relevantes
}

interface MarketOption {
  value: string;
  label: string;
}

export const useMarketStore = defineStore('marketStore', () => {
  const isLoading = ref(false);
  const markets = ref<MarketOption[]>([]);
  const error = ref<string | null>(null);

  const getMarkets = computed(() => markets.value);

  /**
   * Establece los mercados en el estado del store.
   * Esta acción es utilizada por el store orquestador para poblar los datos.
   * @param {MarketFromApi[]} marketsData - El array de mercados crudos desde la API.
   */
  const setMarkets = (marketsData: MarketFromApi[]) => {
    if (marketsData && Array.isArray(marketsData)) {
      markets.value = marketsData.map((market) => {
        let title = market.name;
        let label = market.name;
        if (market.name === 'ESPANA/ITALIA/PORTUGAL') {
          title = 'ESPANA/ITALIA/PORTUGAL';
          label = 'ESPANA/ITALIA/PORT...';
        }

        return { value: market.code, label, title };
      });
    } else {
      markets.value = [];
    }
  };

  const fetchAll = async (params: any = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchMarkets(params);
      setMarkets(response?.data || []);
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching markets.';
      markets.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getMarkets,
    error,
    setMarkets, // Exponemos la nueva acción
    fetchAll,
  };
});
