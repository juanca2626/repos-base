export interface QuoteResponse {
  withHeader?: boolean;
  id_original?: boolean | number | string;
  withClientLogo: number | string;
  radioCategories: number | string;

  accommodation: Accommodation;
  age_child: PassengerAgeChild[];
  categories: OpenQuoteCategory[];
  cities: null;
  code: null;
  created_at: Date;
  date_in: Date | string;
  discount: null;
  discount_detail: null;
  discount_user_permission: null;
  editing_quote_user: EditingQuoteUser;
  estimated_price: number;
  estimated_travel_date: Date | string | null;
  file: File;
  id: number;
  logs: Log[];
  markup: number;
  name: string;
  nights: number;
  notes: string[];
  operation: string;
  order_position: null;
  order_related: null;
  passengers: Passenger[];
  people: Person[];
  ranges: QuoteRange[];
  service_type_id: number;
  shared: number;
  show_in_popup: number;
  status: string;
  updated_at: null;
  user_id: number;
  language_id: number | null;
  flights: [];
  reference_code: string;
  reservation: [];
  statement: [];
  processing?: boolean;
}

export interface QuoteRange {
  id?: number;
  from: number;
  to: number;
  quote_id?: number;
  created_at?: Date | string;
  updated_at?: Date | string;
}

export interface Accommodation {
  id?: number;
  quote_id?: number;
  single: number;
  double: number;
  double_child: number;
  triple: number;
  triple_child: number;
}

export interface OpenQuoteCategory {
  tabActive?: string;
  checkAddService?: boolean;
  checkAddHotel?: boolean;
  checkAddExtension?: boolean;

  id: number;
  quote_id: number;
  services: QuoteService[] | GroupedServices[];
  type_class: TypeClass;
  type_class_id: number;
}

export interface QuoteService {
  adult: number;
  alerta_change_children_ages?: boolean;
  amount: Amount[];
  category: ServiceCategory;
  child: number;
  code_flight: null | string;
  created_at: Date;
  date_flight: null;
  date_in: Date | string;
  date_in_format: Date;
  date_out: Date | string;
  destiny: Destiny | null;
  double: number;
  double_child: number;
  extension_id: null;
  group: null | string;
  group_quote_service_id?: number;
  grouped_code: null | string;
  grouped_show: boolean;
  grouped_type: GroupedType | null;
  hotel: HotelService | null;
  hour_in: null | string;
  id: number;
  import?: QuoteServiceServiceImport | number;
  import_amount?: ImportAmount;
  infant: number;
  locked: boolean;
  nights: number;
  notes: null;
  object_id: number;
  on_request: number;
  optional: number;
  order: number;
  origin: Destiny | null;
  package_extension: null;
  parent_service_id: null;
  passengers: PurplePassenger[];
  passengers_front: Passenger[];
  quote_category_id: number;
  schedule_id: null;
  selected?: boolean;
  service: ServiceService | null;
  service_rate: ServiceRate | null;
  service_rooms: ServiceRoom[];
  single: number;
  total_accommodations: number;
  triple: number;
  triple_active: number;
  triple_child: number;
  type: ServiceTypeEnum;
  type_room_id?: number;
  updated_at: Date;
  validations: Validation[];
  isNew?: boolean;
  isUpdated?: boolean;
  isError?: boolean;
  errorDetail?: string;
  isLoading?: boolean;
}
export interface GroupedServices {
  type: ServiceTypeEnum;
  service: QuoteService;
  group: QuoteService[];
  selected: boolean;
}

export interface Amount {
  id: number;
  quote_service_id: number;
  date_service: string;
  price_per_night_without_markup: number;
  price_per_night: number;
  price_adult_without_markup: number;
  price_adult: number;
  price_child_without_markup: number;
  price_child: number;
  created_at: Date;
  updated_at: Date;
  price_teenagers_without_markup: number;
  price_teenagers: number;
}

