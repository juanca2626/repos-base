import { ref } from 'vue';
import { useAirlineFilterStore } from '@/modules/negotiations/suppliers/airlines/store/airline-filter.store';

export function useAirlineFilter() {
  const { filterState } = useAirlineFilterStore();

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
