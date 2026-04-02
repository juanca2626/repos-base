import { serviceDetailsService } from '@/modules/negotiations/products/configuration/services/general/api/serviceDetailsService';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { ServiceDetailsLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/serviceDetailsLoader.interface';

export class GeneralServiceDetailsLoader implements ServiceDetailsLoader {
  private genericStore = useGenericConfigurationStore();

  async loadServiceDetails(productSupplierId: string): Promise<ServiceDetailsResponse[] | null> {
    const response = await serviceDetailsService.fetchServiceDetails(productSupplierId);

    if (response.success && response.data) {
      this.genericStore.setServiceDetails(response.data);

      return response.data;
    }

    return null;
  }
}
