import { provide, ref } from 'vue';
import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/filters-inputs.interface';

export const useCostSaleAccounts = () => {
  const filters = ref<FiltersInputsInterface>({
    service_classification_id: null,
    from: '',
    to: '',
    cost_account: null,
    sale_account: null,
  });

  const isLoading = ref(false);
  provide('isLoading', isLoading);

  const updateFilters = (newFilters: {
    service_classification_id: number | null;
    from: string;
    to: string;
    cost_account: number | null;
    sale_account: number | null;
  }) => {
    filters.value = newFilters;
  };

  return {
    filters,
    updateFilters,
  };
};
