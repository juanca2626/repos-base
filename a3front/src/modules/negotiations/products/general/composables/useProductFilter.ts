import { reactive } from 'vue';
import { debounce } from 'lodash';
import { useProductFilterStore } from '@/modules/negotiations/products/general/store/useProductFilterStore';
import type { ProductFilterInputs } from '@/modules/negotiations/products/general/interfaces/list';

export function useProductFilter() {
  const formState = reactive<ProductFilterInputs>({
    searchTerm: null,
  });

  const { setSearchTerm } = useProductFilterStore();

  const handleSearchTerm = debounce(() => {
    setSearchTerm(formState.searchTerm);
  }, 500);

  return {
    handleSearchTerm,
    formState,
  };
}
