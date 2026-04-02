import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type { DocumentExtensionInfo } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface ActiveExtensionNotifyProps {
  showModal: boolean;
  documentExtensionInfo: DocumentExtensionInfo;
  documentExtensionIds: string[];
  typeTechnicalSheet: TypeTechnicalSheetEnum;
}

interface User {
  code: string;
  name: string;
}

interface TypeDocument {
  id: number;
  name: string;
}

export interface ExtensionSummary {
  user: User;
  dateTo: string;
  reason: string;
  typeDocument: TypeDocument;
}

export interface ExtensionSummaryResponse {
  id: string;
  user: User;
  date_to: string;
  reason: string;
  type_vehicle_driver_document?: TypeDocument;
  type_vehicle_document?: TypeDocument;
}
