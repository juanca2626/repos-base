import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export interface DocumentExtensionForm {
  id: string | null;
  extensionDateRange: string[];
  typeDocumentId: number | null;
  reason: string | null;
  delete?: boolean;
}

export interface DocumentExtensionFormStore {
  applyDateAll: boolean;
  extensions: DocumentExtensionForm[];
}

export interface DocumentExtensionInfo {
  driverFullName?: string;
  licensePlate?: string;
  typeUnitCode?: string;
}

export interface DocumentExtensionFormProps {
  showDrawerForm: boolean;
  parentId: string;
  documentExtensionInfo: DocumentExtensionInfo;
  typeTechnicalSheet: TypeTechnicalSheetEnum;
}

export interface DocumentExtensionFormResponse {
  id: string;
  parent_id: string;
  date_from: string;
  date_to: string;
  reason: string;
  type_document_id: number;
}
