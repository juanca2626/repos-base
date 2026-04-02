import { defineStore } from 'pinia';
import { sendChunk, sendFiles, deleteFile } from '@/services/global';

export const useUploadsStore = defineStore({
  id: 'uploads',
  state: () => ({
    loading: false,
    loading_chunk: false,
    loading_async: false,
    files: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    isLoadingChunk: (state) => state.loading_chunk,
    isLoadingAsync: (state) => state.loading_async,
  },
  actions: {
    initedAsync() {
      this.loading_async = true;
    },
    finished() {
      this.loading = false;
      this.loading_chunk = false;
      this.loading_async = false;
    },
    sendChunkToLambda(params) {
      this.loading_chunk = true;

      return sendChunk(params)
        .then(({ data }) => {
          this.loading_chunk = false;
          return data;
        })
        .catch((error) => {
          console.log(error);
          this.loading_chunk = false;
          return error;
        });
    },
    sendToS3(params) {
      this.loading = true;

      return sendFiles(params)
        .then(({ data }) => {
          // console.log(data)
          this.loading = false;
          return data.links;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
          return error;
        });
    },
    removeFile(file) {
      this.loading_async = true;
      return deleteFile(file)
        .then(({ data }) => {
          console.log(data);
          this.loading_async = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading_async = false;
        });
    },
  },
});
