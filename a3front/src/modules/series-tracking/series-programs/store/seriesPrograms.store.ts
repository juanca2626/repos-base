import { defineStore } from 'pinia';
import type { SeriesProgramsResponseInterface } from '../interfaces/series-programs-response.interface';

export const seriesProgramsStore = defineStore('filters', {
  state: () => ({
    filtersList: {
      from: '',
      to: '',
    },
    programs: [] as SeriesProgramsResponseInterface[],
    loading: false,
  }),

  getters: {
    isLoading: (state) => state.loading,
  },

  actions: {
    updateFilters(newFilters: { from: string; to: string }) {
      this.filtersList = newFilters;
    },
  },
});
