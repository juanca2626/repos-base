export interface BaseExtensionFormResponse {
  id: string;
  date_from: string;
  date_to: string;
  reason: string;
}

export interface VehicleExtensionFormResponse extends BaseExtensionFormResponse {
  supplier_transport_vehicle_id: string;
  type_vehicle_document_id: number;
}

export interface DriverExtensionFormResponse extends BaseExtensionFormResponse {
  supplier_vehicle_driver_id: string;
  type_vehicle_driver_document_id: number;
}
