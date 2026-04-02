import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type {
  CountryCalendarItem,
  CountryCalendarListResponse,
} from '../interfaces/country-calendar.interface';

export interface CountryLocation {
  country_name: string;
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
  location_name: string;
}

const CALENDARS_URL = 'calendars';
const HOLIDAYS_URL = 'holidays'; // Changed: individual holiday operations use /holidays/{id}
const CLONE_URL = 'holidays/clone';

export interface CreateCalendarPayload {
  country_id: number;
  year_from: string; // YYYY-MM-DD
  year_to: string; // YYYY-MM-DD
}

export interface HolidayLocation {
  state_id?: number;
  city_id?: number;
  zone_id?: number;
  location_name?: string;
}

// Payload for creating/updating holidays - no calendar ID here, it's in the URL
export interface CreateHolidayPayload {
  name_holiday: string;
  date_from: string; // YYYY-MM-DD
  date_to: string; // YYYY-MM-DD
  type_calendar: 'GENERAL' | 'CITY' | 'TOURIST';
  type_value?: 'AMOUNT' | 'PERCENTAGE';
  value?: number;
  currency_id?: number;
  has_blackout?: boolean;
  location?: HolidayLocation;
  reason?: string;
}

export interface HolidayItem {
  id: number;
  name_holiday?: string; // Used in payload
  name?: string; // Appears in response data
  date_from: string;
  date_to: string;
  date_info?: string; // e.g. "Jueves 1"
  country_id?: number;
  holiday_calendar_parent_id?: number; // Parent calendar ID
  type_calendar: 'GENERAL' | 'CITY' | 'TOURIST';
  type_value?: 'AMOUNT' | 'PERCENTAGE';
  has_blackout?: boolean;
  is_active?: boolean;
  status?: string;
  amount?: { value: number; currency_id: number } | null;
  percentage?: number | null;
  location?: HolidayLocation | null;
  created_at?: string;
  year_from?: string;
  year_to?: string;
}

export interface CloneHolidaysPayload {
  source_calendar_id: number;
  target_calendar_id: number;
}

// Backend interfaces (Snake Case)
interface BackendCalendarItem {
  id: number;
  country_name?: string;
  country_id: number;
  status: string; // "active" | "expired" | "closed"
  year_from: string; // "2026-01-25"
  year_to: string; // "2026-01-27"
  created_at: string;
  updated_at: string;
  holidays_count: number;
  country?: {
    id: number;
    name: string;
  };
}

const SUPPORT_RESOURCES_URL = 'support/resources';

export interface SupportResource {
  id: number;
  name: string;
  iso?: string;
  state_id?: number;
  country_id?: number;
}

export interface SupportResourcesResponse {
  cities?: SupportResource[];
  countries?: SupportResource[];
  zones?: SupportResource[];
}

