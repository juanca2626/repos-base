import { defineStore } from 'pinia';

interface SupplierTaxFilters {
  supplier_sub_classification_id: [];
  from: string;
  to: string;
}

export const filterSupplierTaxStore = defineStore('filtersSupplierTax', {
  state: (): { filtersList: SupplierTaxFilters } => ({
    filtersList: {
      supplier_sub_classification_id: [],
      from: '',
      to: '',
    },
  }),
  actions: {
    updateFiltersSupplierTax(newFilters: SupplierTaxFilters) {
      this.filtersList = newFilters;
    },
  },
});
