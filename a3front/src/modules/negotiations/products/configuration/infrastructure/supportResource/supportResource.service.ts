import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SupportResource } from './dtos/supportResource.interface';
import type { SupportResourceKey } from '../../types/index';

export const supportResourceService = async (
  keys: SupportResourceKey[]
): Promise<ApiResponse<SupportResource>> => {
  const response = await productApi.get(`supports/resources`, {
    params: { keys },
    paramsSerializer: {
      indexes: null,
    },
  });

  return response.data;
};