interface BackendListResponse {
  data: BackendCalendarItem[];
  pagination: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

const mapToFrontend = (item: BackendCalendarItem): CountryCalendarItem => {
  const yearFrom = item.year_from ? parseInt(item.year_from.split('-')[0]) : 0;
  const yearTo = item.year_to ? parseInt(item.year_to.split('-')[0]) : 0;

  return {
    id: item.id || item.country_id,
    createdAt: item.created_at,
    country: item.country?.name || item.country_name || 'Unknown',
    countryId: item.country_id,
    yearFrom: yearFrom,
    yearTo: yearTo,
    enabled: item.status === 'active',
    deactivationReason: undefined,
    holidaysCount: item.holidays_count || 0,
  };
};

// ==================== CALENDAR METHODS ====================

async function fetchCalendars(params: {
  page: number;
  pageSize: number;
  search?: string;
  country_id?: number;
  status?: string;
  year?: number;
}): Promise<CountryCalendarListResponse> {
  const { page, pageSize, search, country_id, status, year } = params;
  const response = await supportApi.get<BackendListResponse>(CALENDARS_URL, {
    params: {
      page,
      limit: pageSize,
      search,
      country_id,
      status,
      year,
    },
  });

  const body = response.data;

  return {
    data: body.data.map(mapToFrontend),
    total: body.pagination.total,
    page: body.pagination.current_page,
    pageSize: body.pagination.per_page,
  };
}

async function createCalendar(payload: CreateCalendarPayload): Promise<CountryCalendarItem> {
  const response = await supportApi.post<{ data: BackendCalendarItem }>(CALENDARS_URL, payload);
  return mapToFrontend(response.data.data);
}

async function getCalendar(id: number): Promise<CountryCalendarItem> {
  const response = await supportApi.get<{ data: BackendCalendarItem }>(`${CALENDARS_URL}/${id}`);
  return mapToFrontend(response.data.data);
}

export interface UpdateCalendarPayload {
  year_from: string;
  year_to: string;
  status?: 'active' | 'expired' | 'closed';
}

async function updateCalendar(
  id: number,
  payload: UpdateCalendarPayload
): Promise<CountryCalendarItem> {
  const response = await supportApi.put<{ data: BackendCalendarItem }>(
    `${CALENDARS_URL}/${id}`,
    payload
  );
  return mapToFrontend(response.data.data);
}

// ==================== HOLIDAY METHODS ====================

// GET /calendars/{calendarId}/holidays - List holidays for a calendar
async function getHolidays(
  calendarId: number,
  params?: {
    name_holiday?: string;
    year?: number;
    month?: number;
    type_calendar?: ('GENERAL' | 'CITY' | 'TOURIST')[];
    is_active?: boolean;
  }
): Promise<HolidayItem[]> {
  const response = await supportApi.get<{ data: HolidayItem[] }>(
    `${CALENDARS_URL}/${calendarId}/holidays`,
    { params }
  );
  return response.data.data;
}

// GET /calendars/{calendarId}/holidays?is_active=false - Deactivated holidays
async function getDeactivatedHolidays(
  calendarId: number,
  params?: {
    year?: number;
  }
): Promise<HolidayItem[]> {
  const response = await supportApi.get<{ data: HolidayItem[] }>(
    `${CALENDARS_URL}/${calendarId}/holidays`,
    { params: { is_active: false, ...params } }
  );
  return response.data.data;
}

// GET /holidays/{id} - Get single holiday detail
async function getHoliday(holidayId: number): Promise<HolidayItem> {
  const response = await supportApi.get<{ data: HolidayItem }>(`${HOLIDAYS_URL}/${holidayId}`);
  return response.data.data;
}

// POST /calendars/{calendarId}/holidays - Create holiday
async function createHoliday(
  calendarId: number,
  payload: CreateHolidayPayload
): Promise<HolidayItem> {
  const response = await supportApi.post<{ data: HolidayItem }>(
    `${CALENDARS_URL}/${calendarId}/holidays`,
    payload
  );
  return response.data.data;
}

// PUT /holidays/{id} - Update holiday
async function updateHoliday(
  holidayId: number,
  payload: CreateHolidayPayload
): Promise<HolidayItem> {
  const response = await supportApi.put<{ data: HolidayItem }>(
    `${HOLIDAYS_URL}/${holidayId}`,
    payload
  );
  return response.data.data;
}

// DELETE /holidays/{id} - Delete holiday
async function deleteHoliday(holidayId: number): Promise<void> {
  await supportApi.delete(`${HOLIDAYS_URL}/${holidayId}`);
}

// PUT /holidays/{id}/status - Change holiday status (activate/deactivate)
export interface ChangeHolidayStatusPayload {
  is_active: boolean;
  deactivation_reason?: string;
  reason?: string;
}

async function changeHolidayStatus(
  holidayId: number,
  payload: ChangeHolidayStatusPayload
): Promise<void> {
  await supportApi.put(`${HOLIDAYS_URL}/${holidayId}/status`, payload);
}

// GET /holidays/{id}/logs - Get holiday logs
export interface HolidayLog {
  id: number;
  action: string;
  old_values: Record<string, unknown> | null;
  new_values: Record<string, unknown> | null;
  user_id?: number;
  reason?: string;
  created_at: string;
}

async function getHolidayLogs(holidayId: number): Promise<HolidayLog[]> {
  const response = await supportApi.get<{ data: HolidayLog[] }>(
    `${HOLIDAYS_URL}/${holidayId}/logs`
  );
  return response.data.data;
}

// POST /holidays/clone - Clone holidays between calendars
async function cloneHolidays(payload: CloneHolidaysPayload): Promise<void> {
  await supportApi.post(CLONE_URL, payload);
}

// GET /holidays/filter - Global holiday search
async function filterHolidays(params: {
  country_id?: number;
  date_from?: string;
  date_to?: string;
  type_calendar?: 'GENERAL' | 'CITY' | 'TOURIST';
}): Promise<HolidayItem[]> {
  const response = await supportApi.get<{ data: HolidayItem[] }>(`${HOLIDAYS_URL}/filter`, {
    params,
  });
  return response.data.data;
}

// ==================== SUPPORT RESOURCES ====================

async function fetchResources(keys: string[]): Promise<SupportResourcesResponse> {
  const response = await supportApi.get<{ data: SupportResourcesResponse }>(SUPPORT_RESOURCES_URL, {
    params: { keys },
    paramsSerializer: (params) => {
      if (!params.keys) return '';
      const keysArray = Array.isArray(params.keys) ? params.keys : [params.keys];
      return keysArray.map((k: string) => `keys[]=${encodeURIComponent(k)}`).join('&');
    },
  });

  if (response.data && 'data' in response.data && typeof response.data.data === 'object') {
    return response.data.data;
  }
  return response.data as unknown as SupportResourcesResponse;
}

// GET /countries/{countryId}/cities-and-zones
async function getCitiesAndZones(countryId: number): Promise<{
  country: SupportResource;
  cities: SupportResource[];
  zones: SupportResource[];
}> {
  const response = await supportApi.get<{
    success: boolean;
    data: {
      country: SupportResource;
      cities: SupportResource[];
      zones: SupportResource[];
    };
  }>(`countries/${countryId}/cities-and-zones`);

  return {
    country: response.data.data.country,
    cities: response.data.data.cities || [],
    zones: response.data.data.zones || [],
  };
}

export const countryCalendarService = {
  // Calendar methods
  fetchCalendars,
  createCalendar,
  updateCalendar,
  getCalendar,
  // Holiday methods
  getHolidays,
  getDeactivatedHolidays,
  getHoliday,
  createHoliday,
  updateHoliday,
  deleteHoliday,
  changeHolidayStatus,
  getHolidayLogs,
  cloneHolidays,
  filterHolidays,
  // Support
  fetchResources,
  getCitiesAndZones,
};
