import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import type { PriceState } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/price.types';

export function createInitialPriceState(_serviceType: ServiceType | null = null): PriceState {
  return {
    isLoadingRateVariations: false,
    rateVariations: [],
    selectedRateVariationId: null,
    catalogs: {
      frequencies: [],
    },
  };
}
