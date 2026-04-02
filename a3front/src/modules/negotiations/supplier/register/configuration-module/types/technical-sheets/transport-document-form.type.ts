import type { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type {
  DriverDocumentTransform,
  VehicleDocumentTransform,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type { TransportDocumentResponseMap } from '@/modules/negotiations/supplier/register/configuration-module/types';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';

export type SelectedDocument<T extends TypeTechnicalSheetEnum> =
  T extends TypeTechnicalSheetEnum.VEHICLE_DRIVER
    ? DriverDocumentTransform
    : VehicleDocumentTransform;

export type TransportDocumentResponseType<T extends TypeTechnicalSheetEnum> =
  TransportDocumentResponseMap[T];

export type SelectedDocumentStatus = DriverDocumentStatusEnum | VehicleDocumentStatusEnum | null;
