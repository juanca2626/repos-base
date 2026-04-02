export interface LoadExternalHolidaysParams {
  country: string;
  city?: string;
  dateFrom: string;
  dateTo: string;
  ratePlanId?: string | null;
}
