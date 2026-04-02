import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { VehiclePhotoDetailResponse } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useVehiclePhotoUtils = () => {
  const resource = 'vehicle-photos';

  const fetchVehiclePhoto = async (vehiclePhotoId: string): Promise<VehiclePhotoDetailResponse> => {
    const { data } = await technicalSheetApi.get(`${resource}/${vehiclePhotoId}`);

    return data.data;
  };

  const fetchPhotoFromUrl = async (imageName: string): Promise<string | undefined> => {
    try {
      const response = await technicalSheetApi.get(`${resource}/show-image/${imageName}`, {
        responseType: 'blob',
      });

      const fileBlob = new Blob([response.data]);

      return URL.createObjectURL(fileBlob);
    } catch (error: any) {
      console.error('Error download vehicle document:', error);
    }
  };

  return {
    fetchVehiclePhoto,
    fetchPhotoFromUrl,
  };
};
