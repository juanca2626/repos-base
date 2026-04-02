import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { PackageLoadingDto } from '../../dtos/loading/packageLoading.dto';

export const fetchPackageMarketingContent = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<ApiResponse<PackageLoadingDto>> => {
  const response = await productApi.get(
    `product-suppliers/package/marketing/${productSupplierId}/service-details/${serviceDetailId}/content`
  );
  return response.data;
};
