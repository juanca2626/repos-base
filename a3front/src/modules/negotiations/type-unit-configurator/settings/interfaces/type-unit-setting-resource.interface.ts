export interface Transfer {
  id: number;
  type_transfer: string;
  name: string;
}

export interface LocationResource {
  id: number;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  country_name: string;
  state_name: string;
  city_name: string | null;
  zone_name: string | null;
  full_location_name: string;
}

export interface ResourceData {
  transfers: Transfer[];
  type_unit_transport_locations: LocationResource[];
}

export interface CountryLocation {
  country_name: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  location_name: string;
}
