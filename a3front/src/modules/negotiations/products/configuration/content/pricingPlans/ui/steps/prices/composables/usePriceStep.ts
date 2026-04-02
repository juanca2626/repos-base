import { useFrequencyOptions } from '../../../../composables/useFrequencyOptions';
import type { PriceState } from '../../../../types/price.types';

export const usePriceStep = (pricingState: PriceState) => {
  const { options: frequencyOptions } = useFrequencyOptions(pricingState);

  return {
    frequencyOptions,
  };
};
