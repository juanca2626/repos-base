import type { HolidayDate } from './holidayDate.model';
import { HolidayScopeEnum } from '../enums/holidayScope.enum';
export interface HolidayGroup {
  id?: string | null;
  uuid: string;
  key: HolidayScopeEnum;
  label: string;
  priority: number;
  isActive: boolean;
  dates: HolidayDate[];
}
