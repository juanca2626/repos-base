import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SaveRateVariationRequestDto } from '../dtos/saveRateVariation.dto';

export const saveRateVariation = async (
  rateVariationId: string,
  payload: SaveRateVariationRequestDto
): Promise<any> => {
  const response = await productApi.patch(`rate-variations/${rateVariationId}`, payload);
  return response.data;
};
