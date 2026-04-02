import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

export interface SaveBasicPricingService {
  (
    //   payload: SaveBasicPricingRequest
    payload: any
  ): Promise<ApiResponse<any>>;
}
