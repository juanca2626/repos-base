import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';

export interface CapacityConfigurationLoader {
  loadCapacityConfiguration(
    productSupplierId: string
  ): Promise<CapacityConfigurationResponse[] | null>;
}
