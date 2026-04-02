import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { CreateConfigurationRequest } from '../interfaces';

async function createConfiguration(
  productSupplierId: string,
  request: CreateConfigurationRequest
): Promise<ApiResponse<void>> {
  const response = await productApi.post(
    `product-suppliers/generic/${productSupplierId}/configurations`,
    request
  );
  return response.data;
}

export const createConfigurationService = {
  createConfiguration,
};
