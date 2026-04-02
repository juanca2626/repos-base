import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';

export interface TrainConfigurationPayload {
  operatingLocation: ProductSupplierOperatingLocation;
  trainTypeCodes: string[];
}

export interface TrainConfigurationRequest {
  configurations: TrainConfigurationPayload[];
}
