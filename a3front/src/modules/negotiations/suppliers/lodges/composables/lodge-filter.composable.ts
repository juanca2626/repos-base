import { ref } from 'vue';
import { useLodgeFilterStore } from '@/modules/negotiations/suppliers/lodges/store/lodge-filter.store';

export function useLodgeFilter() {
  const { filterState } = useLodgeFilterStore();

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
