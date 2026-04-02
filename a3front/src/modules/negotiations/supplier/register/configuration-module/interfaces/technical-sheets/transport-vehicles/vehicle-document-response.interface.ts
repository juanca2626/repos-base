import type { DocumentDetail } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface VehicleDocumentDetailResponse {
  id: string;
  expiration_date: string;
  supplier_transport_vehicle_id: string;
  type_vehicle_document_id: number;
  document: DocumentDetail;
}
