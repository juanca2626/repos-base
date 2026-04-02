import { defineStore } from 'pinia';

export const useQuotesStore = defineStore({
  id: 'quotes',
  state: () => ({
    view: 'table',
    isModalOpened: false,
  }),
  actions: {
    setView(view) {
      this.view = view;
    },
    openModals() {
      this.isModalOpened = true;
    },
    closeModals() {
      this.isModalOpened = false;
    },
  },
});
