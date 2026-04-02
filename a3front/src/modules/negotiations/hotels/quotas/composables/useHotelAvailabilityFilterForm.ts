import { ref } from 'vue';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/suppliers/interfaces';
import { useHotelAvailabilityFilterStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-filter.store';

export function useHotelAvailabilityFilterForm(emit: DrawerEmitTypeInterface) {
  const { filterState, resetFilterState, setApplyFilters } = useHotelAvailabilityFilterStore();

  const isLoading = ref<boolean>(false);

  const collapseSectionKeys = ref<string[]>(['hotel-chain', 'hotel-categories', 'rate-types']);

  const handleClose = (): void => {
    emit('update:showDrawerForm', false);
  };

  const cleanFilters = () => {
    resetFilterState();
  };

  const handleCleanFilters = () => {
    cleanFilters();
    handleClose();
    setApplyFilters(true);
  };

  const handleApplyFilters = () => {
    setApplyFilters(true);
    handleClose();
  };

  return {
    isLoading,
    collapseSectionKeys,
    filterState,
    handleClose,
    handleCleanFilters,
    handleApplyFilters,
  };
}
