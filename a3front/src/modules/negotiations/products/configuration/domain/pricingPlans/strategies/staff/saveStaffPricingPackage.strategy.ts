import type { SaveStaffPricingStrategy } from './saveStaffPricing.strategy';
import type { SaveStaffPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveStaffPricingRequest.types';
import { mapToSaveStaffPricingDto } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/mappers/saveStaffPricing.mapper';
import { saveStaffPricingService } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/saveStaffPricing.service';

export class SaveStaffPricingPackageStrategy implements SaveStaffPricingStrategy {
  async execute(ratePlanId: string, payload: SaveStaffPricingRequest): Promise<any> {
    const dto = mapToSaveStaffPricingDto(payload);
    return await saveStaffPricingService(ratePlanId, dto);
  }
}
