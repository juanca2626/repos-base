import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';

export const isVehiclePhotoDrawerVisible = (status: VehiclePhotoStatusEnum): boolean => {
  return [
    VehiclePhotoStatusEnum.NO_DOCUMENTS,
    VehiclePhotoStatusEnum.REJECTED,
    VehiclePhotoStatusEnum.APPROVED,
  ].includes(status);
};

export const isApproved = (status: VehiclePhotoStatusEnum): boolean => {
  return status === VehiclePhotoStatusEnum.APPROVED;
};

export const isRejected = (status: VehiclePhotoStatusEnum): boolean => {
  return status === VehiclePhotoStatusEnum.REJECTED;
};
