import { defineStore } from 'pinia';
import type { FiltersInputsInterface } from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';

export const useTypeUnitFilterStore = defineStore('typeUnitFilterStore', {
  state: () =>
    ({
      codeOrName: null,
      status: null,
    }) as FiltersInputsInterface,
  actions: {
    setCodeOrName(value: string | null) {
      this.codeOrName = value;
    },
    setStatus(value: number | null) {
      this.status = value;
    },
    resetFilters() {
      this.codeOrName = null;
      this.status = null;
    },
  },
});
