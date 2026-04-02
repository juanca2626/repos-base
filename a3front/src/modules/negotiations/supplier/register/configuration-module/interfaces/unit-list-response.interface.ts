import type { Location } from '@/modules/negotiations/supplier/interfaces';

interface TypeUnitTransport {
  id: number;
  code: string;
  name: string;
}

interface SupplierBranchOffice {
  id: number;
  country: Location;
  state: Location;
  city?: Location | null;
  zone?: Location | null;
}

export interface TransportUnitGroupResponse {
  type_unit_transport: TypeUnitTransport;
  supplier_branch_office: SupplierBranchOffice;
  number_of_vehicles: number;
  supplier_transport_vehicle_ids: string[];
}
