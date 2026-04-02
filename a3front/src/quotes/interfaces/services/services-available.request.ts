export interface ServicesAvailableRequest {
  adults: number;
  allWords: number;
  children: number;
  client_id?: string;
  date_from: Date | string;
  date?: Date | string;
  destiny: Destiny | string;
  lang: string;
  limit?: number;
  origin: Destiny | string;
  page?: number;
  service_category?: number[];
  service_name: string;
  service_type?: number | string;
  type_service?: number | string;
  experience_type?: number | string;
  service_sub_category?: number | string;
  zone_destination?: number | string;
  service_premium?: number | string;
  include_transfer_driver?: number | string;
  price_range?: { min: number; max: number };
}

export interface Destiny {
  code: string;
  label: string;
}
