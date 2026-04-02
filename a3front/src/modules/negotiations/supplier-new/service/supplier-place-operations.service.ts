import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PlaceOperationResponse } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration/locations';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

const baseResourceUrl = 'supplier/locations';

async function indexSupplierPlaceOperations(
  supplierId: number
): Promise<ApiResponse<PlaceOperationResponse[]>> {
  const response = await supplierApi.get(`${baseResourceUrl}/place-operations/${supplierId}`);

  return response.data;
}

export const useSupplierPlaceOperationsService = {
  indexSupplierPlaceOperations,
};
