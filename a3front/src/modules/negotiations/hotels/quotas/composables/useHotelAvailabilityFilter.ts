import { ref } from 'vue';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';

export function useHotelAvailabilityFilter() {
  const { filterState } = useHotelAvailabilityFilterStore();

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
