import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';

export const vehicleDocumentInfo: Record<TypeVehicleDocumentEnum, string> = {
  [TypeVehicleDocumentEnum.SOAT]: 'Documento legible, sólo se acepta SOAT vigente de uso turístico',
  [TypeVehicleDocumentEnum.INSPECTION_CERTIFICATE]:
    'Documento legible que indique placa, resultado de la inspección y fecha de la siguiente',
  [TypeVehicleDocumentEnum.SECURE]: 'Cargar póliza completa del vehículo',
  [TypeVehicleDocumentEnum.PROPERTY_CARD]: 'Documento legible que indique la placa',
  [TypeVehicleDocumentEnum.CIRCULATION_CARD]: 'Documento legible que indique la placa y vigencia',
  [TypeVehicleDocumentEnum.GPS_CERTIFICATE]: 'Documento legible que indique la placa y vigencia',
};
