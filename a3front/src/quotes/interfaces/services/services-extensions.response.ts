export interface ServiceExtensionsResponse {
  id: number;
  country_id: number;
  code: string;
  extension: string;
  nights: number;
  map_link: null | string;
  image_link: null | string;
  status: number;
  reference: null | string;
  rate_type: RateType;
  rate_dynamic: null | string;
  allow_guide: string;
  allow_child: string;
  allow_infant: string;
  limit_confirmation_hours: null;
  infant_min_age: number;
  infant_max_age: number;
  infant_discount_rate: number;
  physical_intensity_id: number;
  tag_id: number;
  allow_modify: number;
  free_sale: number;
  enable_fixed_prices: number;
  created_at: Date | null;
  updated_at: Date;
  deleted_at: null;
  recommended: number;
  destinations: string;
  user_id: number | null;
  package_destinations: PackageDestination[];
  tag: Tag;
  translations: ExtensionsResponseTranslation[];
  extension_recommended: ExtensionRecommended[];
  plan_rates: PlanRate[];
}

export interface ExtensionRecommended {
  id: number;
  package_id: number;
  extension_id: number;
}

export interface PackageDestination {
  id: number;
  package_id: number;
  state_id: number;
  created_at: Date;
  updated_at: Date;
  state: State;
}

export interface State {
  id: number;
  iso: string;
  country_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: StateTranslation[];
}

export interface StateTranslation {
  id: number;
  type: Type;
  object_id: number;
  slug: Slug;
  value: string;
  language_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
}

export enum Slug {
  StateName = 'state_name',
  TagName = 'tag_name',
  TypeclassName = 'typeclass_name',
}

export enum Type {
  State = 'state',
  Tag = 'tag',
  Typeclass = 'typeclass',
}

export interface PlanRate {
  id: number;
  package_id: number;
  code: number | null;
  name: string;
  date_from: Date;
  date_to: Date;
  status: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  service_type_id: number;
  enable_fixed_prices: number;
  plan_rate_categories: PlanRateCategory[];
}

export interface PlanRateCategory {
  id: number;
  package_plan_rate_id: number;
  type_class_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: null;
  category: Category;
}

export interface Category {
  id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  code: string;
  order: number;
  color: string;
  translations: StateTranslation[];
}

export enum RateType {
  D = 'D',
  F = 'F',
}

export interface Tag {
  id: number;
  tag_group_id: number;
  color: Color;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: StateTranslation[];
}

export enum Color {
  The770E7F = '770E7F',
}

export interface ExtensionsResponseTranslation {
  id: number;
  name: string;
  tradename: string;
  description: string;
  description_commercial: string;
  label: null;
  itinerary_link: string;
  itinerary_link_commercial: string;
  itinerary_label: ItineraryLabel;
  itinerary_description: string;
  itinerary_commercial: string;
  inclusion: null;
  restriction: null;
  restriction_commercial: null;
  policies: null;
  policies_commercial: null;
  language_id: number;
  package_id: number;
  created_at: Date | null;
  updated_at: Date;
  deleted_at: null;
}

export enum ItineraryLabel {
  Itinerary2023 = 'Itinerary 2023',
}
