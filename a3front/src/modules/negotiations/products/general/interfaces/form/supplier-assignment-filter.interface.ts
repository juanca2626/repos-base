import type { Location } from '@/modules/negotiations/products/general/interfaces/resources';

export interface SupplierQueryParams {
  searchTerm?: string;
  classificationId?: number;
  countryId?: number;
  stateId?: number;
  cityId?: number;
}

export interface SupplierAssignmentFilter {
  locationKey: string | null;
  searchTerm: string | null;
  supplierClassificationId: number | null;
}

export interface LocationOption extends Location {
  label: string;
  value: string;
}
