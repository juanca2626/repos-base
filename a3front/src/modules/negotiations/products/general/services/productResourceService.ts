import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ServiceTypeListItem } from '@/modules/negotiations/products/general/interfaces/resources';

async function fetchServiceTypes(): Promise<ApiResponse<ServiceTypeListItem[]>> {
  const response = await productApi.get(`service-types`);
  return response.data;
}

export const productResourceService = {
  fetchServiceTypes,
};
