import type { HolidayCountry } from './holidayCountry.model';
import type { HolidayGroup } from './holiday.model';

export interface HolidaysResponse {
  country?: HolidayCountry;
  groups: HolidayGroup[];
}
