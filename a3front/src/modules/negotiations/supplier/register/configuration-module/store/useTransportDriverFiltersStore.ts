import { defineStore } from 'pinia';
import type { DriverFiltersInputsInterface } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useTransportDriverFiltersStore = defineStore('transportDriverFiltersStore', {
  state: () =>
    ({
      name: null,
      surnames: null,
      documentStatus: null,
    }) as DriverFiltersInputsInterface,
  actions: {
    setName(value: string | null) {
      this.name = value;
    },
    setSurnames(value: string | null) {
      this.surnames = value;
    },
    setDocumentStatus(documentStatus: number | null) {
      this.documentStatus = documentStatus;
    },
    resetFilters() {
      this.name = null;
      this.surnames = null;
      this.documentStatus = null;
    },
  },
});
