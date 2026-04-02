import type { PassengerAgeChild } from '@/quotes/interfaces/quote.response';

export interface QuoteSaveRequest {
  categories: number[];
  client_id?: string;
  date: Date | string;
  date_estimated: Date | string | null;
  name: string;
  notes: string[];
  operation: string;
  passengers: Passenger[];
  people: People;
  service_type_id: number;
  languageId: number | null;
  reference_code?: string;
}

export interface Passenger {
  address?: null;
  age_child?: PassengerAgeChild;
  birthday: null;
  checked?: boolean;
  city_ifx_iso?: null;
  country_iso: string;
  created_at?: Date;
  dietary_restrictions?: null;
  doctype_iso: string;
  document_number: null;
  email: null;
  first_name: string;
  gender: string;
  id?: number | null;
  index?: number;
  is_direct_client?: boolean;
  last_name: string;
  medical_restrictions?: null;
  notes: null;
  phone: null;
  quote_age_child_id?: null;
  quote_id?: number;
  quote_passenger_id?: null;
  type: string;
  updated_at?: null | Date;
}

export interface People {
  adults: number;
  child: number;
  id: number;
  infant?: number;
  quote_id: number;
}
