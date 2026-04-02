import { defineStore } from 'pinia';
import { SupplierStatus } from '@/modules/negotiations/supplier/land/tourist-transport/interfaces';

interface OperationLocation {
  country_id: number | null;
  state_id: number | null;
  city_id: number | null;
  zone_id: number | null;
}

export const useSearchTouristTransportFiltersStore = defineStore('searchFilters', {
  state: () => ({
    operationLocation: {
      country_id: null,
      state_id: null,
      city_id: null,
      zone_id: null,
    } as OperationLocation,
    type_unit_transport_id: [] as number[],
    status: null as SupplierStatus | null,
    name: '' as string,
  }),
  actions: {
    setOperationLocation(location: OperationLocation) {
      this.operationLocation = location;
    },
    setTypeUnitTransportId(ids: number[]) {
      this.type_unit_transport_id = ids;
    },
    setStatus(status: SupplierStatus | null) {
      this.status = status;
    },
    setName(name: string) {
      this.name = name;
    },
    resetFilters() {
      this.operationLocation = {
        country_id: null,
        state_id: null,
        city_id: null,
        zone_id: null,
      };
      this.type_unit_transport_id = [];
      this.status = null;
      this.name = '';
    },
  },
});
