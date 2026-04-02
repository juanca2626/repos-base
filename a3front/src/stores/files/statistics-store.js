import { defineStore } from 'pinia';
import { fetchStatistics } from '@service/files';

import { createStatisticsAdapter } from '@store/files/adapters';

export const useStatisticsStore = defineStore({
  id: 'statistics',
  state: () => ({
    loading: false,
    statistics: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getStatistics: (state) => state.statistics,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      return fetchStatistics()
        .then(({ data }) => {
          this.statistics = createStatisticsAdapter(data.data);
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
