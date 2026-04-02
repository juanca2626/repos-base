import { defineStore } from 'pinia';
import type { State } from '@/quotes/interfaces';

interface StateState {
  states: State[];
}

export const useStatesStore = defineStore({
  id: 'useStatesStore',
  state: () =>
    ({
      states: [] as State[],
    }) as StateState,
  actions: {
    setStates(states: State[]) {
      this.states = states;
    },
  },
});
