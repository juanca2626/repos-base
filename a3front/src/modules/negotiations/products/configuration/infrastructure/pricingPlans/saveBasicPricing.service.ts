import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SaveBasicPricingService } from '@/modules/negotiations/products/configuration/domain/pricingPlans/interfaces/saveBasicPricing.service.interface';
import type { SaveBasicPricingDto } from './dtos/saveBasicPricing.dto';

export const saveBasicPricingService: SaveBasicPricingService = async (
  payload: SaveBasicPricingDto
): Promise<ApiResponse<any>> => {
  const response = await productApi.post(`rate-plans`, payload);
  return response.data;
};
