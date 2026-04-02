import { defineStore } from 'pinia';

export const useCampusStore = defineStore({
  id: 'campus',
  state: () => ({
    campus: [],
  }),
  actions: {
    setCampus(campus) {
      this.campus = campus;
      this.campus2 = '';
    },
  },
});
