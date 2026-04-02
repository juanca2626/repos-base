import type { ConfigurationResponse } from '@/modules/negotiations/products/configuration/drawers/generic/interfaces/configuration-payload.interface';
import type { ProductSupplierTrainConfigurationResponse } from '@/modules/negotiations/products/configuration/drawers/train/interfaces/product-supplier-train-configuration-response.interface';
import type { ProductSupplierMultiDaysConfiguration } from '@/modules/negotiations/products/configuration/drawers/package/interfaces/product-supplier-multi-days-configuration-response.interface';

export interface ConfigurationLoader {
  loadConfiguration(
    productSupplierId: string
  ): Promise<
    | ConfigurationResponse[]
    | ProductSupplierTrainConfigurationResponse[]
    | ProductSupplierMultiDaysConfiguration[]
    | null
  >;
}
