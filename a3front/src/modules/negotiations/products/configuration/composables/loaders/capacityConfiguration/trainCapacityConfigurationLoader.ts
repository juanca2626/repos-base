import { fetchCapacityConfigurations } from '@/modules/negotiations/products/configuration/content/shared/services/configuration/capacityConfiguration.service';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';
import type { CapacityConfigurationLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/capacityConfigurationLoader.interface';

export class TrainCapacityConfigurationLoader implements CapacityConfigurationLoader {
  private trainStore = useTrainConfigurationStore();

  async loadCapacityConfiguration(
    productSupplierId: string
  ): Promise<CapacityConfigurationResponse[] | null> {
    // TODO: Implementar si es necesario para Train
    // Por ahora, usar el mismo servicio que General
    const response = await fetchCapacityConfigurations(productSupplierId);

    if (response && Array.isArray(response)) {
      this.trainStore.setCapacityConfigurations(response);
      return response;
    }

    return null;
  }
}
