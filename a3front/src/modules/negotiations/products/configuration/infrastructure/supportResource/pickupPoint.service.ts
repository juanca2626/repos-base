import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { PickupPoint } from './dtos/pickupPoint.interface';

export const pickupPointService = async (types: string[]): Promise<ApiResponse<PickupPoint[]>> => {
  const response = await productApi.get('pickup-points/options', {
    params: {
      type: types.join(','),
    },
  });

  return response.data;
};
