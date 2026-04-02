import { defineStore } from 'pinia';
import type { Origin } from '@/quotes/interfaces';

interface OriginsState {
  origins: Origin[];
}

export const useOriginsStore = defineStore({
  id: 'useOriginsStore',
  state: () =>
    ({
      origins: [] as Origin[],
    }) as OriginsState,
  actions: {
    setOrigins(origins: Origin[]) {
      this.origins = origins;
    },
  },
});
