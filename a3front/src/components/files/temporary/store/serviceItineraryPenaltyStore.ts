import { defineStore } from 'pinia';

interface AssumesPenalty {
  hasPenalty: boolean;
  file_id: number | null;
  motive: string | null;
  status_reason_id: number | null;
  executive_id: number | null;
}

interface State {
  assumesPenalty: AssumesPenalty;
}

export const useServiceItineraryPenaltyStore = defineStore('serviceItineraryPenaltyStore', {
  state: (): State => ({
    assumesPenalty: {
      hasPenalty: false,
      file_id: null,
      motive: null,
      status_reason_id: null,
      executive_id: null,
    },
  }),
  actions: {
    addAssumesPenalty(data: AssumesPenalty) {
      this.assumesPenalty = { ...data }; // Asignación directa con propagación
    },
  },
  getters: {
    getAssumesPenalty: (state): AssumesPenalty => state.assumesPenalty,
  },
  persist: {
    pick: ['assumesPenalty'],
    storage: localStorage,
  },
});
