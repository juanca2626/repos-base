export interface PassengersRequest {
  passengers: Passenger[];
  repeat: number;
  modePassenger: number;
  file: number;
  type: string;
  paxs: number;
}

export interface Passenger {
  id: number;
  first_name: string;
  last_name: string;
  gender: string;
  birthday: string;
  document_number: string;
  doctype_iso: string;
  country_iso: string;
  city_ifx_iso: null;
  email: string;
  phone: string;
  notes: string;
  created_at: Date;
  updated_at: string;
  quote_id: number;
  address: string;
  dietary_restrictions: string;
  medical_restrictions: string;
  type: string;
  is_direct_client: boolean;
  quote_age_child_id: string;
  quote_passenger_id: string;
  nombres?: string;
  apellidos?: string;
  sexo?: null;
  fecnac?: string;
  nrodoc?: string;
  tipdoc?: null;
  nacion?: string;
  correo?: string;
  celula?: string;
  observ?: string;
  resali?: string;
  resmed?: string;
  tipo?: string;
  nombre?: string;
}
