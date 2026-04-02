export interface HolidaysApiResponseDTO {
  success: boolean;
  data: {
    success: boolean;
    data: HolidaysDTO;
    code: number;
  };
}

export interface HolidaysDTO {
  country: {
    id: number;
    name: string;
    code: string;
  };
  groups: HolidayGroupDTO[];
}

export interface HolidayGroupDTO {
  id?: string | null;
  key: string;
  label: string;
  priority: number;
  isActive?: boolean;
  dates: HolidayDateDTO[];
}

export interface HolidayDateDTO {
  externalId: number;
  name: string;
  apiType: string;
  apiDate: string;
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
  history: HolidayHistoryDTO[];
}

export interface HolidayHistoryDTO {
  event: string;
  timestamp: string;
  author: string;
  diff: {
    before: any;
    after: any;
  };
}
