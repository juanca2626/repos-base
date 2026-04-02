export interface StatementsResponse {
  client: Client;
  flag_credit: number;
  type: string;
  quote: Quote;
  total: number;
  total_without_markup: number;
  category: Category;
  services: Service[];
  amounts: Array<Amount[]>;
  min_date: string;
  format_date: string;
  policies: Policies;
  min_date_cancellation: Date;
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

export interface Category {
  id: number;
  quote_id: number;
  type_class_id: number;
  created_at: Date;
  updated_at: Date;
}

export interface Client {
  code: string;
  email: string;
  name: string;
  business_name: string;
}

export interface Policies {
  selected_policy_cancelation: SelectedPolicyCancelation;
  next_penalty: NextPenaltyClass;
  penalties: NextPenaltyClass[];
}

export interface NextPenaltyClass {
  from: number;
  to: number;
  apply_date: string;
  expire_date: string;
  days_remain: number;
  penalty_price: string;
  penalty_code: string;
  message: string;
  policies_found: number;
  days_before_checkin: string;
}

export interface SelectedPolicyCancelation {
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
  type: string;
  pivot: Pivot;
  policy_cancellation_parameter: PolicyCancellationParameter[];
}

export interface Pivot {
  policies_rates_id: number;
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
  amount: number;
  tax: number;
  service: number;
  policy_cancelation_id: number;
  penalty: PolicyCancellationParameterPenalty;
}

export interface PolicyCancellationParameterPenalty {
  id: number;
  name: string;
  status: number;
  created_at: null;
  updated_at: null;
  deleted_at: null;
}

export interface Quote {
  id: number;
  code: null;
  name: string;
  date_in: Date;
  cities: null;
  nights: number;
  estimated_price: number;
  user_id: number;
  service_type_id: number;
  status: string;
  discount: null;
  discount_detail: null;
  discount_user_permission: null;
  order_related: null;
  order_position: null;
  show_in_popup: number;
  created_at: Date;
  updated_at: null;
  deleted_at: null;
  operation: string;
  markup: number;
  shared: number;
  estimated_travel_date: Date;
  language_id: null;
  package_id: number;
  people: Person[];
  date_out: Date;
}

export interface Person {
  id: number;
  adults: number;
  child: number;
  created_at: Date;
  updated_at: Date;
  quote_id: number;
}

export interface Service {
  id: number;
  quote_category_id: number;
  type: Type;
  object_id: number;
  order: number;
  date_in: string;
  date_out: string;
  hour_in: null;
  nights: number;
  adult: number;
  child: number;
  infant: number;
  single: number;
  double: number;
  double_child: number;
  triple: number;
  triple_child: number;
  triple_active: number;
  locked: boolean;
  created_at: Date;
  updated_at: Date;
  on_request: number;
  extension_id: null;
  parent_service_id: null;
  new_extension_id: null;
  new_extension_parent_id: null;
  optional: number;
  code_flight: null;
  origin: null;
  destiny: null;
  date_flight: null;
  notes: null;
  schedule_id: null;
  date_in_format: Date;
  amount: Amount[];
  service_rooms: ServiceRoom[];
}

export interface ServiceRoom {
  id: number;
  quote_service_id: number;
  rate_plan_room_id: number;
  created_at: Date;
  updated_at: Date;
  rate_plan_room: RatePlanRoom;
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
  policies_cancelation: any[];
}

export enum Type {
  Hotel = 'hotel',
  Service = 'service',
}
