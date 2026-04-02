import type {
  AutoBrand,
  TypeUnitTransport,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

interface Data {
  auto_brands: AutoBrand[];
  type_units: TypeUnitTransport[];
}

export interface TransportVehicleResourceResponse {
  success: boolean;
  data: Data;
  code: number;
}
