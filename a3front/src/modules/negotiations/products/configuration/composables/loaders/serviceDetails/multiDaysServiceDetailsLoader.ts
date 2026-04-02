import type { MultiDaysServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { ServiceDetailsLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/serviceDetailsLoader.interface';
import { multiDaysServiceDetailsService } from '@/modules/negotiations/products/configuration/services/multiDays/api/multiDaysServiceDetailsService';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
export class MultiDaysServiceDetailsLoader implements ServiceDetailsLoader {
  private multiDaysStore = usePackageConfigurationStore();

  async loadServiceDetails(
    productSupplierId: string
  ): Promise<MultiDaysServiceDetailsResponse[] | null> {
    const response = await multiDaysServiceDetailsService.fetchServiceDetails(productSupplierId);

    if (response.success && response.data) {
      this.multiDaysStore.setServiceDetails(response.data);
      return response.data;
    }
    // console.warn('MultiDays service details service not yet implemented', productSupplierId);
    return null;
  }
}
