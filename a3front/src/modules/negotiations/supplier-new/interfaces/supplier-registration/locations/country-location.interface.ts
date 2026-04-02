export interface CountryLocation {
  country_name: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  location_name: string;
}

export interface LocationResponse {
  id: string;
  name: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
}
