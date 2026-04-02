import { ref } from 'vue';
import { useStaffFilterStore } from '@/modules/negotiations/suppliers/staff/store/staff-filter.store';

export function useStaffFilter() {
  const { filterState } = useStaffFilterStore();

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
