import type { ServiceDetailLoader } from '@/modules/negotiations/products/configuration/composables/factories/serviceDetails/interfaces';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';

export class GenericServiceDetailLoader implements ServiceDetailLoader {
  private genericStore = useGenericConfigurationStore();

  getServiceDetailBySectionKeyCode(key: string, code: string): ServiceDetailsResponse | null {
    return this.genericStore.getServiceDetail(key, code);
  }
}
