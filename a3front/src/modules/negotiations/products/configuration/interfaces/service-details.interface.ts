export interface GroupingKeys {
  operatingLocationKey: string;
  supplierCategoryCode?: string;
  trainTypeCode?: string;
}

export interface BasicInfo {
  name: string;
  subTypeCode: string;
  profileCode: string;
}

export interface Logistics {
  startPointCodes: string[];
  endPointCodes: string[];
  duration: string;
}

export interface Schedule {
  id: number | null;
  day: string;
  start: string | null;
  end: string | null;
  applyAllDay: boolean;
  singleTime: boolean;
  active: boolean;
}

export interface Operability {
  mode: 'CUSTOM' | 'ALL_DAY';
  applyAllDay: boolean;
  singleTime: boolean;
  schedules: Schedule[];
}

export interface Status {
  state: string;
  reason: string;
  clientVisible: boolean;
}

export interface ValidityPeriod {
  startDate: string;
  endDate: string;
}
export interface Frequency {
  id: string;
  code: string;
  fareType: string;
  schedule: Schedule;
  validityPeriods: ValidityPeriod[];
}
export interface ServiceDetailsContent {
  basicInfo: BasicInfo;
  logistics: Logistics;
  frequencies?: Frequency[];
  operability: Operability;
  status: Status;
}

export interface ServiceDetailsResponse {
  id: string;
  groupingKeys: GroupingKeys;
  content: ServiceDetailsContent;
  completionStatus: string;
}

export interface MultiDaysServiceDetailsResponse {
  id: string;
  groupingKeys: {
    programDurationCode: string;
    operationalSeasonCode: string;
  };
  content: ServiceDetailsContent;
  completionStatus: string;
}
