export interface AirlineResponse {
  success: boolean;
  data: Airline[];
  params: Params;
}

export interface Airline {
  codigo: string;
  razon: string;
}

export interface Params {
  term: string;
}
