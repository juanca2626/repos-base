import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';

export interface DriverExtensionTransform {
  id: string;
  dateTo: string;
  typeVehicleDriverDocumentId: TypeVehicleDriverDocumentEnum;
}

export interface DriverDocumentTransform {
  supplierVehicleDriverId: string | null;
  typeVehicleDriverDocumentId: TypeVehicleDriverDocumentEnum | null;
  status: DriverDocumentStatusEnum;
  id?: string;
  expirationDate?: string;
  lastObservation?: string | null;
  typeVehicleDriverDocumentName?: string;
  extension?: DriverExtensionTransform | null;
}

export interface DriverDocuments {
  dni: DriverDocumentTransform;
  driverLicense: DriverDocumentTransform;
  criminalRecord: DriverDocumentTransform;
  policeRecord: DriverDocumentTransform;
  driverRecord: DriverDocumentTransform;
  others: DriverDocumentTransform;
}

export interface SupplierVehicleDriver {
  id: string;
  subClassificationSupplierId: number;
  fullName: string;
  name: string;
  surnames: string;
  phone: string;
  statusDni: DriverDocumentStatusEnum;
  statusDriverLicense: DriverDocumentStatusEnum;
  statusCriminalRecord: DriverDocumentStatusEnum;
  statusPoliceRecord: DriverDocumentStatusEnum;
  statusDriverRecord: DriverDocumentStatusEnum;
  statusOthers: DriverDocumentStatusEnum;
  status: number;
  driverDocuments: DriverDocuments;
}
