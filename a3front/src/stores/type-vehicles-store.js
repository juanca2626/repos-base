import { defineStore } from 'pinia';

export const useTypeVehiclesStore = defineStore({
  id: 'typeVehicles',
  state: () => ({
    typeVehicles: [],
  }),
  actions: {
    setTypeVehicles(typeVehicles) {
      this.typeVehicles = typeVehicles;
    },
  },
});
