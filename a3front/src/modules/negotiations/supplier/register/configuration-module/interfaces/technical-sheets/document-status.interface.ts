import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export interface CommonExtensionTransform {
  id: string;
  dateTo: string;
}

interface BaseDocumentStatusProps {
  date?: string | null;
  observations?: string | null;
  extension?: CommonExtensionTransform | null;
}

export interface DriverDocumentStatusProps extends BaseDocumentStatusProps {
  technicalSheetType: TypeTechnicalSheetEnum.VEHICLE_DRIVER;
  status: DriverDocumentStatusEnum;
}

export interface VehicleDocumentStatusProps extends BaseDocumentStatusProps {
  technicalSheetType: TypeTechnicalSheetEnum.TRANSPORT_VEHICLE;
  status: VehicleDocumentStatusEnum;
}

export interface DocumentStatusData {
  text: string;
  statusClass: string;
  showUploadIcon?: boolean;
}
