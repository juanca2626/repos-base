import { defineStore } from 'pinia';
import { fetchConfiguration, updateConfiguration } from '@service/adventure';

export const useConfigurationStore = defineStore({
  id: 'configuration',
  state: () => ({
    loading: false,
    configuration: {
      code: '',
      description: '',
      data: {
        value: '',
        startDate: '',
        endDate: '',
      },
    },
    error: '',
  }),
  getters: {
    isLoading: (state) => state.loading,
    getConfiguration: (state) => state.configuration,
    getError: (state) => state.error,
  },
  actions: {
    fetchAll() {
      this.loading = true;
      this.error = '';
      return fetchConfiguration()
        .then(({ data }) => {
          if (data.success) {
            this.configuration = data.data.filter((data) => data.code === 'exc')[0];
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    update() {
      this.loading = true;
      this.error = '';
      const params = {
        code: this.configuration.code,
        description: this.configuration.description,
        data: this.configuration.data,
      };

      return updateConfiguration(this.configuration._id, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Error de procesamiento';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
  },
});
