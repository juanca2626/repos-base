import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import type { TypeVehicleDocument } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';

export interface AutoBrand {
  id: string;
  name: string;
}

export interface TypeUnitTransport {
  id: number;
  code: string;
  name: string;
}

export interface VehicleDocument {
  id?: string;
  type_vehicle_document?: TypeVehicleDocument;
  expiration_date?: string;
  last_observation?: string | null;
  status: VehicleDocumentStatusEnum;
}

export interface VehicleDocumentTransform {
  supplierTransportVehicleId: string | null;
  typeVehicleDocumentId: number | null;
  status: VehicleDocumentStatusEnum;
  id?: string;
  expirationDate?: string;
  lastObservation?: string | null;
  typeVehicleDocumentName?: string;
  extension?: VehicleExtensionTransform | null;
}

export interface VehicleExtensionTransform {
  id: string;
  dateTo: string;
  typeVehicleDocumentId: TypeVehicleDocumentEnum;
}

export interface VehiclePhotoTransform {
  supplierTransportVehicleId: string | null;
  status: VehiclePhotoStatusEnum;
  id?: string;
  lastObservation?: string | null;
}

interface VehicleDocuments {
  soat: VehicleDocument;
  inspection_certificate: VehicleDocument;
  secure: VehicleDocument;
  property_card: VehicleDocument;
  circulation_card: VehicleDocument;
  gps_certificate: VehicleDocument;
}

export interface SupplierTransportVehicle {
  id: string;
  supplierBranchOfficeId: number;
  autoBrand: AutoBrand;
  manufacturingYear: number;
  typeUnit: TypeUnitTransport;
  licensePlate: string;
  numberSeats: number;
  description: string;
  statusPhotos: VehiclePhotoStatusEnum; // maneja estados 0,1,2,3 - no tiene fecha vencimiento
  statusSoat: VehicleDocumentStatusEnum;
  statusInspectionCertificate: VehicleDocumentStatusEnum;
  statusSecure: VehicleDocumentStatusEnum;
  statusPropertyCard: VehicleDocumentStatusEnum;
  statusCirculationCard: VehicleDocumentStatusEnum;
  statusGpsCertificate: VehicleDocumentStatusEnum;
  status: number;
  vehiclePhoto: VehiclePhotoTransform;
  vehicleDocuments: VehicleDocuments;
}

export interface TransportVehicleListEmitInterface {
  (event: 'onTransportVehicleListUnmounted'): void;
}
