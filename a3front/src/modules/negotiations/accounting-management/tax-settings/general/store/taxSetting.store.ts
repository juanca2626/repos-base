import { defineStore } from 'pinia';

export const taxSettingStore = defineStore('filters', {
  state: () => ({
    filtersList: {
      from: '',
      to: '',
    },
  }),
  actions: {
    updateFilters(newFilters: { from: string; to: string }) {
      this.filtersList = newFilters;
    },
  },
});
