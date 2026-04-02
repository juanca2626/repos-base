import type {
  RateVariationType,
  TypeMode,
  PricingStatus,
  VariationProcessStatus,
} from '../../domain/types/rateVariation.types';

export interface FrequencyResponseDto {
  frequencyId: string;
  code: string;
  fareType: string;
}

export interface RateVariationResponseDto {
  id: string;
  ratePlanId: string;
  type: RateVariationType;
  priority: number;
  label: string;
  dateDisplay: string;
  refGroupId: string | null;

  frequencies: FrequencyResponseDto[];
  typeMode: TypeMode;
  includeTaxes: boolean;

  pricing: any;
  pricingStatus: PricingStatus;
  status: VariationProcessStatus;
  updatedAt?: string;

  parentVariationId: string | null;
}
