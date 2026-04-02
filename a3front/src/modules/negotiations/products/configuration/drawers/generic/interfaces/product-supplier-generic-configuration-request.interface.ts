import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';
import type { ProductSupplierBehaviorSetting } from './product-supplier-behavior-setting.interface';

export interface ProductSupplierGenericConfigurationRequest {
  operatingLocations: ProductSupplierOperatingLocation[];
  supplierCategoryCodes: string[];
  behaviorSetting: ProductSupplierBehaviorSetting;
}
