export interface QuoteHotelsResponse {
  success: boolean;
  data: Datum[];
  expiration_token: number;
}

export interface Datum {
  city: CityClass;
}

export interface CityClass {
  token_search: string;
  token_search_frontend: string;
  ids: string;
  description: string;
  class: ClassElement[];
  zones: ZoneElement[];
  hotels: Hotel[];
  search_parameters: SearchParameters;
  quantity_hotels: number;
  min_price_search: string;
  max_price_search: string;
}

export interface ClassElement {
  class_name: ClassNameEnum;
  status: boolean;
}

export enum ClassNameEnum {
  Boutique = 'Boutique',
  Comfort = 'Comfort',
  FirstClass = 'First Class',
  LogdeResort = 'Logde - Resort',
  Luxury = 'Luxury',
  Standard = 'Standard',
  SuperLuxury = 'Super Luxury',
  Superior = 'Superior',
}

export interface Hotel {
  address: string;
  amenities: Amenity[];
  best_option_cart_items_id: number[];
  best_option_taken: boolean;
  best_options: BestOptionsClass[];
  category: number;
  chain: string;
  checkIn: string;
  checkOut: string;
  city: CityEnum;
  class: ClassNameEnum;
  code: string;
  color_class: ColorClass;
  order_class: number;
  coordinates: Coordinates;
  country: Country;
  date_end_flag_new: Date | null;
  description: string;
  district: CityEnum;
  favorite: number;
  flag_new: number | null;
  galleries: string[];
  id: number;
  logo: string;
  name: string;
  notes: string;
  political_children: PoliticalChildren;
  popularity: number;
  price: string;
  rooms: Room[];
  state: CityEnum;
  summary: string;
  type: HotelType;
  zone: ZoneEnum;
}

export interface Amenity {
  name: string;
  image: string;
}

export interface BestOptionsClass {
  quantity_rooms: number;
  quantity_adults: number;
  quantity_child: number;
  total_taxes_and_services_amount: string;
  total_supplements_amount: string;
  total_sub_rate_amount: string;
  total_rate_amount: string;
  rooms: Room[];
}

export enum CityEnum {
  Barranco = 'Barranco',
  Huaura = 'Huaura',
  Lima = 'Lima',
  Lince = 'Lince',
  MagdalenaDelMar = 'Magdalena del Mar',
  Miraflores = 'Miraflores',
  SANBorja = 'San Borja',
  SANIsidro = 'San Isidro',
}

export enum ColorClass {
  Ce3B4D = '#CE3B4D',
  Ddca1F = '#DDCA1F',
  The04B5Aa = '#04B5AA',
  The07A0E9 = '#07a0e9',
  The4A90E2 = '#4A90E2',
  The4Bc910 = '#4bc910',
  The85165F = '#85165F',
  The979797 = '#979797',
}

export interface Coordinates {
  latitude: number;
  longitude: number;
}

export enum Country {
  Perú = 'Perú',
}

export interface PoliticalChildren {
  child: Child;
  infant: Infant;
}

export interface Child {
  allows_child: number;
  min_age_child: number | null;
  max_age_child: number | null;
}

export interface Infant {
  allows_teenagers: number;
  min_age_teenagers: number | null;
  max_age_teenagers: number | null;
}

export interface Room {
  countCalendars?: number;

  bed_additional: number;
  best_price: string;
  description: string;
  gallery: string[];
  max_adults: number;
  max_capacity: number;
  max_child: number;
  name: string;
  occupation: number;
  rates: RoomRate[];
  room_id: number;
  room_type: RoomTypeEnum;
  room_type_id: number;
}

export interface RoomRate {
  showAllRates?: number;
  selected?: boolean;

  available: number;
  avgPrice: string;
  cart_items_id: number[];
  day_use: RateDayUse;
  description: string;
  disabled_buttons: boolean;
  meal_id: number;
  meal_name: MealName;
  message_error: string;
  name: PurpleName;
  name_commercial: string;
  no_show: RateNoShow;
  notes: string;
  onRequest: number;
  political: Political;
  promotions_data: PromotionsDatum[];
  quantity_rates: number;
  rate: RateRate[];
  rateId: number;
  ratePlanId: number;
  rateProvider: RateProvider;
  rates_plans_type_id: number;
  show_message_error: boolean;
  supplements: Supplements;
  taken: boolean;
  taxes_and_services: TaxesAndServices;
  total: string;
  total_taxes_and_services: string;
}

export enum RateDayUse {
  PleaseContactYourSpecialistForMoreDetails = 'Please contact your specialist for more details',
}

export enum MealName {
  BoxBreakfast = 'Box breakfast',
  Desayuno = 'Desayuno',
}

