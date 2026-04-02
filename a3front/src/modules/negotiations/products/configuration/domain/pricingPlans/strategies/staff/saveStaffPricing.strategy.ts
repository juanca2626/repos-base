import type { SaveStaffPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveStaffPricingRequest.types';

export interface SaveStaffPricingStrategy {
  execute(ratePlanId: string, payload: SaveStaffPricingRequest): Promise<any>;
}
