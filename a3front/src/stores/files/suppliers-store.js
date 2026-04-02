import { defineStore } from 'pinia';

import { getSuppliersByCodes } from '@service/files';

const DEFAULT_PER_PAGE = 10;
const DEFAULT_PAGE = 1;

export const useSupplierStore = defineStore({
  id: 'supplier',
  state: () => ({
    // loading
    loading: true,
    // pagination
    total: 0,
    currentPage: DEFAULT_PAGE,
    defaultPerPage: DEFAULT_PER_PAGE,
    perPage: DEFAULT_PER_PAGE,
    // data
    suppliers: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getSuppliers: (state) => state.suppliers,
  },
  actions: {
    async fetchSuppliersByCodes(codes) {
      this.loading = true;
      this.suppliers = [];
      return getSuppliersByCodes(codes)
        .then(({ data }) => {
          if (data.status === 200 && data.error === false) {
            this.suppliers = data.data;
          }
          this.loading_async = false;
        })
        .catch((error) => {
          this.loading_async = false;
          console.log(error);
        });
    },
  },
});
