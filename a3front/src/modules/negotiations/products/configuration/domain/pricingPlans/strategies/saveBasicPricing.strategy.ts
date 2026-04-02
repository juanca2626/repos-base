import type { SaveBasicPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveBasicPricingRequest.types';
export interface SaveBasicPricingStrategy {
  execute(
    productSupplierId: string,
    serviceDetailId: string,
    ratePlanId: string | null,
    payload: SaveBasicPricingRequest
  ): Promise<any>;
}
