import type { ServiceType } from '@/modules/negotiations/products/configuration/types';

import type { SaveConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/saveConfiguration.strategy';

import { SaveGenericConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/saveGenericConfiguration.strategy';
import { SaveTrainConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/saveTrainConfiguration.strategy';
import { SavePackageConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/savePackageConfiguration.strategy';

const strategies: Record<ServiceType, SaveConfigurationStrategy> = {
  GENERIC: new SaveGenericConfigurationStrategy(),
  TRAIN: new SaveTrainConfigurationStrategy(),
  PACKAGE: new SavePackageConfigurationStrategy(),
};

export const resolveSaveConfigurationStrategy = (
  serviceType: ServiceType
): SaveConfigurationStrategy => {
  const strategy = strategies[serviceType];

  if (!strategy) {
    throw new Error(`Save strategy not found for ${serviceType}`);
  }

  return strategy;
};
