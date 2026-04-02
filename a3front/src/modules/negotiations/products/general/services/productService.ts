import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type {
  ProductQueryParams,
  ProductResponse,
} from '@/modules/negotiations/products/general/interfaces/list';
import type { ApiListResponse, ApiResponse } from '@/modules/negotiations/products/interfaces';
import type {
  ProductFormRequest,
  ProductFormResponse,
} from '@/modules/negotiations/products/general/interfaces/form';

const baseResourceUrl = 'products';

async function fetchProducts(
  params: ProductQueryParams
): Promise<ApiListResponse<ProductResponse[]>> {
  const { page, pageSize, searchTerm } = params;

  const response = await productApi.get(`${baseResourceUrl}`, {
    params: {
      page,
      limit: pageSize,
      searchTerm: searchTerm || undefined,
    },
  });

  return response.data;
}

async function showProduct(id: string): Promise<ApiResponse<ProductFormResponse>> {
  const response = await productApi.get(`${baseResourceUrl}/${id}`);

  return response.data;
}

async function storeProduct(attributes: ProductFormRequest): Promise<any> {
  const response = await productApi.post(`${baseResourceUrl}`, attributes);

  return response.data;
}

async function updateProduct(id: string, attributes: ProductFormRequest): Promise<any> {
  const response = await productApi.patch(`${baseResourceUrl}/${id}`, attributes);

  return response.data;
}

async function destroyProduct(id: string): Promise<any> {
  const response = await productApi.delete(`${baseResourceUrl}/${id}`);

  return response.data;
}

async function checkCodeAvailability(code: string): Promise<ApiResponse<{ available: boolean }>> {
  const response = await productApi.get(`${baseResourceUrl}/code/available`, {
    params: {
      value: code,
    },
  });

  return response.data;
}

async function checkNameAvailability(name: string): Promise<ApiResponse<{ available: boolean }>> {
  const response = await productApi.get(`${baseResourceUrl}/name/available`, {
    params: {
      value: name,
    },
  });

  return response.data;
}

export const productService = {
  fetchProducts,
  showProduct,
  storeProduct,
  updateProduct,
  destroyProduct,
  checkCodeAvailability,
  checkNameAvailability,
};
