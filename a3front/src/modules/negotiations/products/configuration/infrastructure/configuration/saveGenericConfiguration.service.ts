import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SaveConfigurationService } from '@/modules/negotiations/products/configuration/domain/configuration/interfaces/saveConfiguration.service.interface';
import type { SaveConfigurationRequest } from './dtos/saveConfiguration.dto';
import type { BackendConfigurationResponse } from '../../domain/configuration/types/configuration.backend.types';

export const saveGenericConfigurationService: SaveConfigurationService = async (
  productSupplierId: string,
  payload: SaveConfigurationRequest
): Promise<ApiResponse<BackendConfigurationResponse>> => {
  const response = await productApi.post(
    `product-suppliers/generic/${productSupplierId}/configurations`,
    payload
  );
  return response.data;
};
