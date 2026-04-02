import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SaveStaffPricingService } from '@/modules/negotiations/products/configuration/domain/pricingPlans/interfaces/saveStaffPricing.service.interface';
import type { SaveStaffPricingDto } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/dtos/saveStaffPricing.dto';

export const saveStaffPricingService: SaveStaffPricingService = async (
  ratePlanId: string,
  payload: SaveStaffPricingDto
): Promise<ApiResponse<any>> => {
  const response = await productApi.patch(`rate-plans/${ratePlanId}/staff-and-taxes`, payload);
  return response.data;
};
