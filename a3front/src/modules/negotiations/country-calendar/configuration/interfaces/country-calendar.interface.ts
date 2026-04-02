export interface CountryCalendarItem {
  id: number;
  countryId?: number;
  createdAt: string;
  country: string;
  yearFrom: number;
  yearTo: number;
  enabled?: boolean;
  deactivationReason?: string;
  holidaysCount?: number;
}

export interface CountryCalendarListResponse {
  data: CountryCalendarItem[];
  total: number;
  page: number;
  pageSize: number;
}
