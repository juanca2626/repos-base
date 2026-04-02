import { defineStore } from 'pinia';
import { fetchManifestos } from '@service/adventure';
import { createManifestosAdapter } from './adapters';

export const useManifestosStore = defineStore({
  id: 'manifestos',
  state: () => ({
    loading: false,
    manifestos: [],
    total: 0,
    total_paxs: 0,
    error: '',
    filters: {
      codes: '',
      date_from: '',
      date_to: '',
    },
    pagination: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getManifestos: (state) => state.manifestos,
    getTotal: (state) => state.total,
    getTotalPaxs: (state) => state.total_paxs,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      this.manifestos = [];
      this.pagination = {};

      const params = {
        codes: this.filters.codes,
        startDate: this.filters.date_from,
        endDate: this.filters.date_to,
      };
      return fetchManifestos(params)
        .then(({ data }) => {
          console.log(data.data);
          if (data.success) {
            const items = data.data.data ?? [];
            this.manifestos = items.map((manifestos) => createManifestosAdapter(manifestos));
            this.total = data.data.total;
            this.total_paxs = data.data.totalPaxs;
          }
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
          this.error = err.data.message;
          this.loading = false;
        });
    },
  },
});
