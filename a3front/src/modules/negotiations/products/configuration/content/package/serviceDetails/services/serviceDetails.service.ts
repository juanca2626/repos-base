import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { ServiceDetailsRequest } from '../interfaces/service-details-request.interface';

export const saveServiceDetails = async (
  productSupplierId: string,
  request: ServiceDetailsRequest
): Promise<ServiceDetailsResponse> => {
  try {
    const endpoint = `product-suppliers/package/${productSupplierId}/service-details`;
    const response = await productApi.post(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      handleError(response.data?.error || 'Ocurrió un error en el proceso');
      throw new Error(response.data?.error || 'Ocurrió un error en el proceso');
    }
  } catch (error: any) {
    console.error('Error al guardar los detalles del servicio:', error);
    handleError(error);
    throw error;
  }
};

export const updateServiceDetails = async (
  productSupplierId: string,
  request: ServiceDetailsRequest
): Promise<ServiceDetailsResponse> => {
  try {
    const endpoint = `product-suppliers/package/${productSupplierId}/service-details/${request.id}`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      handleError(response.data?.error || 'Ocurrió un error en el proceso');
      throw new Error(response.data?.error || 'Ocurrió un error en el proceso');
    }
  } catch (error: any) {
    console.error('Error al guardar los detalles del servicio:', error);
    handleError(error);
    throw error;
  }
};
