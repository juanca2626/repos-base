import { defineStore } from 'pinia';
export const useDashboardsStore = defineStore({
  id: 'dashboards',
  state: () => ({ loading: false }),
  getters: { isLoading: (state) => state.loading },
  actions: {
    setLoading(value) {
      this.loading = value;
    },
  },
});
