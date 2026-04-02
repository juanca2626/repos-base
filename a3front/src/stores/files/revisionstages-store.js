import { defineStore } from 'pinia';
import { fetchRevisionStages } from '@service/files';

import { createRevisionStagesAdapter } from '@store/files/adapters';

export const useRevisionStagesStore = defineStore({
  id: 'revisionStages',
  state: () => ({
    loading: false,
    revisionStages: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getRevisionStages: (state) => state.revisionStages,
    getRevisionStageById: (state) => (id) => {
      const isInvalidId = id === null || id === 0;
      const defaultStage = state.revisionStages[0] || {};

      if (isInvalidId) return defaultStage;

      return state.revisionStages.find((stage) => stage.id === id) || defaultStage;
    },
  },
  actions: {
    async fetchAll() {
      this.loading = true;
      this.revisionStages = [];
      return fetchRevisionStages()
        .then(({ data }) => {
          if (data.success) {
            this.revisionStages = data.data.map((d) => createRevisionStagesAdapter(d));
          }
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
