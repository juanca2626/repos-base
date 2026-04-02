import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SupplierPlaceOperation } from '@/modules/negotiations/products/general/interfaces/resources';

async function fetchPlaceOperations(
  supplierId: number
): Promise<ApiResponse<SupplierPlaceOperation[]>> {
  const response = await supplierApi.get(`supplier/primary-place-operations/${supplierId}`);
  return response.data;
}

export const productConfigurationResourceService = {
  fetchPlaceOperations,
};
