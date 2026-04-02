import { defineStore } from 'pinia';
import type { File } from '../interfaces/file';

export const useFileStore = defineStore('fileStore', {
  state: () => ({
    files: [] as File[],
  }),

  getters: {
    getById: (state) => (id: string) => {
      return state.files.find((file) => file._id === id);
    },
  },

  actions: {
    setFiles(files: File[]) {
      this.files = files;
    },

    addFile(file: File) {
      this.files.push(file);
    },

    removeFileById(id: string) {
      this.files = this.files.filter((file) => file._id !== id);
    },

    clearFiles() {
      this.files = [];
    },
  },
});