export interface ServiceCategory {
  id: number;
  quote_id: number;
  type_class_id: number;
  created_at: Date;
  updated_at: Date | null;
}

export enum Destiny {
  Cus = 'CUS',
  Lim = 'LIM',
  Mpi = 'MPI',
  Uru = 'URU',
}

export enum GroupedType {
  Header = 'header',
  Row = 'row',
}

export interface HotelService {
  allows_child: number;
  allows_teenagers: number;
  chain_id: number;
  channel: Channel[];
  check_in_time: string;
  check_out_time: string;
  city: City;
  city_id: number;
  country: Country;
  country_id: number;
  created_at: Date;
  currency_id: number;
  date_end_flag_new: Date | null;
  deleted_at: null;
  district: City | null;
  district_id: number | null;
  flag_new: number;
  hotel_type_id: number;
  hotelcategory_id: number;
  id: number;
  latitude: number;
  longitude: number;
  max_age_child: number | null;
  max_age_teenagers: number;
  min_age_child: number | null;
  min_age_teenagers: number;
  name: string;
  notes: null;
  order: number;
  percentage_completion: number;
  preferential: number;
  rate_order: number;
  stars: string;
  state: City;
  state_id: number;
  status: number;
  typeclass_id: number;
  typeclass: HotelTypeClass;
  updated_at: Date;
  web_site: string;
  zone_id: number | null;
}

export interface HotelTypeClass {
  id: number;
  color: string;
  code: string;
  translations: HotelTypeClassTranslation[];
}
export interface HotelTypeClassTranslation {
  id: number;
  value: Value;
  object_id: number;
}

export interface Channel {
  id: number;
  code: null | string;
  state: number;
  hotel_id: number;
  channel_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
}

export interface City {
  id: number;
  state_id?: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  iso: Destiny | null;
  translations: CityTranslation[];
  city_id?: number;
  country_id?: number;
}

export interface CityTranslation {
  id: number;
  type: TranslationType;
  object_id: number;
  slug: Slug;
  value: string;
  language_id: number;
  created_at: Date;
  updated_at: Date | null;
  deleted_at: null;
}

export enum Slug {
  CityName = 'city_name',
  CountryName = 'country_name',
  DistrictName = 'district_name',
  MealName = 'meal_name',
  RoomDescription = 'room_description',
  RoomName = 'room_name',
  StateName = 'state_name',
}

export enum TranslationType {
  City = 'city',
  Country = 'country',
  District = 'district',
  Meal = 'meal',
  Room = 'room',
  State = 'state',
}

export interface Country {
  id: number;
  iso: ISO;
  iso_ifx: ISO;
  local_tax: number;
  foreign_tax: number;
  local_service: number;
  foreign_service: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: CityTranslation[];
}

export enum ISO {
  PE = 'PE',
}

export interface QuoteServiceServiceImport {
  price_ADL: number | string;
  total_amount_adult: number;
  total_amount_child: number;
  import_childres: ImportChildren[];
  sub_total: number;
  total_taxes: number;
  total_amount: number;
  price_per_person: number;
}
export interface ImportChildren {
  age: number | string;
  price: number | string;
}

export interface ImportAmount {
  price_ADL: number;
  adult: number;
  child: number;
  deta: Deta[];
  subtotal: number;
  taxes: number;
  total: number;
  price_per_person: string;
}

export interface Deta {
  date: string;
  adult: number;
  child: number;
  subTotal: number;
}

export interface PurplePassenger {
  id: number;
  created_at: Date;
  updated_at: Date | null;
  quote_service_id: number;
  quote_passenger_id: number;
  passenger: Passenger;
  type?: PassengerType;
  age?: number;
  selected?: boolean;
}

