import type { RateVariation } from '../../domain/models/RateVariation';
import type {
  SaveRateVariationRequestDto,
  FrequencyRequestDto,
} from '../../infrastructure/dtos/saveRateVariation.dto';

export const mapRateVariationToRequestDto = (
  variation: RateVariation,
  catalogs: any | null
): SaveRateVariationRequestDto => {
  const catalogFrequencies = catalogs?.frequencies ?? [];

  const frequencies: FrequencyRequestDto[] = variation.frequencies.map((id: string) => {
    const found = catalogFrequencies.find((f: any) => f.id === id);

    return {
      frequencyId: id,
      code: found?.code ?? '',
      fareType: found?.fareType ?? '',
    };
  });

  return {
    pricingStatus: variation.pricingStatus,
    frequencies,
  };
};
