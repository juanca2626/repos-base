import { defineStore } from 'pinia';
import type { Airline } from '@/quotes/interfaces';

interface AirlinesState {
  airlines: Airline[];
}

export const useAirlinesStore = defineStore({
  id: 'useAirlinesStore',
  state: () =>
    ({
      airlines: [] as Airline[],
    }) as AirlinesState,
  actions: {
    setAirlines(airlines: Airline[]) {
      this.airlines = airlines;
    },
  },
});
