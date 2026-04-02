import { defineStore } from 'pinia';

export const useInputsMontadosStore = defineStore({
  id: 'inputsMontados',
  state: () => ({
    currentInput: '',
  }),
  getters: {
    getCurrentInput: (state) => state.currentInput,
  },
  actions: {},
});
