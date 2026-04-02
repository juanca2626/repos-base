import type { RateVariation } from '../domain/models/RateVariation';
import { saveRateVariation } from '../infrastructure/services/saveRateVariation.service';
import { mapRateVariationToRequestDto } from './mappers/mapRateVariationToRequestDto';

export const saveRateVariationsUseCase = async (
  rateVariation: RateVariation,
  catalogs: any | null
) => {
  const payload = mapRateVariationToRequestDto(rateVariation, catalogs);
  const response = await saveRateVariation(rateVariation.id, payload);
  return response.data;
};
