import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { createStatusOptions } from '@/modules/negotiations/supplier/register/configuration-module/helpers/documentStatusHelper';

// estados generales
export const vehicleDocumentStatusData: Record<number, string> = {
  [VehicleDocumentStatusEnum.NO_DOCUMENTS]: 'Sin documentos',
  [VehicleDocumentStatusEnum.TO_BE_REVIEWED]: 'Por revisar',
  [VehicleDocumentStatusEnum.APPROVED]: 'Aprobado',
  [VehicleDocumentStatusEnum.REJECTED]: 'Rechazado',
  [VehicleDocumentStatusEnum.TO_EXPIRE]: 'Por vencer',
  [VehicleDocumentStatusEnum.EXPIRED]: 'Vencido',
  [VehicleDocumentStatusEnum.NOT_APPLICABLE]: 'No se aplica',
};

// estados para selects
export const vehicleDocumentStatusOptions = createStatusOptions(vehicleDocumentStatusData);
