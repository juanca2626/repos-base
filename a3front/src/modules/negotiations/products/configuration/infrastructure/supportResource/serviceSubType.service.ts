import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ServiceSubType } from './dtos/serviceSubType.interface';

export const serviceSubTypeService = async (
  serviceTypeId: string
): Promise<ApiResponse<ServiceSubType[]>> => {
  const response = await productApi.get(`service-types/${serviceTypeId}/sub-types`);
  return response.data;
};
