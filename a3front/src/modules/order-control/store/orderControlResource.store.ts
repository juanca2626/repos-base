import { defineStore } from 'pinia';
import { fetchBootstrapData } from '@ordercontrol/api';
import { useMarketStore } from '@ordercontrol/store/market.store';
import { useCustomerStore } from '@ordercontrol/store/customer.store';
import { useOrderStatusStore } from '@ordercontrol/store/order-status.store';
import { useGeneralStore } from '@ordercontrol/store/general.store';
import { useTemplateStore } from '@ordercontrol/store/template.store';
import { ref } from 'vue'; // @ts-ignore

export const useOrderControlResourceStore = defineStore('orderControlResourceStore', () => {
  const isLoading = ref(false);
  const error = ref(null);

  // Instancias de los otros stores
  const marketStore = useMarketStore();
  const customerStore = useCustomerStore();
  const orderStatusStore = useOrderStatusStore();
  const generalStore = useGeneralStore();
  const templateStore = useTemplateStore();

  const fetchAll = async () => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchBootstrapData();
      if (response && response.data) {
        const { markets, customers, orderStatus, teams, templates } = response.data;
        marketStore.setMarkets(markets);
        customerStore.setCustomers(customers);
        orderStatusStore.setOrderStatuses(orderStatus);
        generalStore.setTeams(teams.teams);
        templateStore.setTemplates(templates, {
          page: 1,
          per_page: templates.length,
          total: templates.length,
        });

        return true;
      }
    } catch (e: any) {
      error.value = e.message || 'Failed to fetch resources';
      console.error(error.value);
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  return { isLoading, error, fetchAll };
});
