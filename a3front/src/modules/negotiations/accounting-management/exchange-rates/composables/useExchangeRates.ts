import { provide, ref } from 'vue';
import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';

export const useExchangeRates = () => {
  const filters = ref<FilterDatesInterface>({
    from: '',
    to: '',
  });

  const isLoading = ref(false);
  provide('isLoading', isLoading);

  const updateFilters = (newFilters: { from: string; to: string }) => {
    filters.value = newFilters;
  };

  return {
    filters,
    updateFilters,
  };
};
