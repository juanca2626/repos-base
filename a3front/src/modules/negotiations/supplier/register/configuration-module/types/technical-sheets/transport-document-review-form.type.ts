import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import type { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type {
  BaseTransportDocumentForm,
  DriverDocumentDetailResponse,
  VehicleDocumentDetailResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export type TransportDocumentReviewForm<TStatus> = BaseTransportDocumentForm & {
  status: TStatus | null;
};

export type TransportDocumentFormTypeMap = {
  [TypeTechnicalSheetEnum.VEHICLE_DRIVER]: TransportDocumentReviewForm<DriverDocumentStatusEnum>;
  [TypeTechnicalSheetEnum.TRANSPORT_VEHICLE]: TransportDocumentReviewForm<VehicleDocumentStatusEnum>;
};

export type TransportDocumentResponseMap = {
  [TypeTechnicalSheetEnum.VEHICLE_DRIVER]: DriverDocumentDetailResponse;
  [TypeTechnicalSheetEnum.TRANSPORT_VEHICLE]: VehicleDocumentDetailResponse;
};
