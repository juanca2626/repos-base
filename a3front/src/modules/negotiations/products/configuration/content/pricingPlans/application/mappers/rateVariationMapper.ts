import type { RateVariation } from '../../domain/models/RateVariation';
import { normalizePricing } from '../helpers/normalizePricing';
import type { RateVariationResponseDto } from '../../infrastructure/dtos/rateVariationResponseDto';

export const mapRateVariation = (variation: RateVariationResponseDto): RateVariation => ({
  id: variation.id,
  ratePlanId: variation.ratePlanId,
  type: variation.type,
  priority: variation.priority,
  label: variation.label,
  dateDisplay: variation.dateDisplay,
  refGroupId: variation.refGroupId,
  frequencies: variation.frequencies.map((frequency: any) => frequency.frequencyId),
  typeMode: variation.typeMode ?? 'UNIQUE',
  includeTaxes: variation.includeTaxes,
  pricing: normalizePricing(variation.pricing),
  pricingStatus: variation.pricingStatus,
  status: variation.status,
  parentVariationId: variation.parentVariationId,
});
