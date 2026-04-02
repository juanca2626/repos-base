import { defineStore } from 'pinia';

export const useMarketStore = defineStore({
  id: 'market',
  state: () => ({
    markets: [],
  }),
  actions: {
    setMarkets(marketsToSet) {
      this.markets = marketsToSet;
    },
  },
});
