export interface activityResponse {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedSchedule: string;
}

export interface itemsResponse {
  scheduleId: string;
  totalDuration: string;
  activities: activityResponse[];
}

export interface textResponse {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface inclusionsResponse {
  inclusionCode: string;
  included: boolean;
  visibleToClient: boolean;
}

export interface requirementsResponse {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface contentOperabilityResponse {
  items: itemsResponse[];
}

export interface GenericContentResponse {
  texts: textResponse[];
  contentOperability: contentOperabilityResponse;
  inclusions: inclusionsResponse[];
  requirements: requirementsResponse[];
}
