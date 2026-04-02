import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

async function fetchServiceDetails(
  productSupplierId: string
): Promise<ApiResponse<ServiceDetailsResponse[]>> {
  const response = await productApi.get(
    `product-suppliers/generic/${productSupplierId}/service-details`
  );
  return response.data;
}

export const serviceDetailsService = {
  fetchServiceDetails,
};
