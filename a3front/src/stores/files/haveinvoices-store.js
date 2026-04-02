import { defineStore } from 'pinia';
import { fetchHaveInvoices } from '@service/files';

import { createHaveInvoiceAdapter } from '@store/files/adapters';

export const useHaveInvoicesStore = defineStore({
  id: 'haveInvoices',
  state: () => ({
    loading: false,
    haveInvoices: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getHaveInvoices: (state) => state.haveInvoices,
    getHaveInvoiceByIso: (state) => (iso) => {
      iso = iso ? 'SI' : 'NO';
      return state.haveInvoices.find((haveInvoice) => haveInvoice.iso === iso);
    },
  },
  actions: {
    async fetchAll() {
      this.loading = true;
      return fetchHaveInvoices()
        .then(({ data }) => {
          this.haveInvoices = data.data.map((d) => createHaveInvoiceAdapter(d));
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
