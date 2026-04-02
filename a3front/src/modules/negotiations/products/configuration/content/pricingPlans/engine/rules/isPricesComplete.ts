import type { PriceState } from '../../types/price.types';

export function isPricesComplete(priceState: PriceState) {
  return priceState.rateVariations.some((variation) => variation.status === 'COMPLETED');
}
