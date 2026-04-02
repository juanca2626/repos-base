export interface QuoteReservationResponse {
  success: boolean;
  errors: any;
  response: QuoteReservation;
}

export interface QuoteReservation {
  client_id: string;
  file_code: null;
  reference: string;
  guests: { [key: string]: null | string }[];
  reservations: Reservation[];
  billing: any[];
  reservations_services: ReservationsService[];
  reservations_flights: any[];
  date_init: Date;
  set_markup: number;
  executive_id: number;
}

export interface Reservation {
  token_search: string;
  room_ident: number;
  hotel_id: number;
  notes: null;
  best_option: boolean;
  rate_plan_room_id: number;
  suplements: any[];
  guest_note: string;
  date_from: Date;
  date_to: Date;
  quantity_adults: number;
  quantity_child: number;
  optional: number;
  child_ages: ChildAge[];
}

export interface ChildAge {
  child: number;
  age: number;
}

export interface ReservationsService {
  token_search: string;
  service_id: number;
  service_ident: number;
  rate_plan_id: number;
  reservation_time: string;
  date_from: Date;
  guest_note: string;
  quantity_adults: number;
  quantity_child: number;
  optional: number;
  child_ages: ChildAge[];
}
