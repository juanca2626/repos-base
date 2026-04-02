import { supplierApi, supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierClassification,
  Location,
} from '@/modules/negotiations/products/general/interfaces/resources';
import type {
  SupplierResponse,
  SupplierQueryParams,
} from '@/modules/negotiations/products/general/interfaces/form';
async function fetchSupplierClassifications(): Promise<ApiResponse<SupplierClassification[]>> {
  const response = await supportApi.get(`supplier-classifications/all`);
  return response.data;
}

async function fetchLocationsByCity(value: string): Promise<ApiResponse<Location[]>> {
  const response = await supplierApi.get(`locations/cities/search`, {
    params: {
      value,
    },
  });
  return response.data;
}

async function fetchSuppliers(
  params: SupplierQueryParams
): Promise<ApiResponse<SupplierResponse[]>> {
  const response = await supplierApi.get(`supplier/product-suppliers`, {
    params,
  });
  return response.data;
}

export const supplierAssignmentResourceService = {
  fetchSupplierClassifications,
  fetchLocationsByCity,
  fetchSuppliers,
};
