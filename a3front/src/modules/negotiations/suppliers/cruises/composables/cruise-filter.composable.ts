import { ref } from 'vue';
import { useCruiseFilterStore } from '@/modules/negotiations/suppliers/cruises/store/cruise-filter.store';

export function useCruiseFilter() {
  const { filterState } = useCruiseFilterStore();

  const showDrawerForm = ref<boolean>(false);

  const handleAddFilter = () => {
    showDrawerForm.value = true;
  };

  return {
    showDrawerForm,
    filterState,
    handleAddFilter,
  };
}
