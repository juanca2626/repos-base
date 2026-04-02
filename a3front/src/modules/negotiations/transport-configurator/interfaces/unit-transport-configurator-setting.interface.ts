// unit-transport-setting.interfaces.ts
//import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface'; // Importa la interfaz de paginación existente

// Interface para la capacidad de configuración
export interface Capacity {
  minimum: number;
  maximum: number;
}

// Interface para los detalles de la configuración
export interface SettingDetail {
  id?: number; // Puede ser opcional si es nuevo y aún no se ha guardado en el API
  type_unit_transport_setting_id?: number; // Puede ser opcional hasta que se guarde
  capacity: Capacity;
  representative_quantity: number;
  trunk_car_quantity: number;
  trunk_representative_quantity: number;
  quantity_guides: number;
  quantity_units_required: number;
}

// Interface para la localización de la configuración
export interface Location {
  id: number;
  state: string;
  quantity: number;
}

// Interface para los datos de transferencia (Transferencia y Tipo de Transferencia)
export interface Transfer {
  id: number;
  name: string;
}

// Interface para las configuraciones de tipo de transporte
export interface UnitTransportConfigurationSetting {
  id?: number; // Puede ser opcional hasta que se guarde
  transfer: Transfer;
  type_transfer: string; // 'TOUR' o 'TRANSFER'
  date_from: Date; // Formato 'YYYY-MM-DD'
  date_to: Date; // Formato 'YYYY-MM-DD'
  status: boolean; // 1 para activo, 0 para inactivo
  created_at?: string; // Campo opcional, proporcionado por el API
  setting_details: SettingDetail[];
  locations: Location;
}

// Interface para el payload de envío al API
export interface SaveTransportConfigurationPayload {
  settings: UnitTransportConfigurationSetting[];
  copy_location: number[]; // IDs de las locaciones a las que se copiará la configuración
}

// Interface para la respuesta del API con las configuraciones de transporte
/*export interface TransportConfiguratorApiResponse {
  success: boolean;
  data: UnitTransportConfigurationSetting[];
  pagination: PaginationInterface; // Usa la interfaz de paginación existente
  code: number;
}



// Interface para la respuesta al actualizar o crear configuraciones
export interface TransportConfiguratorUpdateResponse {
  success: boolean;
  data: UnitTransportConfigurationSetting; // La configuración de transporte que se ha creado o actualizado
  code: number;
}*/

export interface TransferItem {
  id: number;
  type_transfer: string;
  name: string;
}
