import { defineStore } from 'pinia';
import type { SupplierAssignmentFilter } from '@/modules/negotiations/products/general/interfaces/form';

export const useSupplierAssignmentFilterStore = defineStore('supplierAssignmentFilterStore', {
  state: () =>
    ({
      locationKey: null,
      searchTerm: null,
      supplierClassificationId: null,
    }) as SupplierAssignmentFilter,
  actions: {
    setLocationKey(locationKey: string | null) {
      this.locationKey = locationKey;
    },
    setSearchTerm(searchTerm: string | null) {
      this.searchTerm = searchTerm;
    },
    setSupplierClassificationId(supplierClassificationId: number | null) {
      this.supplierClassificationId = supplierClassificationId;
    },
    resetFilters() {
      this.locationKey = null;
      this.searchTerm = null;
      this.supplierClassificationId = null;
    },
  },
});
