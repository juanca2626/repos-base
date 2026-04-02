export type HolidayCategoryKey = 'GENERAL' | 'TOURIST' | 'CITY' | 'CUSTOM';

export type HolidayDateType = 'UNIQUE' | 'RANGE';

export type HolidaySourceStatus = 'ACTIVE' | 'REMOVED';

export interface HolidayRange {
  from: string;
  to: string;
}

export interface HolidayMetadata {
  movedFrom?: HolidayCategoryKey;
  originalCategory: HolidayCategoryKey;
  movedAt?: string;
  userModifiedRange?: boolean;
}

export interface HolidayDate {
  externalId: number;
  name: string;
  type: HolidayDateType;
  range?: HolidayRange;
  date?: string;
  isActive: boolean;
  sourceStatus?: HolidaySourceStatus;
  metadata: HolidayMetadata;
}

export interface HolidayCategory {
  uuid: string;
  id?: string;
  key: HolidayCategoryKey;
  label: string;
  icon: string;
  color: string;
  order: number;
  isActive: boolean;
  dates: HolidayDate[];
}
