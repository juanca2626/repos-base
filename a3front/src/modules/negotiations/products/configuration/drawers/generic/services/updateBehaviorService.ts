import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

export interface UpdateBehaviorRequest {
  applyGeneralBehavior: boolean;
  behaviorSettings: Array<{
    supplierCategoryCode: string;
    mode: string;
  }>;
}

export interface UpdateBehaviorResponse {
  applyGeneralBehavior: boolean;
  behaviorSettings: Array<{
    supplierCategoryCode: string;
    mode: string;
  }>;
}

async function updateBehavior(
  behaviorId: string,
  request: UpdateBehaviorRequest
): Promise<ApiResponse<UpdateBehaviorResponse>> {
  const response = await productApi.patch(
    `product-suppliers/generic/configurations/${behaviorId}/behavior`,
    request
  );
  return response.data;
}

export const updateBehaviorService = {
  updateBehavior,
};
