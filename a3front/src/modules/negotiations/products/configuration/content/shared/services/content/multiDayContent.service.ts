import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError } from '@/modules/negotiations/api/responseApi';
import type { MultidayContentResponse } from '../../interfaces/content/multiday-content-response.interface';

export const fetchContentMultiDays = async (
  productSupplierId: string,
  serviceDetailId: string
): Promise<MultidayContentResponse | null> => {
  try {
    const endpoint = `product-suppliers/package/${productSupplierId}/service-details/${serviceDetailId}/content`;
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
    console.log(error);
    if (error?.response?.status === 404) {
      return null;
    }
    console.error('Error al obtener el contenido:', error);
    handleError(error);
    throw error;
  }
};
