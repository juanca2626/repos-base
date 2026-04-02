import { ref } from 'vue';
import { useTrainFilterStore } from '@/modules/negotiations/suppliers/trains/store/train-filter.store';

export function useTrainFilter() {
  const { filterState } = useTrainFilterStore();

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
