import type { HolidaysResponse } from '../models/holidaysResponse.model';
import type { LoadExternalHolidaysParams } from '../types/loadExternalHolidaysParams.types';

export interface HolidayService {
  loadExternalHolidays(params: LoadExternalHolidaysParams): Promise<HolidaysResponse>;
}
