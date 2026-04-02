import { provide, ref } from 'vue';
import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/filter-inputs.interface';

export const useSupplierTax = () => {
  const showDrawer = ref<boolean>(false);

  const filtersSupplierTax = ref<FiltersInputsInterface>({
    supplier_sub_classification_id: [],
    from: '',
    to: '',
  });

  const isLoading = ref(false);
  provide('isLoading', isLoading);

  const updateFilters = (newFilters: {
    supplier_sub_classification_id: [];
    from: string;
    to: string;
  }) => {
    filtersSupplierTax.value = newFilters;
  };

  const handlerShowDrawer = (show: boolean) => {
    showDrawer.value = show;
  };

  return {
    showDrawer,
    filtersSupplierTax,
    handlerShowDrawer,
    updateFilters,
  };
};
