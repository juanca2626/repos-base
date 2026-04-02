import { ref } from 'vue';

export const useQuotasLayout = () => {
  const isLoading = ref<boolean>(false);

  return {
    isLoading,
  };
};
