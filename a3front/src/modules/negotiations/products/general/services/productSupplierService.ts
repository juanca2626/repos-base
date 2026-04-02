import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  ProductSupplierResponse,
  ProductSupplierBatchRequest,
} from '@/modules/negotiations/products/general/interfaces/form';

async function syncBatchProductSuppliers(
  attributes: ProductSupplierBatchRequest
): Promise<ApiResponse<ProductSupplierResponse[]>> {
  const response = await productApi.put(`product-suppliers/batch`, attributes);
  return response.data;
}

async function fetchProductSuppliersByProduct(
  productId: string
): Promise<ApiResponse<ProductSupplierResponse[]>> {
  const response = await productApi.get(`product-suppliers/by-product/${productId}`);
  return response.data;
}

export const productSupplierService = {
  syncBatchProductSuppliers,
  fetchProductSuppliersByProduct,
};
