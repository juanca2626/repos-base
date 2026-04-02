import { useLoaderStore } from '@/quotes/store/loader.store';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

const useLoader = () => {
  const store = useLoaderStore();
  const { isLoading } = storeToRefs(store);
  return {
    // Properties
    isLoading: computed(() => isLoading.value),

    // Methods
    showIsLoading: () => store.showIsLoading(),
    closeIsLoading: () => store.closeIsLoading(),

    // Getters
  };
};

export default useLoader;
