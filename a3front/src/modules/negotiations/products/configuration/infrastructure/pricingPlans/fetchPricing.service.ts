import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { RatePlanResponseDto } from './dtos/ratePlanResponse.dto';

export const fetchPricingService = async (
  productSupplierId: string,
  serviceDetailId: string,
  ratePlanId: string | null
): Promise<ApiResponse<RatePlanResponseDto>> => {
  const response = await productApi.get(`rate-plans`, {
    params: {
      productSupplierId,
      serviceDetailId,
      ratePlanId,
    },
  });
  return response.data;
};
