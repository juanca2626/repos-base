import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { MultiDaysServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

async function fetchServiceDetails(
  productSupplierId: string
): Promise<ApiResponse<MultiDaysServiceDetailsResponse[]>> {
  const response = await productApi.get(
    `product-suppliers/package/${productSupplierId}/service-details`
  );
  return response.data;
}

export const multiDaysServiceDetailsService = {
  fetchServiceDetails,
};
