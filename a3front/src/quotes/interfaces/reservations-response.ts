export interface ReservationsResponse {
  success: boolean;
  data: Reservation;
}

export interface Reservation {
  id: number;
  client_id: number;
  file_code: string;
  booking_code: string;
  executive_id: number;
  customer_name: string;
  given_name: string;
  surname: string;
  date_init: Date;
  customer_country: string;
  total_hotels_discounts: number;
  total_hotels_subs: number;
  total_hotels: number;
  total_tax_and_services_amount: number;
  total_service_supplement_selected: number;
  reservations_hotel: ReservationsHotel[];
  reservations_service: ReservationsService[];
  reservations_flight: any[];
  min_date: Date;
  client: Client;
  reservation_errors: any[];
  total_services_tax_amount: number;
  total_services_subs: number;
  total_services: number;
  total: number;
}

export interface Client {
  id: number;
  code: string;
  name: string;
  business_name: string;
  address: string;
  web: null;
  anniversary: string;
  postal_code: null;
  ruc: string;
  email: string;
  phone: null;
  use_email: string;
  have_credit: number;
  credit_line: null;
  status: number;
  market_id: number;
  classification_code: string;
  classification_name: string;
  executive_code: string;
  bdm_id: null;
  general_markup: number;
  ecommerce: number;
  allow_direct_passenger_creation: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  country_id: number;
  city_code: null;
  city_name: null;
  language_id: number;
  logo: string;
  bdm: null;
  type: string;
  first_name: null;
  second_name: null;
  last_name: null;
  mothers_last_name: null;
  doctype_id: number;
}

export interface ReservationsHotel {
  reservation_code: number;
  status: number;
  status_in_channel: number;
  hotel_id: number;
  hotel_name: string;
  hotel_code: string;
  check_in: Date;
  check_out: Date;
  check_in_time: string;
  check_out_time: string;
  total_amount: number;
  total_tax_and_services_amount: number;
  hotel_logo: string;
  reservations_hotel_rooms: ReservationsHotelRoom[];
}

export interface ReservationsHotelRoom {
  reservation_room_code: number;
  status: number;
  status_in_channel: number;
  first_penalty_date: Date;
  room_name: RoomName;
  room_code: string;
  room_description: RoomDescription;
  room_type_id: number;
  rate_plan_code: string;
  rate_plan_name: string;
  meal_name: MealName;
  rates_plans_room_id: number;
  onRequest: number;
  adult_num: number;
  child_num: number;
  guest_note: string;
  observations: string;
  supplements: any[];
  total_amount: number;
  policies_cancellation: PoliciesCancellation[];
  taxes_and_services: TaxesAndServices;
}

export enum MealName {
  Desayuno = 'Desayuno',
}

export interface PoliciesCancellation {
  from: number;
  to: number;
  apply_date: string;
  expire_date: string;
  days_remain: number;
  penalty_price: string;
  penalty_code: PenaltyCode;
  message: string;
  policies_found: number;
  days_before_checkin: string;
}

export enum PenaltyCode {
  Percentage = 'PERCENTAGE',
  TotalReservation = 'TOTAL_RESERVATION',
}

export enum RoomDescription {
  StandardDbl = 'Standard dbl',
  StandardSgl = 'Standard sgl',
}

export enum RoomName {
  HabitaciónEstándarSimple = 'Habitación estándar simple',
  StandardSingleRoom = 'Standard single room',
  StandardTwinRoom = 'Standard twin room',
}

export interface TaxesAndServices {
  apply_fees: any[];
  type_fees: TypeFees;
  amount_fees: number;
  amount_before_fees: number;
  amount_after_fees: number;
}

export enum TypeFees {
  FeesForForeign = 'fees_for_foreign',
}

export interface ReservationsService {
  reservation_code: number;
  total_service_supplement_selected: number;
  status: number;
  service_id: number;
  service_name: string;
  service_code: string;
  date: Date;
  adult_num: number;
  child_num: number;
  guest_note: string;
  total_amount: number;
  total_amount_base: number;
  total_amount_taxes: number;
  total_amount_supplements: number;
  type_service: TypeService;
  parent_id: null;
  on_request: number;
  service_logo: string;
}

export enum TypeService {
  Service = 'service',
}
