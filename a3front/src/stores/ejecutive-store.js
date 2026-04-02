import { defineStore } from 'pinia';

export const useEjecutiveStore = defineStore({
  id: 'ejecutive',
  state: () => ({
    ejecutives: [],
  }),
  actions: {
    setEjecutives(ejecutives) {
      this.ejecutives = ejecutives;
    },
  },
});
