import type { Ref } from 'vue';
export interface ContentOperabilityActivity {
  duration: string;
  activityCode: string;
  calculatedSchedule?: string | null;
}

export interface ContentOperabilityDay {
  dayNumber: number;
  activities: ContentOperabilityActivity[];
}

export interface ContentOperabilityItem {
  scheduleId: string;
  days: ContentOperabilityDay[];
}

export interface TextDay {
  dayNumber: number;
  html: string;
}

export interface TextItem {
  textTypeCode: string;
  status: string;
  /** Para ITINERARY: contenido por día */
  days?: TextDay[];
  /** Para REMARKS, MENU, etc.: contenido único */
  html?: string;
}

export interface MultiDayContentRequest {
  contentOperability: {
    items: ContentOperabilityItem[];
  };
  texts: TextItem[];
}

export interface ActivityForRequest {
  numberDay?: number | null;
  duration: string | null;
  activityId?: string | number | null;
}

export interface ScheduleForRequest {
  id: string | number | null;
  activities?: ActivityForRequest[];
}

export interface daysHtmlForRequest {
  dayNumber: number;
  html?: string;
}
export interface TextTypeForRequest {
  textTypeCode: string;
  status?: string;
  days?: daysHtmlForRequest[];
}

export interface BuildRequestParams {
  schedules: ScheduleForRequest[];
  groupedSchedules: Ref<any[]>;
  textTypes: TextTypeForRequest[];
}
