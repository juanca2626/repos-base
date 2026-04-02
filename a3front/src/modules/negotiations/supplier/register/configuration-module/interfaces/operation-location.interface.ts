import type { Location } from '@/modules/negotiations/supplier/interfaces';

export interface OperationLocationResponse {
  supplier_branch_office_id: number;
  country: Location;
  state: Location;
  city?: Location | null;
  zone?: Location | null;
}

export interface OperationLocationData {
  supplier_branch_office_id?: number;
  ids: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  display_name: string;
}

export interface OperationLocationProps {
  data: OperationLocationData[];
}

export interface OperationLocationEmit {
  (event: 'handleTabClick', item: OperationLocationData): void;
}
