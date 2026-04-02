import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type {
  CapacityConfigurationRequest,
  CapacityConfigurationResponse,
} from '../../interfaces/configuration/capacity-configuration.interface';

export const createCapacityConfiguration = async (
  productSupplierId: string,
  request: CapacityConfigurationRequest
): Promise<CapacityConfigurationResponse> => {
  try {
    const endpoint = `product-suppliers/${productSupplierId}/capacity-configurations`;
    const response = await productApi.post(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      throw new Error(response.data?.error || 'Error al crear la configuración de capacidad');
    }
  } catch (error: any) {
    console.error('Error al crear la configuración de capacidad:', error);
    handleError(error);
    throw error;
  }
};

export const updateCapacityConfiguration = async (
  productSupplierId: string,
  capacityConfigurationId: string,
  request: CapacityConfigurationRequest
): Promise<CapacityConfigurationResponse> => {
  try {
    const endpoint = `product-suppliers/${productSupplierId}/capacity-configurations/${capacityConfigurationId}`;
    const response = await productApi.patch(endpoint, request);

    if (response.data?.success && response.data?.data) {
      handleSuccessResponse(response.data);
      return response.data.data;
    } else {
      throw new Error(response.data?.error || 'Error al actualizar la configuración de capacidad');
    }
  } catch (error: any) {
    console.error('Error al actualizar la configuración de capacidad:', error);
    handleError(error);
    throw error;
  }
};

export const fetchCapacityConfigurations = async (
  productSupplierId: string
): Promise<CapacityConfigurationResponse[]> => {
  try {
    const endpoint = `product-suppliers/${productSupplierId}/capacity-configurations`;
    const response = await productApi.get(endpoint);

    if (response.data?.success && response.data?.data) {
      return response.data.data;
    } else {
      throw new Error(response.data?.error || 'Error al obtener las configuraciones de capacidad');
    }
  } catch (error: any) {
    console.error('Error al obtener las configuraciones de capacidad:', error);
    handleError(error);
    throw error;
  }
};
