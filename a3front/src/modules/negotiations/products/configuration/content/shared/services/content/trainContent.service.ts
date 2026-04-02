import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError } from '@/modules/negotiations/api/responseApi';
import type { TrainContentResponse } from '../../interfaces/content/train-content-response.interface';

export const fetchContentTrain = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<TrainContentResponse | null> => {
  try {
    const endpoint = `product-suppliers/train/${productSupplierId}/service-details/${serviceDetailId}/content`;
    const response = await productApi.get(endpoint);

    if (response.data?.success && response.data?.data) {
      return response.data.data;
    } else {
      if (response.data?.data === null) {
        return null;
      } else {
        throw new Error(response.data?.error || 'Error al obtener el contenido');
      }
    }
  } catch (error: any) {
    console.error('Error al obtener el contenido:', error);
    handleError(error);
    throw error;
  }
};
