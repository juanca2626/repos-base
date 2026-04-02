export interface Country {
  id: number;
  name: string;
}

export interface State {
  id: number;
  name: string;
}

export interface City {
  id: number;
  name: string;
}

export interface Location {
  country: Country;
  state: State;
  city: City;
  location_name: string;
}
