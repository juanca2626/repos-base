export interface ServiceDetailsSchedule {
  day: string;
  start: string | null;
  end: string | null;
  applyAllDay: boolean;
  singleTime: boolean;
  active: boolean;
}

export interface ServiceDetailsRequest {
  id: string | null;
  groupingKeys: {
    programDurationCode: string;
    operationalSeasonCode: string;
  };
  content: {
    basicInfo: {
      name: string;
      subTypeCode: string;
      profileCode: string;
    };
    logistics: {
      startPointCodes: string[];
      endPointCodes: string[];
      duration: string;
    };
    operability: {
      mode: string;
      applyAllDay: boolean;
      singleTime: boolean;
      schedules: ServiceDetailsSchedule[];
    };
    status: {
      state: string;
      reason: string;
      clientVisible: boolean;
    };
  };
  completionStatus: string;
}
