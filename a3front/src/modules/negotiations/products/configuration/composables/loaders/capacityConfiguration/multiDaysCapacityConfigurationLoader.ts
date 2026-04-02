import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';
import type { CapacityConfigurationLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/capacityConfigurationLoader.interface';
import { fetchCapacityConfigurations } from '@/modules/negotiations/products/configuration/content/shared/services/configuration/capacityConfiguration.service';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';

export class MultiDaysCapacityConfigurationLoader implements CapacityConfigurationLoader {
  private multiDaysStore = usePackageConfigurationStore();

  async loadCapacityConfiguration(
    productSupplierId: string
  ): Promise<CapacityConfigurationResponse[] | null> {
    const response = await fetchCapacityConfigurations(productSupplierId);

    if (response && Array.isArray(response)) {
      this.multiDaysStore.setCapacityConfigurations(response);
      return response;
    }
    console.log('No response from fetchCapacityConfigurations', productSupplierId);
    return null;
  }
}
