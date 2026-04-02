export interface Unit {
  id: number | null;
  status: string;
  amount_sale: number;
  amount_cost: number;
  accommodations: Accommodation[];
}

export interface Accommodation {
  file_passenger_id: number;
  room_key: string | null;
}

export interface Composition {
  id: number | null;
  file_classification_id: number;
  type_composition_id: number;
  type_component_service_id: string;
  composition_id: number;
  code: string;
  name: string;
  item_number: number;
  total_adults: number;
  total_children: number;
  total_infants: number;
  units: Unit[];
}

export interface Service {
  id: number | null;
  master_service_id: number;
  name: string;
  code: string;
  amount_cost: number;
  compositions: Composition[];
}

export interface Detail {
  language_id: number;
  itinerary: string;
  skeleton: string;
}

export interface Payload {
  file_id: number;
  entity: string;
  object_id: string;
  object_code: string;
  name: string;
  category: string;
  country_in_iso: string;
  country_in_name: string;
  city_in_iso: string;
  city_in_name: string;
  country_out_iso: string;
  country_out_name: string;
  city_out_iso: string;
  city_out_name: string;
  start_time: string;
  departure_time: string;
  date: string;
  adult_num: number;
  child_num: number;
  total_amount: number;
  services: Service[];
  details: Detail[];
}
