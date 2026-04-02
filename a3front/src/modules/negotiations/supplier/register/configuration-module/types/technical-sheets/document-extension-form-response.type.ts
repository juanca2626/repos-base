import type {
  DriverExtensionFormResponse,
  VehicleExtensionFormResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export type DocumentExtensionFormResponse =
  | VehicleExtensionFormResponse[]
  | DriverExtensionFormResponse[];
