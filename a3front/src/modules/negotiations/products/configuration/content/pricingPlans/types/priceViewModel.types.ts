import type { EnginePeriod } from './period.types';
import type { PeriodPrice } from './price.types';

export interface PricePeriodViewModel {
  id: string;
  name: string;
  type: EnginePeriod['type'];
  displayDate: string;
  price: PeriodPrice;
}
