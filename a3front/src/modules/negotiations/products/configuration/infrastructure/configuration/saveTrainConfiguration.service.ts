import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SaveConfigurationService } from '@/modules/negotiations/products/configuration/domain/configuration/interfaces/saveConfiguration.service.interface';
import type { SaveConfigurationRequest } from './dtos/saveConfiguration.dto';
import type { BackendConfigurationResponse } from '../../domain/configuration/types/configuration.backend.types';

export const saveTrainConfigurationService: SaveConfigurationService = async (
  productSupplierId: string,
  request: SaveConfigurationRequest
): Promise<ApiResponse<BackendConfigurationResponse>> => {
  const response = await productApi.post(
    `product-suppliers/train/${productSupplierId}/configurations`,
    request
  );
  return response.data;
};