export enum PurpleName {
  NameRegularRateFITS = 'Regular rate FITS',
  NameRegularRateFITSGroups = 'Regular Rate FITS/Groups',
  RegularRate = 'Regular Rate',
  RegularRateFITS = 'Regular Rate FITS',
  RegularRateFITSGROUPS = 'Regular Rate FITS & GROUPS',
  RegularRateFITSGroups = 'Regular Rate FITS & Groups',
  RegularRateFITSYGROUPS = 'Regular Rate FITS Y GROUPS',
  RegularRateFits = 'Regular Rate Fits',
  RegularRateFitsGroup = 'Regular Rate Fits & Group',
  RegularRateFitsGroups = 'Regular Rate Fits & Groups',
  RegularRateForGuide = 'Regular rate for guide',
  SpecialOffer = 'Special Offer',
  SpecialOffer2023 = 'Special Offer 2023',
  SpecialOfferBWTW = 'Special Offer BW/TW',
  SpecialRate = 'Special Rate',
  SpecialRateFITS = 'Special rate FITS',
  SpecialRateFits = 'Special Rate Fits',
  TarifaRegularFitsHyperguest = 'Tarifa Regular Fits - Hyperguest',
}

export enum RateNoShow {
  The100Fee18Tax = '100% fee + 18% Tax',
}

export interface Political {
  rate: PoliticalRate;
  cancellation: Cancellation;
  no_show_apply: NoShowApply;
}

export interface Cancellation {
  name: string;
  details: Detail[];
}

export interface Detail {
  to: number;
  from: number;
  amount: number;
  tax: number;
  service: number;
  penalty: RateProvider;
}

export enum RateProvider {
  Aurora = 'Aurora',
  Hyperguest = 'HYPERGUEST',
  Nigth = 'nigth',
  Percentage = 'percentage',
  SiteMinder = 'SiteMinder',
  TotalReservation = 'total_reservation',
  TravelClick = 'TravelClick',
}

export interface NoShowApply {
  executive: number;
  political_child: PoliticalChild;
  message: string;
}

export enum PoliticalChild {
  Free = 'Free',
}

export interface PoliticalRate {
  name: PoliciesRatesName;
  message: string;
  max_occupancy: number | null;
  example: Example;
}

export interface Example {
  id: number;
  rates_plans_id: number;
  room_id: number;
  status: number;
  bag: number;
  channel_id: number;
  channel_child_price: number;
  channel_infant_price: number;
  inventories: Inventory[];
  channel: Channel;
  policies_cancelation: CalendaryPoliciesCancelation[];
  descriptions: any[];
  calendarys: Calendary[];
  rate_plan: RatePlan;
  bag_rate?: any[] | BagRateClass;
  markup?: null;
  created_at?: Date;
  updated_at?: Date | null;
  deleted_at?: null;
  room?: ExampleRoom;
}

export interface BagRateClass {
  id: number;
  bag_room_id: number;
  rate_plan_rooms_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  bag_room: BagRoom;
}

export interface BagRoom {
  id: number;
  bag_id: number;
  room_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  inventory_bags: Inventory[];
}

export interface Inventory {
  id: number;
  day: number;
  date: Date;
  inventory_num: number;
  total_booking: number;
  total_canceled: number;
  locked: number;
  bag_room_id?: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
  rate_plan_rooms_id?: number;
}

export interface Calendary {
  id: number;
  date: Date;
  policies_rate_id: number;
  status: number;
  rates_plans_room_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
  max_ab_offset: null;
  min_ab_offset: null;
  min_length_stay: null;
  max_length_stay: null;
  max_occupancy: null;
  policies_cancelation_id: number | null;
  policies_rates: PoliciesRates;
  policies_cancelation?: CalendaryPoliciesCancelation | null;
  rate: OldRateElement[];
  old_rate?: OldRateElement[];
}

export interface OldRateElement {
  id: number;
  rates_plans_calendarys_id: number;
  num_adult: number | null;
  num_child: number | null;
  num_infant: number | null;
  price_adult: string;
  price_child: string;
  price_infant: string;
  price_extra: string;
  price_total: string;
  created_at: Date | null;
  updated_at: null;
  deleted_at: null;
}

export interface CalendaryPoliciesCancelation {
  id: number;
  name: PoliciesCancelationName;
  hotel_id: number;
  type_fit: number;
  min_num: number;
  max_num: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  status: number;
  is_channel: number;
  code: null;
  type: PoliciesCancelationType;
  policy_cancellation_parameter: PolicyCancellationParameter[];
  pivot?: PurplePivot;
}

export enum PoliciesCancelationName {
  PolíticaDeCancelaciónFits = 'Política de cancelación (Fits)',
}

export interface PurplePivot {
  rates_plans_rooms_id: number;
  policies_cancelation_id: number;
}

export interface PolicyCancellationParameter {
  id: number;
  min_day: number;
  max_day: number;
  penalty_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  amount: number | null;
  tax: number;
  service: number;
  policy_cancelation_id: number;
  penalty: Channel;
}

