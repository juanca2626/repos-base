import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { FilterState } from '@/modules/negotiations/suppliers/lodges/interfaces';

export const useLodgeFilterStore = defineStore('lodgeFilterStore', () => {
  const initFilterState: FilterState = {
    searchTerm: '',
    searchChain: '',
    chainIds: [],
    searchCountryState: '',
    countryStateKeys: [],
    statuses: [],
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
