import { defineStore } from 'pinia';
import type { Destinations } from '@/quotes/interfaces';

interface DestinationsState {
  destinations: Destinations;
}
export const useDestinationsStore = defineStore({
  id: 'useDestinationsStore',
  state: () =>
    ({
      destinations: {} as Destinations,
    }) as DestinationsState,
  actions: {},
});
