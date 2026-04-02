import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';

export interface ProductSupplierTrainConfigurationResponse {
  id: string;
  operatingLocation: ProductSupplierOperatingLocation;
  trainTypeCodes: string[];
}
