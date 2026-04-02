import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ProductSupplierSummaryData } from '@/modules/negotiations/products/configuration/interfaces/product-supplier-summary.interface';

export const summaryService = async (
  productSupplierId: string
): Promise<ApiResponse<ProductSupplierSummaryData>> => {
  const response = await productApi.get(`product-suppliers/${productSupplierId}/summary`);
  return response.data;
};
