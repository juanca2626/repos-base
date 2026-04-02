import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export const DOCUMENT_RESOURCES = {
  TRANSPORT_VEHICLE: 'vehicle-documents',
  VEHICLE_DRIVER: 'vehicle-driver-documents',
} as const;

export const DOCUMENT_ENTITY_MAPPING = {
  [TypeTechnicalSheetEnum.TRANSPORT_VEHICLE]: {
    statusEnum: VehicleDocumentStatusEnum,
    resource: DOCUMENT_RESOURCES.TRANSPORT_VEHICLE,
  },
  [TypeTechnicalSheetEnum.VEHICLE_DRIVER]: {
    statusEnum: DriverDocumentStatusEnum,
    resource: DOCUMENT_RESOURCES.VEHICLE_DRIVER,
  },
} as const;
