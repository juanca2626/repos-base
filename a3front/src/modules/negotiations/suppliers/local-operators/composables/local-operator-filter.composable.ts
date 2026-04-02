import { ref } from 'vue';
import { useLocalOperatorFilterStore } from '@/modules/negotiations/suppliers/local-operators/store/local-operator-filter.store';

export function useLocalOperatorFilter() {
  const { filterState } = useLocalOperatorFilterStore();

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
