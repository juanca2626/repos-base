import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { FilterState } from '@/modules/negotiations/suppliers/local-operators/interfaces';

export const useLocalOperatorFilterStore = defineStore('localOperatorFilterStore', () => {
  const initFilterState: FilterState = {
    searchTerm: '',
    searchChain: '',
    chains: [],
    searchCountryState: '',
    countryStateKeys: [],
    searchTypeFood: '',
    searchAmenity: '',
    status: [],
  };

  const applyFilters = ref<boolean>(false);

  const filterState = reactive<FilterState>(structuredClone(initFilterState));

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
