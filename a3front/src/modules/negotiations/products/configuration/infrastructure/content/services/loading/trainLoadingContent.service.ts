import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { TrainLoadingDto } from '../../dtos/loading/trainLoading.dto';

export const fetchTrainLoadingContent = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<ApiResponse<TrainLoadingDto | null>> => {
  const response = await productApi.get(
    `product-suppliers/train/${productSupplierId}/service-details/${serviceDetailId}/content`
  );
  return response?.data ?? null;
};
