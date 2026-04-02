import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  TrainServiceDetailsRequest,
  TrainServiceDetailsResponse,
} from '../interfaces/train-service-details.interface';

async function saveTrainServiceDetails(
  productSupplierId: string,
  request: TrainServiceDetailsRequest
): Promise<ApiResponse<TrainServiceDetailsResponse>> {
  const response = await productApi.patch(
    `product-suppliers/train/${productSupplierId}/service-details`,
    request
  );
  return response.data;
}

async function fetchTrainServiceDetails(
  productSupplierId: string
): Promise<ApiResponse<TrainServiceDetailsResponse[]>> {
  const response = await productApi.get(
    `product-suppliers/train/${productSupplierId}/service-details`
  );
  return response.data;
}

export const trainServiceDetailsService = {
  saveTrainServiceDetails,
  fetchTrainServiceDetails,
};
