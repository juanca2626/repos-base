import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { FetchConfigurationService } from '@/modules/negotiations/products/configuration/domain/configuration/interfaces/fetchConfiguration.service.interface';
import type { BackendConfigurationResponse } from '@/modules/negotiations/products/configuration/domain/configuration/types/configuration.backend.types';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

export const fetchTrainConfiguration: FetchConfigurationService = async (
  productSupplierId: string
): Promise<ApiResponse<BackendConfigurationResponse>> => {
  const response = await productApi.get(
    `product-suppliers/train/${productSupplierId}/configurations`
  );

  return response.data;
};
