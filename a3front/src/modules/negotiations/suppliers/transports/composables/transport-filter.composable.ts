import { ref } from 'vue';
import { useTransportFilterStore } from '@/modules/negotiations/suppliers/transports/store/transport-filter.store';

export function useTransportFilter() {
  const { filterState } = useTransportFilterStore();

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
