import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSiderBarStore = defineStore('sidebar', () => {
  const status = ref<boolean>(false);
  const modePage = ref<string>('search');
  const searchPage = ref<string>('');

  return {
    status,
    modePage,
    searchPage,

    setStatus(open: boolean, page: string, mode: string) {
      status.value = open;
      searchPage.value = page;
      modePage.value = mode;
    },
  };
});
