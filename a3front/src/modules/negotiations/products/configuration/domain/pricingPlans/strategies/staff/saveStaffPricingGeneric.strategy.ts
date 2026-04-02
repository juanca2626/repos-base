import type { SaveStaffPricingStrategy } from './saveStaffPricing.strategy';
import type { SaveStaffPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveStaffPricingRequest.types';
import { saveStaffPricingService } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/saveStaffPricing.service';
import { mapToSaveStaffPricingDto } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/mappers/saveStaffPricing.mapper';

export class SaveStaffPricingGenericStrategy implements SaveStaffPricingStrategy {
  async execute(ratePlanId: string, payload: SaveStaffPricingRequest): Promise<any> {
    const dto = mapToSaveStaffPricingDto(payload);
    return await saveStaffPricingService(ratePlanId, dto);
  }
}
