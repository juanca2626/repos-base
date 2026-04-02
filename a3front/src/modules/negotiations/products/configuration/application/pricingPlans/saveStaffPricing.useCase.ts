import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import { resolveSaveStaffPricingStrategy } from './resolvers/saveStaffPricingResolver';
import type { SaveStaffPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveStaffPricingRequest.types';

export async function saveStaffPricingUseCase(
  serviceType: ServiceType,
  ratePlanId: string,
  payload: SaveStaffPricingRequest
): Promise<any> {
  const strategy = resolveSaveStaffPricingStrategy(serviceType);

  const response = await strategy.execute(ratePlanId, payload);

  return response;
}
