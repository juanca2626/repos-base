export interface Service {
  type: string;
  service_id: number;
  itinerary: Itinerary;
}

export interface Itinerary {
  id: number;
  entity: string;
  name: string;
  category: string;
  object_id: number;
  object_code: string;
  country: string;
  date_in: Date;
  date_out: Date;
  start_time: string;
  departure_time: null;
  adults: number;
  children: number;
  total_amount: number;
  total_amount_penalties: number;
  total_cost_amount: number;
  status: boolean;
  confirmation_status: boolean;
  descriptions: any[];
  profitability: number;
  is_in_ope: boolean;
  sent_to_ope: boolean;
  city_in_iso: string;
  city_out_iso: string;
  zone_in_airport: boolean;
  zone_out_airport: boolean;
  hotel_origin: string;
  hotel_destination: boolean;
  service_amount_logs: any[];
  services: Services[];
  isLoading: boolean;
  flights_completed: boolean;
  service_category_id: string;
  service_sub_category_id: string;
  service_type_id: string;
  service_summary: string;
  service_itinerary: string;
}

export interface Services {
  id: number;
  master_service_id: number;
  file_itinerary_id: number;
  name: string;
  code_ifx: string;
  type_ifx: string;
  start_time: string;
  departure_time: string;
  amount_cost: number;
  status: boolean;
  confirmation_status: boolean;
  is_in_ope: boolean;
  sent_to_ope: boolean;
  service_amount: ServiceAmount;
  file_service_amount_logs: any[];
  compositions: Composition[];
}

export interface Composition {
  id: number;
  code: string;
  name: string;
  is_programmable: boolean;
  is_in_ope: boolean;
  sent_to_ope: boolean;
  start_time: string;
  departure_time: string;
  date_in: Date;
  date_out: Date;
  currency: string;
  amount_sale: number;
  amount_cost: number;
  amount_sale_origin: number;
  amount_cost_origin: number;
  duration_minutes: number;
  status: boolean;
  units: Unit[];
  supplier: Supplier;
  penality: Penality;
}

export interface Penality {
  penality_price: string;
  message: string;
}

export interface Supplier {
  id: number;
  file_service_composition_id: number;
  reservation_for_send: boolean;
  assigned: boolean;
  for_assign: boolean;
  code_request_book: string;
  code_request_invoice: string;
  code_request_voucher: string;
  policies_cancellation_service: string;
  send_communication: boolean;
}

export interface Unit {
  id: number;
  file_service_composition_id: number;
  status: boolean;
  amount_sale: number;
  amount_cost: number;
  amount_sale_origin: number;
  amount_cost_origin: number;
  accommodations: Accommodation[];
}

export interface Accommodation {
  id: number;
  file_service_unit_id: number;
  file_passenger_id: number;
  room_key: null;
  passenger: Passenger;
}

export interface Passenger {
  id: number;
  name: null | string;
  surnames: null | string;
}

export interface ServiceAmount {
  id: number;
  file_amount_type_flag_id: number;
  file_amount_reason_id: number;
  file_service_id: number;
  user_id: number;
  amount_previous: number;
  amount: number;
  file_amount_reason: FileAmountReason;
  file_amount_type_flag: FileAmountTypeFlag;
}

export interface FileAmountReason {
  id: number;
  name: string;
  influences_sale: boolean;
  area: string;
  visible: boolean;
  process: null;
}

export interface FileAmountTypeFlag {
  id: number;
  name: string;
  description: string;
  icon: string;
}
