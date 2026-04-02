import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { MultiDayContentRequest } from '../interfaces';
import type { MultidayContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/multiday-content-response.interface';
export interface PackageSendTextForReviewRequest {
  textTypeCode: string;
  html?: string;
  days?: { dayNumber: number; html: string }[];
}

export const saveServiceContent = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: MultiDayContentRequest
): Promise<MultidayContentResponse> => {
  try {
    const endpoint = `product-suppliers/package/${productSupplierId}/service-details/${serviceDetailsId}/content`;
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

export const sendPackageTextForReview = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: PackageSendTextForReviewRequest
): Promise<void> => {
  try {
    const endpoint = `product-suppliers/package/${productSupplierId}/service-details/${serviceDetailsId}/content/text`;
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
