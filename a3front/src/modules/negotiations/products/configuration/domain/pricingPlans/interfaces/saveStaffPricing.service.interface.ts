import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

export interface SaveStaffPricingService {
  (ratePlanId: string, payload: any): Promise<ApiResponse<any>>;
}
