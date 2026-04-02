import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  ProductSupplierMultiDaysConfiguration,
  MultiDayConfigurationRequest,
  operationalSeasonCodeRequest,
} from '../interfaces';

async function fetchProductSupplierById(
  productSupplierId: string
): Promise<ApiResponse<ProductSupplierMultiDaysConfiguration[]>> {
  const response = await productApi.get(
    `product-suppliers/package/${productSupplierId}/configurations`
  );
  return response.data;
}

async function createMultiDayConfiguration(
  productSupplierId: string,
  request: MultiDayConfigurationRequest
): Promise<ApiResponse<void>> {
  const response = await productApi.post(
    `product-suppliers/package/${productSupplierId}/configurations`,
    request
  );
  return response.data;
}

async function updateMultiDayConfiguration(
  behaviorId: string,
  request: operationalSeasonCodeRequest
): Promise<ApiResponse<ProductSupplierMultiDaysConfiguration>> {
  const response = await productApi.patch(
    `product-suppliers/package/configurations/${behaviorId}/operational-seasons`,
    request
  );
  return response.data;
}

export const productConfigurationMultiDayService = {
  fetchProductSupplierById,
  createMultiDayConfiguration,
  updateMultiDayConfiguration,
};
