export interface activityResponseMultiday {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedSchedule: string;
}

export interface daysResponseMultiday {
  dayNumber: number;
  activities: activityResponseMultiday[];
}

export interface itemsResponseMultiday {
  scheduleId: string;
  days: daysResponseMultiday[];
}

export interface contentOperabilityResponseMultiday {
  items: itemsResponseMultiday[];
}

export interface daysTextResponseMultiday {
  dayNumber: number;
  html: string;
}

export interface textResponseMultiday {
  textTypeCode: string;
  status: string;
  days: daysTextResponseMultiday[];
}

export interface MultidayContentResponse {
  contentOperability: contentOperabilityResponseMultiday;
  texts: textResponseMultiday[];
}
