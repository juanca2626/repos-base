import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ProductSupplierTrainConfigurationResponse } from '../interfaces/product-supplier-train-configuration-response.interface';
import type { ProductSupplierTrainConfigurationRequest } from '../interfaces/product-supplier-train-configuration-request.interface';
import type { TrainConfigurationRequest } from '../interfaces';

async function fetchProductSupplierById(
  productSupplierId: string
): Promise<ApiResponse<ProductSupplierTrainConfigurationResponse[]>> {
  const response = await productApi.get(
    `product-suppliers/train/${productSupplierId}/configurations`
  );
  return response.data;
}

async function createTrainConfiguration(
  productSupplierId: string,
  request: TrainConfigurationRequest
): Promise<ApiResponse<void>> {
  const response = await productApi.post(
    `product-suppliers/train/${productSupplierId}/configurations`,
    request
  );
  return response.data;
}

async function updateProductSupplierTrainConfiguration(
  productSupplierId: string,
  configuration: ProductSupplierTrainConfigurationRequest
): Promise<ApiResponse<ProductSupplierTrainConfigurationResponse[]>> {
  const response = await productApi.patch(
    `product-suppliers/train/${productSupplierId}/configurations`,
    configuration
  );
  return response.data;
}

async function updateTrainTypes(
  configurationId: string,
  trainTypeCodes: string[]
): Promise<ApiResponse<ProductSupplierTrainConfigurationResponse>> {
  const response = await productApi.patch(
    `product-suppliers/train/configurations/${configurationId}/train-types`,
    { trainTypeCodes }
  );
  return response.data;
}

export const productConfigurationTrainService = {
  fetchProductSupplierById,
  createTrainConfiguration,
  updateProductSupplierTrainConfiguration,
  updateTrainTypes,
};
