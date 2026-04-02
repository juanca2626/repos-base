import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { RateVariationResponseDto } from '../dtos/rateVariationResponseDto';

export const getRateVariations = async (
  ratePlanId: string
): Promise<ApiResponse<RateVariationResponseDto[]>> => {
  const response = await productApi.get(`rate-plans/${ratePlanId}/variations`);
  return response.data;
};
