export interface TrainServiceDetailsRequest {
  id: string | null;
  groupingKeys: {
    operatingLocationKey: string;
    trainTypeCode: string;
  };
  content: {
    basicInfo: {
      name: string;
      startTrainStationCodes: string[];
      endTrainStationCodes: string[];
    };
    frequencies: Array<{
      id: string | null;
      code: string;
      fareType: string;
      schedule: {
        departure: string;
        arrival: string;
        duration: string;
        operatingDays: string[];
      };
      validityPeriods: Array<{
        startDate: string;
        endDate: string;
      }>;
    }>;
    status: {
      state: string;
      reason: string;
      clientVisible: boolean;
    };
  };
  completionStatus: string;
}

export interface TrainServiceDetailsResponse {
  id: string;
  groupingKeys: {
    operatingLocationKey: string;
    trainTypeCode: string;
  };
  content: {
    basicInfo: {
      name: string;
      startTrainStationCodes: string[];
      endTrainStationCodes: string[];
    };
    frequencies: Array<{
      id: string;
      code: string;
      fareType: string;
      schedule: {
        departure: string;
        arrival: string;
        duration: string;
        operatingDays: string[];
      };
      validityPeriods: Array<{
        startDate: string;
        endDate: string;
      }>;
    }>;
    status: {
      state: string;
      clientVisible: boolean;
    };
  };
  completionStatus: string;
}
