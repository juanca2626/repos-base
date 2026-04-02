import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export interface HotelAvailabilityFilterState {
  hotelChain: string | null;
  hotelCategories: string[];
  rateTypes: string[];
  dateRange: {
    from: string | null;
    to: string | null;
  };
}

export const useHotelAvailabilityFilterStore = defineStore('hotelAvailabilityFilterStore', () => {
  const initFilterState: HotelAvailabilityFilterState = {
    hotelChain: null,
    hotelCategories: [],
    rateTypes: [],
    dateRange: {
      from: null,
      to: null,
    },
  };

  const applyFilters = ref<boolean>(false);

  const filterState = reactive<HotelAvailabilityFilterState>(structuredClone(initFilterState));

  const resetFilterState = () => {
    Object.assign(filterState, structuredClone(initFilterState));
  };

  const setApplyFilters = (value: boolean) => {
    applyFilters.value = value;
  };

  const resetApplyFilters = () => {
    setApplyFilters(false);
  };

  return {
    filterState,
    applyFilters,
    resetFilterState,
    setApplyFilters,
    resetApplyFilters,
  };
});
