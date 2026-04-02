import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type { FilterState } from '@/modules/negotiations/suppliers/miscellaneous/interfaces';

export const useMiscellaneousFilterStore = defineStore('miscellaneousFilterStore', () => {
  const initFilterState: FilterState = {
    searchTerm: '',
    searchChain: '',
    chains: [],
    searchCountryState: '',
    countryStateKeys: [],
    searchItemType: '',
    itemTypes: [],
    searchCategory: '',
    categories: [],
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
