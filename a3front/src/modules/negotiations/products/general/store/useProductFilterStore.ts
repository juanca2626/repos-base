import { defineStore } from 'pinia';
import type { ProductFilterInputs } from '@/modules/negotiations/products/general/interfaces/list';

export const useProductFilterStore = defineStore('productFilterStore', {
  state: () =>
    ({
      searchTerm: null,
    }) as ProductFilterInputs,
  actions: {
    setSearchTerm(searchTerm: string | null) {
      this.searchTerm = searchTerm;
    },
    resetFilters() {
      this.searchTerm = null;
    },
  },
});
