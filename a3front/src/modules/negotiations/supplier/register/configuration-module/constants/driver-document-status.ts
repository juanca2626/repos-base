import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { createStatusOptions } from '@/modules/negotiations/supplier/register/configuration-module/helpers/documentStatusHelper';

// estados generales
export const driverDocumentStatusData: Record<number, string> = {
  [DriverDocumentStatusEnum.NO_DOCUMENTS]: 'Sin documentos',
  [DriverDocumentStatusEnum.TO_BE_REVIEWED]: 'Por revisar',
  [DriverDocumentStatusEnum.APPROVED]: 'Aprobado',
  [DriverDocumentStatusEnum.REJECTED]: 'Rechazado',
  [DriverDocumentStatusEnum.TO_EXPIRE]: 'Por vencer',
  [DriverDocumentStatusEnum.EXPIRED]: 'Vencido',
};

// estados para selects
export const driverDocumentStatusOptions = createStatusOptions(driverDocumentStatusData);
