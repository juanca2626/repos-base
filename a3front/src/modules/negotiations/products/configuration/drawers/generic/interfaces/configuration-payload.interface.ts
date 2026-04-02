import type { ProductSupplierBehaviorMode } from '@/modules/negotiations/products/configuration/interfaces';
import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';

export interface BehaviorSettingPayload {
  supplierCategoryCode: string;
  mode: ProductSupplierBehaviorMode;
}

export interface ConfigurationPayload {
  operatingLocation: ProductSupplierOperatingLocation;
  applyGeneralBehavior: boolean;
  behaviorSettings: BehaviorSettingPayload[];
}

export interface ConfigurationResponse extends ConfigurationPayload {
  id: string;
}

export interface CreateConfigurationRequest {
  configurations: ConfigurationPayload[];
}
