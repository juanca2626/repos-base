import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';

import { resolveSaveConfigurationStrategy } from '@/modules/negotiations/products/configuration/application/configuration/resolvers/saveConfigurationServiceResolver';
import { resolveSaveResponseMapper } from '@/modules/negotiations/products/configuration/application/configuration/resolvers/saveConfigurationResponseMapperResolver';

export interface SaveConfigurationParams {
  serviceType: ServiceType;
  productSupplierId: string;
  configuration: Configuration;
}

export const saveConfigurationUseCase = async ({
  serviceType,
  productSupplierId,
  configuration,
}: SaveConfigurationParams): Promise<any> => {
  const strategy = resolveSaveConfigurationStrategy(serviceType);

  const response = await strategy.execute(productSupplierId, configuration);

  const mapper = resolveSaveResponseMapper(serviceType);

  return mapper.map(response ?? []);
};
