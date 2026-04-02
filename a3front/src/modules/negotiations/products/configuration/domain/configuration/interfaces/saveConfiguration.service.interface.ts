import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SaveConfigurationRequest } from '@/modules/negotiations/products/configuration/infrastructure/configuration/dtos/saveConfiguration.dto';
import type { BackendConfigurationResponse } from '@/modules/negotiations/products/configuration/domain/configuration/types/configuration.backend.types';

export interface SaveConfigurationService {
  (
    productSupplierId: string,
    payload: SaveConfigurationRequest
  ): Promise<ApiResponse<BackendConfigurationResponse>>;
}
