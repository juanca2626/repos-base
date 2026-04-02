import type {
  Transfer,
  TypeUnitTransport,
} from '@/modules/negotiations/type-unit-configurator/settings/interfaces';

export interface SettingDetailResponse {
  id: number;
  type_unit_transport_transfer_id: number;
  type_unit_transport: TypeUnitTransport;
  minimum_capacity: number;
  maximum_capacity: number;
  representative_quantity: number;
  trunk_car_quantity: number;
  trunk_representative_quantity: number;
  quantity_guides: number;
  quantity_units_required: number;
}

export interface TypeUnitTransportTransferResponse {
  id: number;
  type_unit_transport_setting_id: number;
  transfer: Transfer;
  status: number;
  setting_details: SettingDetailResponse[];
}
