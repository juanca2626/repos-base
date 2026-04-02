import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type { SaveBasicPricingStrategy } from '../../../domain/pricingPlans/strategies/saveBasicPricing.strategy';

import { SaveBasicPricingGenericStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/saveBasicPricingGeneric.strategy';
import { SaveBasicPricingTrainStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/saveBasicPricingTrain.strategy';
import { SaveBasicPricingPackageStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/saveBasicPricingPackage.strategy';

const strategies: Record<ServiceType, SaveBasicPricingStrategy> = {
  GENERIC: new SaveBasicPricingGenericStrategy(),
  TRAIN: new SaveBasicPricingTrainStrategy(),
  PACKAGE: new SaveBasicPricingPackageStrategy(),
};

export const resolveSaveBasicPricingStrategy = (
  serviceType: ServiceType
): SaveBasicPricingStrategy => {
  const strategy = strategies[serviceType];

  if (!strategy) {
    throw new Error(`Save basic pricing strategy not found for ${serviceType}`);
  }

  return strategy;
};
