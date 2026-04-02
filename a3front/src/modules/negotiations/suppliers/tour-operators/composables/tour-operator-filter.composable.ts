import { ref } from 'vue';
import { useTourOperatorFilterStore } from '@/modules/negotiations/suppliers/tour-operators/store/tour-operator-filter.store';

export function useTourOperatorFilter() {
  const { filterState } = useTourOperatorFilterStore();

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
