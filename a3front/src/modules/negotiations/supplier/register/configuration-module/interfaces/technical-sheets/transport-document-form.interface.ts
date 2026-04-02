import type { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';
import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
import type { SelectedDocumentStatus } from '@/modules/negotiations/supplier/register/configuration-module/types';

export interface BaseTransportDocumentForm {
  id: string | null;
  expirationDate: string | null;
  observations: string | null;
}

export interface TransportDocumentForm extends BaseTransportDocumentForm {
  file: File | null | undefined;
  parentId: string | null;
  typeDocumentId: number | null;
  notApplicable: boolean;
}

export interface SelectedDocument {
  parentId: string | null;
  typeDocumentId: TypeVehicleDocumentEnum | TypeVehicleDriverDocumentEnum | null;
  status: SelectedDocumentStatus;
  id?: string;
  expirationDate?: string;
  lastObservation?: string | null;
  typeDocumentName?: string;
}

export interface TransportDocumentFormProps<T extends TypeTechnicalSheetEnum> {
  showDrawerForm: boolean;
  typeTechnicalSheet: T;
  selectedDocument: SelectedDocument;
}

export interface TransportDocumentReviewFormProps<T extends TypeTechnicalSheetEnum> {
  showDrawerForm: boolean;
  typeTechnicalSheet: T;
  documentId?: string;
}

export interface DocumentDetail {
  filename: string;
  size: number;
}
