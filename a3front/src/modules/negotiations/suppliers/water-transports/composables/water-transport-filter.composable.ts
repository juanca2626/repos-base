import { ref } from 'vue';
import { useWaterTransportFilterStore } from '@/modules/negotiations/suppliers/water-transports/store/water-transport-filter.store';

export function useWaterTransportFilter() {
  const { filterState } = useWaterTransportFilterStore();

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
