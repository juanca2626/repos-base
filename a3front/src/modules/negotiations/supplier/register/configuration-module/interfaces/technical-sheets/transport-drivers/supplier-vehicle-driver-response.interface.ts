import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';

export interface TypeVehicleDriverDocument {
  id: number;
  name: string;
}

export interface VehicleDriverDocumentResponse {
  id: string;
  type_vehicle_driver_document: TypeVehicleDriverDocument;
  expiration_date: string;
  last_observation: string | null;
  status: number;
}

export interface DriverExtensionResponse {
  id: string;
  date_to: string;
  type_vehicle_driver_document_id: TypeVehicleDriverDocumentEnum;
}

export interface SupplierVehicleDriverResponse {
  id: string;
  sub_classification_supplier_id: number;
  name: string;
  surnames: string;
  phone: string;
  status_dni: DriverDocumentStatusEnum;
  status_driver_license: DriverDocumentStatusEnum;
  status_criminal_record: DriverDocumentStatusEnum;
  status_police_record: DriverDocumentStatusEnum;
  status_driver_record: DriverDocumentStatusEnum;
  status_others: DriverDocumentStatusEnum;
  status: number;
  last_driver_extension_reason: any;
  vehicle_driver_documents: VehicleDriverDocumentResponse[];
  driver_extensions: DriverExtensionResponse[];
}