export interface Passenger {
  id?: number | null;
  first_name: string;
  last_name: string;
  gender: string;
  birthday: null;
  document_number: null;
  doctype_iso: string;
  country_iso: string;
  city_ifx_iso?: null;
  email: null;
  phone: null;
  notes: null;
  created_at?: Date;
  updated_at?: null | Date;
  quote_id?: number;
  address?: null;
  dietary_restrictions?: null;
  medical_restrictions?: null;
  type: string;
  is_direct_client?: boolean;
  quote_age_child_id?: null;
  quote_passenger_id?: null;
  age_child?: PassengerAgeChild;
  checked?: boolean;
  index?: number;
}
export interface PassengerAgeChild {
  id?: number;
  age: number;
  quote_id: number;
  created_at?: Date;
  updated_at?: Date;
  quote_age_child_id?: number;
}

export enum PassengerType {
  Adl = 'ADL',
}

export interface ServiceService {
  id: number;
  aurora_code: string;
  name: string;
  equivalence_aurora: string;
  qty_reserve_client: number | null;
  qty_reserve: number;
  allow_child: number;
  allow_infant: number;
  infant_min_age: number;
  infant_max_age: number;
  unit_id: number;
  unit_duration_id: number;
  service_type_id: number;
  classification_id: number;
  service_sub_category?: ServiceSubCategory;
  service_sub_category_id: number;
  duration: number;
  pax_min: number;
  pax_max: number;
  type: ServiceTypeEnum;
  status: number;
  notes: null | string;
  deleted_at: null;
  service_destination: Service[];
  service_origin: Service[];
  children_ages: ChildrenAge[];
  service_type: ServiceType;
  service_translations: ServiceTranslation[];
  schedules: Schedule[];
}

export interface ServiceSubCategory {
  service_category_id: number;
}

export interface ChildrenAge {
  id: number;
  min_age: number;
  max_age: number;
  service_id: number;
  status: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
}

export interface Schedule {
  id: number;
  service_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  services_schedule_detail: ServicesScheduleDetail[];
}

export interface ServicesScheduleDetail {
  id: number;
  service_schedule_id: number;
  type: ServicesScheduleDetailType;
  monday: null | string;
  tuesday: null | string;
  wednesday: null | string;
  thursday: null | string;
  friday: null | string;
  saturday: null | string;
  sunday: null | string;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
}

export enum ServicesScheduleDetailType {
  F = 'F',
  I = 'I',
}

export interface Service {
  id: number;
  service_id: number;
  country_id: number;
  state_id: number;
  city_id: number;
  zone_id: number | null;
}

export interface ServiceTranslation {
  id: number;
  name: string;
  name_commercial: string;
  description: string;
  description_commercial: string;
  itinerary: string;
  itinerary_commercial: string;
  summary: string;
  summary_commercial: string;
  service_id: number;
}

export interface ServiceType {
  id: number;
  code: Abbreviation;
  abbreviation: Abbreviation;
  translations: ServiceTypeTranslation[];
}

export enum Abbreviation {
  Na = 'NA',
  PC = 'PC',
  Sim = 'SIM',
}

export interface ServiceTypeTranslation {
  id: number;
  value: Value;
  object_id: number;
}

export enum Value {
  Comfort = 'Comfort',
  None = 'None',
  Private = 'Private',
  Shared = 'Shared',
  Standard = 'Standard',
}

export enum ServiceTypeEnum {
  Flight = 'flight',
  GroupHeader = 'group_header',
  Hotel = 'hotel',
  Service = 'service',
  GroupTypeRoom = 'group_type_room',
}

export interface ServiceRate {
  id: number;
  quote_service_id: number;
  service_rate_id: number;
  created_at: Date;
  updated_at: Date;
}

export interface ServiceRoom {
  id: number;
  quote_service_id: number;
  on_request: number;
  rate_plan_room_id: number;
  created_at: Date;
  updated_at: Date;
  rate_plan_room: RatePlanRoom;
  available_rooms: AvailableRoom[];
}

