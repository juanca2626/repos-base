export interface ServicesAvailableResponse {
  data: Service[];
  count: number;
  success: boolean;
}

export interface Service {
  id: number;
  aurora_code: string;
  name: string;
  currency_id: number;
  latitude: number;
  longitude: number;
  qty_reserve: number;
  equivalence_aurora: string;
  affected_igv: number;
  affected_markup: number;
  affected_schedule: number;
  allow_guide: number;
  allow_child: number;
  allow_infant: number;
  limit_confirm_hours: number;
  unit_duration_limit_confirmation: number;
  infant_min_age: number;
  infant_max_age: number;
  include_accommodation: number;
  unit_id: number;
  unit_duration_id: number;
  unit_duration_reserve: number;
  service_type_id: number;
  classification_id: number;
  service_sub_category_id: number;
  user_id: number;
  date_solicitude: Date | string | string;
  duration: number;
  pax_min: number;
  pax_max: number;
  min_age: number;
  require_itinerary: number;
  require_image_itinerary: number;
  physical_intensity_id: number;
  type: Type;
  status: number;
  exclusive: number;
  compensation: number;
  exclusive_client_id: null;
  notes: null | string;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  status_ifx: StatusIfx;
  pax_max_ifx: number;
  language_iso_ifx: LanguageISOIfx;
  description_ifx: string;
  type_ifx: TypeIfx;
  tag_service_id: null;
  rate_order: number;
  qty_reserve_client: number | null;
  service_origin: Service[];
  service_destination: Service[];
  service_type: ServicesType;
  service_sub_category: ServiceSubCategory;
  experience: Experience[];
  service_rate: ServiceRate[];
  service_translations: ServiceTranslation[];
  markup_service: any[];
}

export interface Experience {
  id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  color: Color;
  pivot: Pivot;
  translations: ExperienceTranslation[];
}

export enum Color {
  The04B5Aa = '#04B5AA',
}

export interface Pivot {
  service_id: number;
  experience_id: number;
  created_at: Date | string;
  updated_at: Date | string;
}

export interface ExperienceTranslation {
  object_id: number;
  value: PurpleValue;
}

export enum PurpleValue {
  HistoryAndLiving = 'HISTORY AND LIVING',
}

export enum LanguageISOIfx {
  En = 'EN',
}

export interface Service {
  id: number;
  service_id: number;
  country_id: number;
  state_id: number;
  city_id: number;
  zone_id: null;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  state: State;
  zone: null;
}

export interface State {
  id: number;
  iso: ISO;
  country_id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  translations: StateTranslation[];
}

export enum ISO {
  Cus = 'CUS',
}

export interface StateTranslation {
  id: number;
  value: FluffyValue;
  language_id: number;
  object_id: number;
}

export enum FluffyValue {
  Cusco = 'Cusco',
  Private = 'Private',
  Tour = 'TOUR',
}

export interface ServiceRate {
  id: number;
  name: Name;
  allotment: number;
  rate: number;
  taxes: number;
  services: number;
  advance_sales: number;
  promotions: number;
  status: number;
  service_id: number;
  service_type_rate_id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  flag_process_markup: null;
  inventory_count: number;
  service_rate_plans: ServiceRatePlan[];
  markup_rate_plan: any[];
}

export enum Name {
  Tarifa = 'Tarifa',
}

export interface ServiceRatePlan {
  id: number;
  service_rate_id: number;
  service_cancellation_policy_id: number;
  user_id: number;
  date_from: Date | string;
  date_to: Date | string;
  monday: number;
  tuesday: number;
  wednesday: number;
  thursday: number;
  friday: number;
  saturday: number;
  sunday: number;
  pax_from: number;
  pax_to: number;
  price_adult: string;
  price_child: string;
  price_infant: string;
  price_guide: string;
  status: number;
  deleted_at: null;
  created_at: Date | string;
  updated_at: Date | string;
  flag_migrate: number;
  price_adult_without_markup: string;
}

export interface ServiceSubCategory {
  id: number;
  service_category_id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  order: number;
  service_categories: ServiceCategories;
}

export interface ServiceCategories {
  id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  order: number;
  translations: StateTranslation[];
}

export interface ServiceTranslation {
  id: number;
  language_id: number;
  name: string;
  name_commercial: string;
  description: string;
  description_commercial: string;
  itinerary: string;
  itinerary_commercial: string;
  summary: null | string;
  summary_commercial: null | string;
  link_trip_advisor: null;
  service_id: number;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
}

export interface ServicesType {
  id: number;
  code: string;
  label?: string;
  abbreviation: string;
  created_at: Date | string;
  updated_at: Date | string;
  deleted_at: null;
  translations: StateTranslation[];
}

export enum StatusIfx {
  Ok = 'OK',
  The00 = '00',
}

export enum Type {
  Service = 'service',
}

export enum TypeIfx {
  Svs = 'SVS',
}
