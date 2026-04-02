import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { GenericLoadingDto } from '../../dtos/loading/genericLoading.dto';

export const fetchGenericLoadingContent = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<ApiResponse<GenericLoadingDto>> => {
  const response = await productApi.get(
    `product-suppliers/generic/${productSupplierId}/service-details/${serviceDetailId}/content`
  );
  return response.data;
};
