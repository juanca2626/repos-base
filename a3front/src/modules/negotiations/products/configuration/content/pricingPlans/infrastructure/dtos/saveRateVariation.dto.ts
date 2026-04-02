import type { PricingStatus } from '../../domain/types/rateVariation.types';

export interface FrequencyRequestDto {
  frequencyId: string;
  code: string;
  fareType: string;
}

export interface SaveRateVariationRequestDto {
  pricingStatus: PricingStatus;
  frequencies: FrequencyRequestDto[];
}
