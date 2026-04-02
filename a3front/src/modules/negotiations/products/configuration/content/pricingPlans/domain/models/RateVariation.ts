import type {
  RateVariationType,
  TypeMode,
  PricingStatus,
  VariationProcessStatus,
} from '../types/rateVariation.types';

export interface RateVariation {
  id: string;
  ratePlanId: string;
  type: RateVariationType;
  priority: number;
  label: string;
  dateDisplay: string;
  refGroupId: string | null;

  frequencies: string[];
  typeMode: TypeMode;
  includeTaxes: boolean;

  pricing: any;
  pricingStatus: PricingStatus;
  status: VariationProcessStatus;
  updatedAt?: string;
  parentVariationId: string | null;
}
