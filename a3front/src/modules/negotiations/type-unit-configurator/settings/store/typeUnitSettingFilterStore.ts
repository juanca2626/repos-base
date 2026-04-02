import { defineStore } from 'pinia';
import type { FiltersInputsInterface } from '@/modules/negotiations/type-unit-configurator/settings/interfaces';
import { currentYear } from '@/modules/negotiations/type-unit-configurator/helpers/periodHelper';

export const useTypeUnitSettingFilterStore = defineStore('typeUnitSettingFilterStore', {
  state: () =>
    ({
      typeUnitTransportLocationId: null,
      periodYear: currentYear,
      transferId: null,
    }) as FiltersInputsInterface,
  actions: {
    setTypeUnitTransportLocationId(value: number | null) {
      this.typeUnitTransportLocationId = value;
    },
    setPeriodYear(value: number) {
      this.periodYear = value;
    },
    setTransferId(value: number | null) {
      this.transferId = value;
    },
    resetFilters() {
      this.typeUnitTransportLocationId = null;
      this.periodYear = currentYear;
      this.transferId = null;
    },
  },
});
