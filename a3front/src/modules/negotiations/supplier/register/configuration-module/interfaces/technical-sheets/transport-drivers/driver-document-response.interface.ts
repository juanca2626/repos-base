import type { DocumentDetail } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface DriverDocumentDetailResponse {
  id: string;
  supplier_vehicle_driver_id: string;
  type_vehicle_driver_document_id: number;
  expiration_date: string;
  document: DocumentDetail;
}
