import { fetchCapacityConfigurations } from '@/modules/negotiations/products/configuration/content/shared/services/configuration/capacityConfiguration.service';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';
import type { CapacityConfigurationLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/capacityConfigurationLoader.interface';

export class GeneralCapacityConfigurationLoader implements CapacityConfigurationLoader {
  private genericStore = useGenericConfigurationStore();

  async loadCapacityConfiguration(
    productSupplierId: string
  ): Promise<CapacityConfigurationResponse[] | null> {
    const response = await fetchCapacityConfigurations(productSupplierId);

    if (response && Array.isArray(response)) {
      this.genericStore.setCapacityConfigurations(response);
      return response;
    }

    return null;
  }
}
