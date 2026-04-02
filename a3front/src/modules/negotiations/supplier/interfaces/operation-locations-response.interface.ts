export interface ApiResponse {
  country: { id: number; name: string };
  state: { id: number; name: string };
  city: { id: number; name: string } | null;
  zone: { id: number; name: string } | null;
}

export interface Datum {
  country: City;
  state: City;
  city: City | null;
  zone: City | null;
}

export interface City {
  id: number;
  name: string;
}

export interface TransformedData {
  ids: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  display_name: string;
}

export interface CountryLocation {
  country_name: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  location_name: string;
}

export interface LocationOption {
  label: string;
  value: string;
}
