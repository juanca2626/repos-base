import type { LoadExternalHolidaysParams } from '@/modules/negotiations/products/configuration/domain/holidays/types/loadExternalHolidaysParams.types';
import { loadExternalHolidaysService } from '@/modules/negotiations/products/configuration/infrastructure/holidays/services/holidayApi.service';
import { mapExternalHolidaysToState } from './mappers/mapExternalHolidaysToState';
import type { HolidaysResponse } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidaysResponse.model';

export async function loadExternalHolidaysUseCase(
  params: LoadExternalHolidaysParams
): Promise<HolidaysResponse> {
  const response = await loadExternalHolidaysService(params);

  const dto = response?.data;
  const holidays = mapExternalHolidaysToState(dto);

  return holidays;
}
