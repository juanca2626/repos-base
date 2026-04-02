export interface SeriesProgramsResponseInterface {
  id: number;
  serie_departure_program_id: number;
  file: number;
  passenger_group_name: string;
  qty_passengers: number;
  client_id: number;
  user_id: number;
  ticket_mapi: string;
  observation: string;
  created_at: Date;
  departure_program: DepartureProgram;
  client: Client;
  user: User;
}

export interface Client {
  id: number;
  name: string;
}

export interface DepartureProgram {
  id: number;
  serie_program_id: number;
  serie_departure_id: number;
  date: Date;
  program: Client;
  departure: Departure;
}

export interface Departure {
  id: number;
  name: string;
  has_holiday: boolean;
  name_holiday: null;
}

export interface User {
  id: number;
  name: string;
  client_seller: null;
}
