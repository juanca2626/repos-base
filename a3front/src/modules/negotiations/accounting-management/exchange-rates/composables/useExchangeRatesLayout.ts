import { provide, ref } from 'vue';
import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';

export const useExchangeRatesLayout = () => {
  const showDrawer = ref<boolean>(false);
  const filters = ref<FilterDatesInterface>({
    from: '',
    to: '',
  });

  const isLoading = ref(false);
  provide('isLoading', isLoading);

  const handlerShowDrawer = (show: boolean) => {
    showDrawer.value = show;
  };

  const updateFilters = (newFilters: { from: string; to: string }) => {
    filters.value = newFilters;
  };

  return {
    showDrawer,
    handlerShowDrawer,
    updateFilters,
  };
};
