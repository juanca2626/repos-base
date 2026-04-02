export interface CityHotelData {
  id: number;
  city: string;
  total_hotels: number;
  hyperguest: number;
  aurora: number;
  ambos: number;
}

export interface ChartDataItem {
  label: string;
  value: number;
  percentage?: number;
}

export interface FilterOption {
  label: string;
  code: string;
  originalId?: string; // ID original para usar en peticiones cuando el code incluye índice
  occupation?: number; // Occupation para tipos de habitación
}

export interface DateRange {
  from: string | null;
  to: string | null;
}

export interface HotelAvailabilityFilters {
  country: FilterOption | null;
  city: FilterOption | null;
  destination: FilterOption | null;
  internalCategory: FilterOption | null;
  connection: FilterOption | null;
  dateRange: DateRange;
}

// Interfaces para respuestas de API
export interface TypeClassResponse {
  id: number;
  code: string;
  color: string;
  order: number;
  checked: boolean;
  translations: Array<{
    object_id: number;
    value: string;
  }>;
}

export interface ChannelResponse {
  text: string;
  value: number;
}

export interface StateResponse {
  id: number;
  iso: string;
  country_id: number;
  translations: Array<{
    object_id: number;
    value: string;
  }>;
}

export interface DestinationsResponse {
  label: string;
  code: string;
}

export interface ChainResponse {
  label: string;
  code: number;
}

export interface StarsResponse {
  id: number;
  description: string;
}

export interface RoomTypeResponse {
  id: number;
  description: string;
  occupation: number;
}

// Interfaces para hotels-rooms-list
export interface HotelRoomDay {
  date: string;
  inventory_num: number;
  locked: number;
}

export interface HotelRoomPeriod {
  period: number;
  days: HotelRoomDay[];
}

export interface HotelRoomDetail {
  room_id: number;
  room_name: string;
  occupation: number;
  occupancy: string;
  rate_id: number;
  rate_name: string;
  channel_mark: string;
  days: HotelRoomPeriod[];
  price?: number | string | null; // Precio opcional de la tarifa
  rates_plans_type_id?: number; // ID del tipo de plan de tarifa
}

export interface HotelRoomHeader {
  period: string;
  days: string[];
}

export interface HotelRoomData {
  hotel_id: number;
  name: string;
  chain: string;
  category: string;
  preferente: number;
  channel_mark: string;
  inventory_status: string;
  inventory_status_porcent: string;
  cupos: number;
  header: HotelRoomHeader[];
  details: HotelRoomDetail[];
}

export interface HotelsRoomsListResponse {
  current_page: number;
  data: HotelRoomData[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

// Interfaces para el endpoint de disponibilidad por mes/día
export interface HotelAvailabilityDetail {
  hotel_id: number;
  hotel_name: string;
  typeclass_id: number;
  min_inventory: number;
  hotel_score: number;
  state_group: string;
}

export interface HotelAvailabilityPeriodData {
  date: string;
  typeclass_id: number;
  typeclass_color: string;
  typeclass_name: string;
  total_hoteles_procesados: number;
  score_del_periodo: number;
  cnt_score_0: string;
  cnt_score_1: string;
  cnt_score_2: string;
  cnt_score_3: string;
  cnt_score_4: string;
  cnt_score_5: string;
  agotados: string;
  minimas: string;
  disponibles: string;
  detalle: HotelAvailabilityDetail[];
}

export interface HotelAvailabilityChartResponse {
  success: string;
  data: HotelAvailabilityPeriodData[];
}

export interface HotelChartTotalsData {
  available_rooms: string;
  sold_out_rooms: string;
  blocked_rooms: string;
  total_count: string;
}

export interface HotelChartTotalsResponse {
  success: string;
  data: HotelChartTotalsData[];
}

export interface HotelsRoomsListTotalsResponse {
  success: string;
  data: Array<{
    total_hotels: number;
    total_rooms: number;
  }>;
}

// Interfaces para calendar-details
export interface CalendarDetailHotel {
  date: string;
  hotel_id: number;
  hotel_name: string;
  typeclass_id: number;
  available_rooms: string;
  sold_out_rooms: string;
  blocked_rooms: string;
  state_group: 'bloqueado' | 'disponible' | 'agotado';
  sold_out_percent: string;
  available_percent: string;
}

export interface CalendarDayTotals {
  available_hotels: number;
  sold_out_hotels: number;
  blocked_hotels: number;
  total_hotels: number;
}

export interface CalendarDayData {
  date: string;
  totals: CalendarDayTotals;
  details: CalendarDetailHotel[];
}

export interface CalendarDetailsResponse {
  success: string;
  data: CalendarDayData[];
}
