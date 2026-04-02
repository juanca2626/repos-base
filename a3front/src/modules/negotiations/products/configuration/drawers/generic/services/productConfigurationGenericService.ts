import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { ProductSupplierGenericConfigurationRequest } from '@/modules/negotiations/products/configuration/interfaces';
import type { ConfigurationResponse } from '../interfaces/configuration-payload.interface';

async function fetchProductSupplierById(
  productSupplierId: string
): Promise<ApiResponse<ConfigurationResponse[]>> {
  const response = await productApi.get(
    `product-suppliers/generic/${productSupplierId}/configurations`
  );
  return response.data;
}

async function updateProductSupplierGenericConfiguration(
  productSupplierId: string,
  configuration: ProductSupplierGenericConfigurationRequest
): Promise<ApiResponse<ConfigurationResponse[]>> {
  const response = await productApi.patch(
    `product-suppliers/generic/configurations/${productSupplierId}/behavior`,
    configuration
  );
  return response.data;
}

export const productConfigurationGenericService = {
  fetchProductSupplierById,
  updateProductSupplierGenericConfiguration,
};
