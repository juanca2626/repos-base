import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type { SaveStaffPricingStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/staff/saveStaffPricing.strategy';

import { SaveStaffPricingGenericStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/staff/saveStaffPricingGeneric.strategy';
import { SaveStaffPricingTrainStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/staff/saveStaffPricingTrain.strategy';
import { SaveStaffPricingPackageStrategy } from '@/modules/negotiations/products/configuration/domain/pricingPlans/strategies/staff/saveStaffPricingPackage.strategy';

const strategies: Record<ServiceType, SaveStaffPricingStrategy> = {
  GENERIC: new SaveStaffPricingGenericStrategy(),
  TRAIN: new SaveStaffPricingTrainStrategy(),
  PACKAGE: new SaveStaffPricingPackageStrategy(),
};

export const resolveSaveStaffPricingStrategy = (
  serviceType: ServiceType
): SaveStaffPricingStrategy => {
  const strategy = strategies[serviceType];

  if (!strategy) {
    throw new Error(`Save staff pricing strategy not found for ${serviceType}`);
  }

  return strategy;
};
