import { HolidayDateTypeEnum } from '../enums/holidayDateType.enum';

export interface HolidayHistory {
  event: string;
  timestamp: string;
  author: string;
  diff: {
    before: any;
    after: any;
  };
}
export interface HolidayDate {
  externalId: number;
  name: string;
  apiType: HolidayDateTypeEnum;
  apiDate: string | null;
  apiDateRange: {
    from: string;
    to: string;
  } | null;
  isActive: boolean;
  isModified: boolean;
  isNewFromApi: boolean;
  moveInfo: {
    originGroupKey: string;
    currentGroupKey: string;
    movedAt: string;
  };
  syncConflict: boolean;
  isOrphan: boolean;
  expandedDates: string[];
  history: HolidayHistory[];
}
