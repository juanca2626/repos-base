import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';

export const isApproved = (status: DriverDocumentStatusEnum): boolean => {
  return status === DriverDocumentStatusEnum.APPROVED;
};

export const isRejected = (status: DriverDocumentStatusEnum): boolean => {
  return status === DriverDocumentStatusEnum.REJECTED;
};

export const isToBeReviewed = (status: DriverDocumentStatusEnum): boolean => {
  return status === DriverDocumentStatusEnum.TO_BE_REVIEWED;
};

export const isDriverDocumentDrawerVisible = (status: DriverDocumentStatusEnum): boolean => {
  return [
    DriverDocumentStatusEnum.NO_DOCUMENTS,
    DriverDocumentStatusEnum.REJECTED,
    DriverDocumentStatusEnum.APPROVED,
  ].includes(status);
};
