import type { EnginePeriod } from '../types/period.types';
import type { PeriodPrice } from '../types/price.types';
import type { PricePeriodViewModel } from '../types/priceViewModel.types';
import { formatPeriodDisplay } from '../utils/formatPeriodDisplay';

export function createPricePeriodsViewModel(
  enginePeriods: EnginePeriod[],
  pricePeriods: PeriodPrice[]
): PricePeriodViewModel[] {
  const priceMap = new Map(pricePeriods.map((p) => [p.periodId, p]));
  return enginePeriods.map((period) => ({
    id: period.id,
    name: period.name,
    type: period.type,
    displayDate: formatPeriodDisplay(period),
    price: priceMap.get(period.id)!,
  }));
}
