import { getRateVariations } from '../infrastructure/services/rateVariation.service';
import { mapRateVariation } from './mappers/rateVariationMapper';

export const getRateVariationsUseCase = async (ratePlanId: string) => {
  const rateVariations = await getRateVariations(ratePlanId);
  const mappedRateVariations = rateVariations.data.map(mapRateVariation);
  return mappedRateVariations;
};
