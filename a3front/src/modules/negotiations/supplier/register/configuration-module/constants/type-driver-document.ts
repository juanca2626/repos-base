import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';

export const driverDocumentInfo: Partial<Record<TypeVehicleDriverDocumentEnum, string>> = {
  [TypeVehicleDriverDocumentEnum.DNI]: 'Documento legible por ambas caras',
  [TypeVehicleDriverDocumentEnum.DRIVER_LICENSE]: 'Documento legible por ambas caras',
  [TypeVehicleDriverDocumentEnum.CRIMINAL_RECORD]:
    'No se acepta conductores con antecedentes penales',
  [TypeVehicleDriverDocumentEnum.POLICE_RECORD]:
    'No se acepta conductores con antecedentes policiales',
};
