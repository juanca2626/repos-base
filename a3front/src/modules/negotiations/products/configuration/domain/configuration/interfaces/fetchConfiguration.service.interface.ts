import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { BackendConfigurationResponse } from '../types/configuration.backend.types';

export interface FetchConfigurationService {
  (productSupplierId: string): Promise<ApiResponse<BackendConfigurationResponse>>;
}
