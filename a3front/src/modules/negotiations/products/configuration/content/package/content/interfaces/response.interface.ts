export interface activityResponse {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedSchedule: string;
}

export interface daysResponse {
  dayNumber: number;
  activities: activityResponse[];
}

export interface itemsResponse {
  scheduleId: string;
  days: daysResponse[];
}

export interface contentOperabilityResponse {
  items: itemsResponse[];
}

export interface daysTextResponse {
  dayNumber: number;
  html: string;
}

export interface textResponse {
  textTypeCode: string;
  status: string;
  days: daysTextResponse[];
}

export interface multiDayContentResponse {
  contentOperability: contentOperabilityResponse;
  texts: textResponse[];
}
