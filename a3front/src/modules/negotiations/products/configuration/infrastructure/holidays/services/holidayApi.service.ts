import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { LoadExternalHolidaysParams } from '@/modules/negotiations/products/configuration/domain/holidays/types/loadExternalHolidaysParams.types';
import type { HolidaysDTO } from '../dto/holidays.dto';

export const loadExternalHolidaysService = async (
  params: LoadExternalHolidaysParams
): Promise<ApiResponse<HolidaysDTO>> => {
  const response = await productApi.get(
    `rate-plans/calendar/holidays/${params.country}/${params.city}`,
    {
      params: {
        date_from: params.dateFrom,
        date_to: params.dateTo,
        ratePlanId: params.ratePlanId,
      },
    }
  );

  return response.data;
};
