import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';

export const isClickableStatus = (status: VehicleDocumentStatusEnum): boolean => {
  return [
    VehicleDocumentStatusEnum.NO_DOCUMENTS,
    VehicleDocumentStatusEnum.TO_BE_REVIEWED,
    VehicleDocumentStatusEnum.REJECTED,
    VehicleDocumentStatusEnum.APPROVED,
  ].includes(status);
};

export const isApproved = (status: VehicleDocumentStatusEnum): boolean => {
  return status === VehicleDocumentStatusEnum.APPROVED;
};

export const isRejected = (status: VehicleDocumentStatusEnum): boolean => {
  return status === VehicleDocumentStatusEnum.REJECTED;
};

export const isVehicleDocumentDrawerVisible = (status: VehicleDocumentStatusEnum): boolean => {
  return [
    VehicleDocumentStatusEnum.NO_DOCUMENTS,
    VehicleDocumentStatusEnum.REJECTED,
    VehicleDocumentStatusEnum.APPROVED,
  ].includes(status);
};

export const isDocumentUnavailable = (status: VehicleDocumentStatusEnum): boolean => {
  return [
    VehicleDocumentStatusEnum.NO_DOCUMENTS,
    VehicleDocumentStatusEnum.NOT_APPLICABLE,
  ].includes(status);
};
