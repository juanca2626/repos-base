import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { ServiceContentRequest } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/request.interface';
import type { GenericContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/generic-content-response.interface';

export interface SendTextForReviewRequest {
  textTypeCode: string;
  html: string;
}

export const saveServiceContent = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: ServiceContentRequest
): Promise<GenericContentResponse> => {
  try {
    const endpoint = `product-suppliers/generic/${productSupplierId}/service-details/${serviceDetailsId}/content`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      handleError(response.data?.error || 'Ocurrió un error en el proceso');
      throw new Error(response.data?.error || 'Ocurrió un error en el proceso');
    }
  } catch (error: any) {
    console.error('Error al guardar el contenido del servicio:', error);
    handleError(error);
    throw error;
  }
};

export const sendTextForReview = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: SendTextForReviewRequest
): Promise<void> => {
  try {
    const endpoint = `product-suppliers/generic/${productSupplierId}/service-details/${serviceDetailsId}/content/text`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success) {
      handleSuccessResponse(response.data);
    } else {
      handleError(response.data?.error || 'Ocurrió un error al enviar a revisión');
      throw new Error(response.data?.error || 'Ocurrió un error al enviar a revisión');
    }
  } catch (error: any) {
    console.error('Error al enviar texto a revisión:', error);
    handleError(error);
    throw error;
  }
};
