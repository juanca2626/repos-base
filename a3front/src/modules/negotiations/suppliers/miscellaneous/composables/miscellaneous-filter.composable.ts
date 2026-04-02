import { ref } from 'vue';
import { useMiscellaneousFilterStore } from '@/modules/negotiations/suppliers/miscellaneous/store/miscellaneous-filter.store';

export function useMiscellaneousFilter() {
  const { filterState } = useMiscellaneousFilterStore();

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