export interface AvailableRoom {
  available: number;
  date: Date;
  locked: number;
}

export interface RatePlanRoom {
  id: number;
  rates_plans_id: number;
  room_id: number;
  status: number;
  created_at: Date;
  updated_at: Date;
  bag: number;
  channel_id: number;
  channel_infant_price: number;
  channel_child_price: number;
  deleted_at: null;
  calendarys: Calendary[];
  rate_plan: RatePlan;
  room: Room;
  rates_plans_type_id: number;
}

export interface Calendary {
  id: number;
  date: Date;
  policies_rate_id: number;
  status: number;
  rates_plans_room_id: number;
  created_at: Date | null;
  updated_at: null;
  deleted_at: null;
  max_ab_offset: null;
  min_ab_offset: null;
  min_length_stay: null;
  max_length_stay: null;
  max_occupancy: null;
  policies_cancelation_id: null;
  rate: Rate[];
}

export interface Rate {
  id: number;
  rates_plans_calendarys_id: number;
  num_adult: number;
  num_child: number;
  num_infant: number;
  price_adult: string;
  price_child: string;
  price_infant: string;
  price_extra: string;
  price_total: string;
  created_at: Date | null;
  updated_at: null;
  deleted_at: null;
}

export interface RatePlan {
  id: number;
  code: string;
  name: Name;
  allotment: boolean;
  taxes: boolean;
  services: boolean;
  timeshares: number;
  promotions: boolean;
  status: number;
  meal_id: number;
  rates_plans_type_id: number;
  charge_type_id: number;
  hotel_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  rate_plan_code: null;
  channel_id: number;
  promotion_from: null;
  promotion_to: null;
  rate: boolean;
  no_show: NoShow | null;
  day_use: DayUse | null;
  flag_process_markup: number | null;
  meal: Meal;
}

export enum DayUse {
  ContacteASuEspecialistaParaMayorDetalle = 'Contacte a su especialista para mayor detalle',
}

export interface Meal {
  id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: CityTranslation[];
}

export enum Name {
  NameTarifaRegularFITSGROUPS = 'Tarifa regular FITS & GROUPS',
  TarifaRegularFITSGROUPS = 'Tarifa Regular FITS & GROUPS',
  TarifaRegularFITSGrupo = 'Tarifa Regular FITS/Grupo',
  TarifaRegularFITSGrupoSerie = 'Tarifa Regular FITS/Grupo/Serie',
}

export enum NoShow {
  The100TaxesApply = '100% + taxes apply',
}

export interface Room {
  id: number;
  max_capacity: number;
  min_adults: number;
  max_adults: number;
  max_child: number;
  max_infants: number;
  min_inventory: number;
  state: number;
  see_in_rates: number;
  hotel_id: number;
  room_type_id: number;
  order: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  estela_id: number | null;
  inventory: number;
  bed_additional: number;
  room_type: RoomType;
  translations: CityTranslation[];
}

export interface RoomType {
  id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  occupation: number;
  type_room_id: number;
}

export interface Validation {
  error: Error;
  range: string;
  validation: boolean;
}

export enum Error {
  ErrorPrice = 'Error price',
  TheHotelHasObservationsInTheRooms = 'The hotel has observations in the rooms',
  YouMustAssignThePassengersToTheRoom = 'You must assign the passengers to the room',
}

export interface TypeClass {
  id: number;
  color: string;
  translations: ServiceTypeTranslation[];
}

export interface EditingQuoteUser {
  editing: boolean;
  user: null;
}

export interface File {
  file_code: null;
  file_reference: null;
  client: Client;
  type_class_id: null;
}

export interface Client {
  id: null;
  code: null;
  name: null;
}

export interface Log {
  id: number;
  quote_id: number;
  type: string;
  object_id: number;
  user_id: null;
}

export interface Person {
  id: number;
  adults: number;
  child: number;
  infant?: number;
  quote_id: number;
}
