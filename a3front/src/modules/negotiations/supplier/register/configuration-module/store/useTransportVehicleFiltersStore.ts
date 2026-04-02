import { defineStore } from 'pinia';
import type { VehicleFiltersInputsInterface } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useTransportVehicleFiltersStore = defineStore('transportVehicleFiltersStore', {
  state: () =>
    ({
      licensePlate: null,
      documentStatus: null,
      typeUnitTransportId: [],
    }) as VehicleFiltersInputsInterface,
  actions: {
    setLicensePlate(licensePlate: string | null) {
      this.licensePlate = licensePlate;
    },
    setDocumentStatus(documentStatus: number | null) {
      this.documentStatus = documentStatus;
    },
    setTypeUnitTransportId(typeUnitTransportId: number[]) {
      this.typeUnitTransportId = typeUnitTransportId;
    },
    resetFilters() {
      this.licensePlate = null;
      this.documentStatus = null;
      this.typeUnitTransportId = [];
    },
  },
});