export interface Channel {
  id: number;
  name: RateProvider;
  status: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
  code?: Code;
  pivot?: ChannelPivot;
}

export enum Code {
  Aurora = 'AURORA',
  Hyperguest = 'HYPERGUEST',
  Siteminder = 'SITEMINDER',
  Travelclick = 'TRAVELCLICK',
}

export interface ChannelPivot {
  room_id: number;
  channel_id: number;
  code: string;
  state: number;
  created_at: Date | null;
  updated_at: Date | null;
}

export enum PoliciesCancelationType {
  Cancellations = 'cancellations',
}

export interface PoliciesRates {
  id: number;
  name: PoliciesRatesName;
  description: null;
  status: number;
  days_apply: TypeclassID;
  max_ab_offset: number;
  min_ab_offset: number;
  min_length_stay: number;
  max_length_stay: number;
  max_occupancy: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  hotel_id: number;
  policies_cancelation: PoliciesRatesPoliciesCancelation[];
  translations?: TranslationsDayUseElement[];
}

export enum TypeclassID {
  All = 'all',
  The56 = '5|6',
  The567 = '5|6|7',
}

export enum PoliciesRatesName {
  CancelacionPromocional2022 = 'Cancelacion Promocional 2022',
  NamePoliticaGeneral = 'Politica General.',
  NamePoliticaGeneralVD = 'Politica General  (V-D)',
  NamePolíticaGeneral = 'Política general',
  NamePolíticaGeneralFits = 'Política General Fits',
  NamePolíticasGeneralesFits = 'Políticas Generales -Fits',
  PoliticaGeneral = 'Politica General',
  PoliticaGeneral2023 = 'Politica General 2023',
  PoliticaGeneralFits = 'Politica General Fits',
  PoliticaGeneralVD = 'Politica General (V-D)',
  PoliticasGeneral = 'Politicas General.',
  PoliticasGeneral2023 = 'Politicas General 2023',
  PoliticasGenerales = 'Politicas generales',
  PolíticaDeTarifaPromocional = 'Política de tarifa Promocional',
  PolíticaGeneral = 'Política General',
  PolíticaGeneral2023 = 'Política General 2023',
  PolíticaGeneralFits = 'Política General - Fits',
  PolíticaGeneralVS = 'Política General (V-S)',
  PolíticaGeneralVYS = 'Política General (V y S)',
  PolíticaTarifa2024 = 'Política tarifa 2024',
  PolíticaTarifaGeneralFitsGrupos = 'Política Tarifa General Fits/Grupos',
  PolíticaTarifaPromocional = 'Política Tarifa Promocional',
  PolíticasGenerales = 'Políticas generales',
  PolíticasGeneralesFits = 'Políticas Generales Fits',
  PurplePolíticaGeneral = 'Política General.',
  PurplePolíticasGeneralesFits = 'Políticas Generales - Fits',
}

export interface PoliciesRatesPoliciesCancelation {
  id: number;
  name: string;
  hotel_id: number;
  type_fit: number;
  min_num: number;
  max_num: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  status: number;
  is_channel: number;
  code: null;
  type: PoliciesCancelationType;
  pivot: FluffyPivot;
  policy_cancellation_parameter: PolicyCancellationParameter[];
}

export interface FluffyPivot {
  policies_rates_id: number;
  policies_cancelation_id: number;
}

export interface TranslationsDayUseElement {
  id: number;
  type: TranslationType;
  object_id: number;
  slug: TranslationsDayUseSlug;
  value: string;
  language_id: number;
  created_at: Date | null;
  updated_at: Date;
  deleted_at: null;
}

export enum TranslationsDayUseSlug {
  CommercialName = 'commercial_name',
  DayUse = 'day_use',
  MealName = 'meal_name',
  NoShow = 'no_show',
  Notes = 'notes',
  PolicyDescription = 'policy_description',
}

export enum TranslationType {
  Meal = 'meal',
  RatePolicies = 'rate_policies',
  RatesPlan = 'rates_plan',
}

export interface RatePlan {
  id: number;
  code: string;
  name: string;
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
  no_show: RatePlanNoShow | null;
  day_use: RatePlanDayUse | null;
  flag_process_markup: number | null;
  translations: TranslationsDayUseElement[];
  translations_no_show: TranslationsDayUseElement[];
  translations_day_use: TranslationsDayUseElement[];
  translations_notes: TranslationsDayUseElement[];
  meal: Meal;
  markup: null;
  promotions_data?: PromotionsDatum[];
}

export enum RatePlanDayUse {
  ContacteASuEspecialistaParaMayorDetalle = 'Contacte a su especialista para mayor detalle',
}

export interface Meal {
  id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: TranslationsDayUseElement[];
}

