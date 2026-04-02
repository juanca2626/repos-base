import { defineStore } from 'pinia';

interface LoaderState {
  isLoading: boolean;
}

export const useLoaderStore = defineStore({
  id: 'loaderStore',
  state: () =>
    ({
      isLoading: false,
    }) as LoaderState,
  actions: {
    showIsLoading() {
      this.isLoading = true;
    },
    closeIsLoading() {
      this.isLoading = false;
    },
  },
});
