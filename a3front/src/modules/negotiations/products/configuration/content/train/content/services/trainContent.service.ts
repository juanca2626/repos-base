import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { TrainContentRequest } from '../interfaces/train-content-request.interface';
import type { TrainContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/train-content-response.interface';
import type { SendTextForReviewRequest } from '@/modules/negotiations/products/configuration/content/generic/content/services/serviceContent.service';

export const saveTrainServiceContent = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: TrainContentRequest
): Promise<TrainContentResponse> => {
  try {
    const endpoint = `product-suppliers/train/${productSupplierId}/service-details/${serviceDetailsId}/content`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      handleError(response.data?.error || 'Ocurrió un error en el proceso');
      throw new Error(response.data?.error || 'Ocurrió un error en el proceso');
    }
  } catch (error: any) {
    console.error('Error al guardar el contenido del servicio train:', error);
    handleError(error);
    throw error;
  }
};

export const sendTrainTextForReview = async (
  productSupplierId: string,
  serviceDetailsId: string,
  request: SendTextForReviewRequest
): Promise<void> => {
  try {
    const endpoint = `product-suppliers/train/${productSupplierId}/service-details/${serviceDetailsId}/content/text`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success) {
      handleSuccessResponse(response.data);
    } else {
      handleError(response.data?.error || 'Ocurrió un error al enviar a revisión');
      throw new Error(response.data?.error || 'Ocurrió un error al enviar a revisión');
    }
  } catch (error: any) {
    console.error('Error al enviar texto a revisión (train):', error);
    handleError(error);
    throw error;
  }
};
