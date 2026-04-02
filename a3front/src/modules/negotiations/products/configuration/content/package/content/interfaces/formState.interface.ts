import type { day as Day } from '@/modules/negotiations/products/configuration/utils/date.utils';

export interface UseMultiDayContentFormOptions {
  setReadMode?: () => void;
  currentKey?: string;
  currentCode?: string;
}

export interface ContentActivity {
  numberDay?: number | null;
  day?: string | null;
  duration: string | null;
  activityId: number | null;
  calculatedSchedule: string | null;
}

export interface ContentSchedule {
  id: number | string | null;
  day: string;
  selected: boolean;
  start?: string | null;
  end?: string | null;
  applyAllDay?: boolean;
  singleTime?: boolean;
  activities: ContentActivity[];
}

export interface TextTypeDay {
  dayNumber: number;
  html: string;
}

export interface ContentTextType {
  textTypeCode: string;
  status: string;
  days: TextTypeDay[];
}

export interface FormState {
  textTypeId: (string | number)[];
  days: Day[];
  schedules: ContentSchedule[];
  textTypes: ContentTextType[];
}
