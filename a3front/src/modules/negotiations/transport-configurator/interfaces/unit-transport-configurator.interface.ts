export interface Location {
  id: number;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  display_name: string;
  quantity: number;
}

export interface UnitTransportConfigurator {
  id: number | null;
  name: string;
  code: string;
  status: boolean;
  is_trunk: boolean;
  created_at: string;
  locations: Location[];
  isValid: boolean;
}
