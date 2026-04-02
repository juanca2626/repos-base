export interface ServiceDestinations {
  destinationsCountries: DestinyCountry[];
  destinationsStates: DestinyState[];
  destinationsCities: DestinyCity[];
}

export interface DestinyCountry {
  code: string;
  label: string;
}

export interface DestinyState {
  code: string;
  label: string;
  country_code: string;
}

export interface DestinyCity {
  code: string;
  label: string;
  state_code: string;
}
