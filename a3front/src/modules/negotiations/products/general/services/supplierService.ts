import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierFormResponse,
  SupplierFormRequest,
  SupplierFormBatchRequest,
} from '@/modules/negotiations/products/general/interfaces/form';

async function upsertSupplier(
  attributes: SupplierFormRequest
): Promise<ApiResponse<SupplierFormResponse>> {
  const response = await productApi.post(`suppliers`, attributes);
  return response.data;
}

async function syncBatchSuppliers(
  attributes: SupplierFormBatchRequest
): Promise<ApiResponse<SupplierFormResponse[]>> {
  const response = await productApi.put(`suppliers/batch`, attributes);
  return response.data;
}

export const supplierService = {
  upsertSupplier,
  syncBatchSuppliers,
};
