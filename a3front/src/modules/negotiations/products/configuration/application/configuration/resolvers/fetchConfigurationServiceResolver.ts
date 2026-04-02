import type { ServiceType } from '../../../types';
import type { FetchConfigurationService } from '@/modules/negotiations/products/configuration/domain/configuration/interfaces/fetchConfiguration.service.interface';

import { fetchGenericConfiguration } from '@/modules/negotiations/products/configuration/infrastructure/configuration/genericConfiguration.service';
import { fetchTrainConfiguration } from '@/modules/negotiations/products/configuration/infrastructure/configuration/trainConfiguration.service';
import { fetchPackageConfiguration } from '@/modules/negotiations/products/configuration/infrastructure/configuration/packageConfiguration.service';

const serviceMap: Record<ServiceType, FetchConfigurationService> = {
  GENERIC: fetchGenericConfiguration,
  TRAIN: fetchTrainConfiguration,
  PACKAGE: fetchPackageConfiguration,
};

export function resolveConfigurationService(serviceType: ServiceType): FetchConfigurationService {
  const service = serviceMap[serviceType];

  if (!service) {
    throw new Error(`Configuration service not found for service type: ${serviceType}`);
  }

  return service;
}
