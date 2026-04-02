import type {
  DocumentStatusData,
  DriverDocumentStatusProps,
  VehicleDocumentStatusProps,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';

export type DocumentStatusProps = VehicleDocumentStatusProps | DriverDocumentStatusProps;

export type DocumentStatusEnum = typeof VehicleDocumentStatusEnum | typeof DriverDocumentStatusEnum;

export type DocumentStatusDataMap = Record<
  VehicleDocumentStatusEnum | DriverDocumentStatusEnum,
  DocumentStatusData
>;

export type DriverDocumentStatusKey =
  | 'status_dni'
  | 'status_driver_license'
  | 'status_criminal_record'
  | 'status_police_record'
  | 'status_driver_record'
  | 'status_others';
