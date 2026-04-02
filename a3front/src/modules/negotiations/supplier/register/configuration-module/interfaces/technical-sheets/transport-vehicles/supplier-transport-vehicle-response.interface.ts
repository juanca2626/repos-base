import type {
  AutoBrand,
  TypeUnitTransport,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';
import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';

export interface TypeVehicleDocument {
  id: number;
  name: string;
}

export interface VehicleDocumentResponse {
  id: string;
  type_vehicle_document: TypeVehicleDocument;
  expiration_date: string;
  last_observation: string | null;
  status: number;
}

export interface VehiclePhotoResponse {
  id: string;
  last_observation: string;
  status: number;
}

export interface VehicleExtensionResponse {
  id: string;
  date_to: string;
  type_vehicle_document_id: TypeVehicleDocumentEnum;
}

export interface SupplierTransportVehicleResponse {
  id: string;
  sub_classification_supplier_id: number;
  supplier_branch_office_id: number;
  auto_brand: AutoBrand;
  type_unit_transport: TypeUnitTransport;
  license_plate: string;
  manufacturing_year: number;
  number_seats: number;
  description: string;
  status_photos: VehiclePhotoStatusEnum;
  status_soat: VehicleDocumentStatusEnum;
  status_inspection_certificate: VehicleDocumentStatusEnum;
  status_secure: VehicleDocumentStatusEnum;
  status_property_card: VehicleDocumentStatusEnum;
  status_circulation_card: VehicleDocumentStatusEnum;
  status_gps_certificate: VehicleDocumentStatusEnum;
  status: number;
  last_vehicle_extension_reason: string;
  vehicle_documents: VehicleDocumentResponse[];
  vehicle_photo: VehiclePhotoResponse | null;
  vehicle_extensions: VehicleExtensionResponse[];
}
