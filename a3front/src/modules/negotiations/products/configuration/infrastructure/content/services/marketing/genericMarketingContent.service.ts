import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { GenericMarketingDto } from '../../dtos/marketing/genericMarketing.dto';

export const fetchGenericMarketingContent = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<ApiResponse<GenericMarketingDto>> => {
  const response = await productApi.get(
    `product-suppliers/generic/marketing/${productSupplierId}/service-details/${serviceDetailId}/content`
  );
  return response.data;
};