export enum RatePlanNoShow {
  The100TaxesApply = '100% + taxes apply',
}

export interface PromotionsDatum {
  rates_plans_id: number;
  promotion_from: Date;
  promotion_to: Date;
  created_at: Date;
  updated_at: Date;
}

export interface ExampleRoom {
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
  galeries: Galery[];
  channels: Channel[];
  room_type: RoomTypeClass;
  translations: RoomTranslation[];
}

export interface Galery {
  object_id: number;
  url: string;
}

export interface RoomTypeClass {
  id: number;
  occupation: number;
  translations: RoomTypeTranslation[];
}

export interface RoomTypeTranslation {
  object_id: number;
  value: RoomTypeEnum;
}

export enum RoomTypeEnum {
  DoubleSuite = 'Double Suite',
  EstándarDoble = 'Estándar Doble',
  EstándarSimple = 'Estándar Simple',
  EstándarTriple = 'Estándar Triple',
  JuniorSuiteDoble = 'Junior Suite Doble',
  JuniorSuiteDouble = 'Junior Suite Double',
  JuniorSuiteSimple = 'Junior Suite Simple',
  JuniorSuiteTriple = 'Junior Suite Triple',
  SimpleStandard = 'Simple Standard',
  SimpleSuite = 'Simple Suite',
  StandardDouble = 'Standard Double',
  SuiteDoble = 'Suite Doble',
  SuiteSimple = 'Suite Simple',
  SuiteTriple = 'Suite Triple',
  SuperiorDoble = 'Superior Doble',
  SuperiorDouble = 'Superior Double',
  SuperiorSimple = 'Superior Simple',
  SuperiorTriple = 'Superior Triple',
  TripleStandard = 'Triple Standard',
}

export interface RoomTranslation {
  object_id: number;
  value: string;
  slug: PurpleSlug;
}

export enum PurpleSlug {
  RoomDescription = 'room_description',
  RoomName = 'room_name',
}

export interface RateRate {
  total_amount: string;
  total_taxes_and_services: string;
  avgPrice: string;
  quantity_adults: number;
  quantity_child: number;
  ages_child: any[];
  quantity_extras: number;
  quantity_adults_total: number;
  quantity_child_total: number;
  quantity_extras_total: number;
  total_amount_adult: number;
  total_amount_child: number;
  total_amount_infants: number;
  total_amount_extras: number;
  people_coverage: number;
  quantity_inventory_taken: number;
  amount_days: AmountDay[];
}

export interface AmountDay {
  id: number;
  date: Date;
  policies_rate_id: number;
  status: number;
  rates_plans_room_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
  max_ab_offset: null;
  min_ab_offset: null;
  min_length_stay: null;
  max_length_stay: null;
  max_occupancy: null;
  policies_cancelation_id: number | null;
  policies_rates: PoliciesRates;
  policies_cancelation?: CalendaryPoliciesCancelation | null;
  rate: PurpleRate[];
  total_adult: string;
  total_child: string;
  total_extra: string;
  total_amount: string;
  total_adult_base: string;
  total_child_base: string;
  total_extra_base: string;
  total_amount_base: string;
  old_rate?: OldRateElement[];
}

export interface PurpleRate {
  id: number;
  rates_plans_calendarys_id: number;
  num_adult: number | null;
  num_child: number | null;
  num_infant: number | null;
  price_adult: string;
  price_child: string;
  price_infant: string;
  price_extra: string;
  price_total: string;
  created_at: Date | null;
  updated_at: null;
  deleted_at: null;
  price_adult_base: string;
  total_adult: string;
  total_adult_base: string;
  price_child_base: string;
  total_child: string;
  total_child_base: string;
  price_extra_base: string;
  total_extra: string;
  total_extra_base: string;
  total_amount: string;
  total_amount_base: string;
}

export interface Supplements {
  total_amount: number;
  supplements: any[];
  supplements_optional: any[];
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

export enum HotelType {
  ApartHotel = 'Apart-hotel',
  BoutiqueHotel = 'Boutique hotel',
  CityHotel = 'City Hotel',
}

export enum ZoneEnum {
  CentroHistórico = 'Centro Histórico',
  Empty = '',
  HistoricalCenter = 'Historical Center',
  KennedyPark = 'Kennedy Park',
  Larcomar = 'Larcomar.',
  ParqueKennedy = 'Parque Kennedy',
  SANIsidroBusinessCenter = 'San Isidro Business Center',
}

export interface SearchParameters {
  destiny: Destiny;
  date_from: Date;
  date_to: Date;
  typeclass_id: TypeclassID;
  quantity_persons_rooms: any[];
  promotional_rate: number;
}

export interface Destiny {
  code: string;
  label: string;
}

export interface ZoneElement {
  zone_name: string;
  status: boolean;
}
