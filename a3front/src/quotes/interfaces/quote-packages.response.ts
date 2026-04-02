export interface PackageResponse {
  success: boolean;
  count: number;
  data: Package[];
}

export interface Package {
  id: number;
  language_id: number;
  available_from: AvailableFrom;
  date_reserve: Date;
  type_package: string;
  nights: number;
  map_link: null | string;
  favorite: boolean;
  allow_modify: number;
  allow_guide: number;
  allow_child: number;
  allow_infant: number;
  adult_age_from: number;
  infant_age_allowed: InfantAgeAllowed;
  children_age_allowed: ChildrenAgeAllowed;
  type_services: CategoryElement[];
  categories: CategoryElement[];
  physical_intensity: PhysicalIntensity;
  destinations: Destinations;
  tag: PhysicalIntensity;
  descriptions: Descriptions;
  inclusions: Inclusions;
  included_services: IncludedServices;
  itinerary_hotels: any[];
  fixed_outputs: any[];
  rated: number;
  quantity_adult: number;
  quantity_child: QuantityChild;
  rate: Rate;
  galleries: Gallery[];
  highlights: Highlight[];
  prices_children: PricesChildren;
  cancellation_policy: CancellationPolicy;
  flights: any[];
  amounts: Amounts;
  token_search: string;
  min_date_reserve: Date;
}

export interface Amounts {
  offer: boolean;
  offer_value: number;
  without_discount: number;
  price_per_person: number;
  price_per_adult: PricePerAdult;
  total_adults: number;
  total_children: PricePerChild;
  price_per_child: PricePerChild;
  total_amount: number;
}

export interface PricePerAdult {
  plan_rate_category_id: number;
  category_id: number;
  room_sgl: number;
  room_dbl: number;
  room_tpl: number;
  room_total: number;
}

export interface PricePerChild {
  with_bed: number;
  without_bed: number;
}

export interface AvailableFrom {
  from: number;
  unit_duration: string;
}

export interface CancellationPolicy {
  applies_cancellation_fees: boolean;
  cancellation_fees: number;
  last_day_cancel: null;
}

export interface CategoryElement {
  id: number;
  name: string;
  translations: Translation[];
}

export interface Translation {
  id: number;
  type: Type;
  object_id: number;
  slug: Slug;
  value: string;
  language_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
}

export enum Slug {
  ServicetypeName = 'servicetype_name',
  TypeclassName = 'typeclass_name',
}

export enum Type {
  Servicetype = 'servicetype',
  Typeclass = 'typeclass',
}

export interface ChildrenAgeAllowed {
  with_bed: ChildrenAgeAllowedWithBed;
  without_bed: ChildrenAgeAllowedWithBed;
}

export interface ChildrenAgeAllowedWithBed {
  min_age: number;
  max_age: number;
}

export interface Descriptions {
  name: string;
  description: string;
  itinerary: Itinerary[];
  restrictions: null;
  contact: null;
  cancellation_policies: null;
  itinerary_link: string;
  priceless_itinerary_link: string;
  itineraries_all: ItinerariesAll;
}

export interface ItinerariesAll {
  '2024': The2024[];
}

export interface The2024 {
  id: number;
  year: string;
  itinerary_link: string;
  itinerary_link_commercial: string;
  link_itinerary_priceless: string;
  package_id: number;
  language_id: number;
  created_at: Date;
  updated_at: Date;
}

export interface Itinerary {
  day: number;
  description: string;
}

export interface Destinations {
  destinations_display: string;
  destinations: Destination[];
}

export interface Destination {
  state: string;
}

export interface Gallery {
  url: string;
}

export interface Highlight {
  url: string;
  name: string;
}

export interface IncludedServices {
  breakfast_days: boolean;
  accommodation: any[];
  lunch_days: any[];
  dinner_days: any[];
  transport: boolean;
  includes_flights: boolean;
  include_guides_tickets: IncludeGuidesTickets;
}

export interface IncludeGuidesTickets {
  guides: boolean;
  tickets: boolean;
}

export interface Inclusions {
  include: any[];
  no_include: any[];
}

export interface InfantAgeAllowed {
  min: number;
  max: number;
}

export interface PhysicalIntensity {
  name: string;
  color: string;
}

export interface PricesChildren {
  with_bed: PricesChildrenWithBed;
  without_bed: PricesChildrenWithBed;
}

export interface PricesChildrenWithBed {
  price: number;
  min_age: number;
  max_age: number;
}

export interface QuantityChild {
  quantity_children: number;
  with_bed: number;
  without_bed: number;
}

export interface Rate {
  id: number;
  plan_rate_category_id: number;
  date_from: Date;
  date_to: Date;
  category: RateCategory;
  type_service: RateCategory;
}

export interface RateCategory {
  id: number;
  name: string;
}
