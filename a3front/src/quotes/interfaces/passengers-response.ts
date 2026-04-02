export interface PassengersResponse {
  '1106398': PassengerR;
  '1106399': PassengerR;
  process: boolean;
  success: boolean;
  message: string;
}

export interface PassengerR {
  id: number;
  first_name: null | string;
  last_name: null | string;
  gender: null;
  birthday: null;
  document_number: null;
  doctype_iso: null;
  country_iso: null;
  city_ifx_iso: null;
  email: null;
  phone: null;
  notes: null;
  created_at: Date;
  updated_at: Date;
  quote_id: number;
  address: null;
  dietary_restrictions: null;
  medical_restrictions: null;
  type: string;
  is_direct_client: boolean;
  quote_age_child_id: null;
  quote_passenger_id: null;
}
