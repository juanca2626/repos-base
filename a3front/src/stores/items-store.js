import { defineStore } from 'pinia';

export const useItemsStore = defineStore({
  id: 'items',
  state: () => ({
    items: [],
  }),
  actions: {
    setItems(items) {
      this.items = items;
    },
  },
});
