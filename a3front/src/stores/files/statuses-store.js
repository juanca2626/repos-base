import { defineStore } from 'pinia';
import { fetchStatuses } from '@service/files';
import { createStatusAdapter } from '@store/files/adapters';

export const useStatusesStore = defineStore({
  id: 'statuses',
  state: () => ({
    loading: false,
    statuses: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getStatuses: (state) => state.statuses,
    getStatusByIso: (state) => (iso) => {
      return state.statuses.find((status) => status.iso.toUpperCase() === iso.toUpperCase());
    },
  },
  actions: {
    fetchAll() {
      this.loading = true;
      return fetchStatuses()
        .then(({ data }) => {
          this.statuses = data.data.map((d) => createStatusAdapter(d));
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
