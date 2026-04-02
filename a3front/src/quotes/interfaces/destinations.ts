export interface Destinations {
  destinationsCountries: DestinationsCountry[];
  destinationsStates: DestinationsState[];
  destinationsCities: DestinationsCity[];
  destinationsZones: DestinationsZone[];
}

export interface DestinationsCountry {
  code: string;
  label: string;
}

export interface DestinationsState {
  code: string;
  label: string;
  country_code: string;
}

export interface DestinationsCity {
  code: string;
  label: string;
  state_code: string;
}

export interface DestinationsZone {
  id: string;
  label: string;
  city_code: string;
}
