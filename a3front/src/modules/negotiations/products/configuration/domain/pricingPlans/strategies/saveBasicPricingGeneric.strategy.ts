import type { SaveBasicPricingStrategy } from './saveBasicPricing.strategy';
import { mapToSaveBasicPricingRequest } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/mappers/saveBasicPricing.mapper';
import { saveBasicPricingService } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/saveBasicPricing.service';
import type { SaveBasicPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveBasicPricingRequest.types';
export class SaveBasicPricingGenericStrategy implements SaveBasicPricingStrategy {
  async execute(
    productSupplierId: string,
    serviceDetailId: string,
    ratePlanId: string | null,
    data: SaveBasicPricingRequest
  ): Promise<any> {
    const payload = mapToSaveBasicPricingRequest(
      productSupplierId,
      serviceDetailId,
      ratePlanId,
      data
    );

    const response = await saveBasicPricingService(payload);

    return response?.data ?? [];
  }
}
