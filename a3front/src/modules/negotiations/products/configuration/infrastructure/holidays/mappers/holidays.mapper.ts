import type { HolidaysApiResponseDTO } from '../dto/holidays.dto';
import type { HolidaysResponse } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidaysResponse.model';

export function mapHolidaysResponse(response: HolidaysApiResponseDTO): HolidaysResponse {
  const data = response.data.data;

  return {
    country: data.country,
    holidays: data.holidays.map((h: any) => ({
      type: h.type,
      country: h.country,
      city: h.city,
      dates: h.dates,
    })),
  };
}
