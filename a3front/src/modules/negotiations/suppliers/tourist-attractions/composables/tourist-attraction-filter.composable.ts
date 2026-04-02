import { ref } from 'vue';
import { useTouristAttractionFilterStore } from '@/modules/negotiations/suppliers/tourist-attractions/store/tourist-attraction-filter.store';

export function useTouristAttractionFilter() {
  const { filterState } = useTouristAttractionFilterStore();

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
