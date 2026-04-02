export interface ContentOperabilityActivity {
  activityCode: string;
  duration?: string | null;
  durationMinutes?: number | null;
  calculatedSchedule?: string | null;
}

export interface ContentOperabilitySchedule {
  scheduleId: string;
  totalDuration?: string | null;
  activities: ContentOperabilityActivity[];
}

export interface ContentOperabilityItem {
  items: ContentOperabilitySchedule[];
}

export interface ContentText {
  textTypeCode: string;
  html: string;
}

export interface ContentInclusion {
  inclusionCode: string | null;
  included: boolean;
  visibleToClient: boolean;
}

export interface ContentRequirement {
  requirementCode: string | null;
  visibleToClient: boolean;
}

export interface ServiceContentRequest {
  contentOperability: ContentOperabilityItem;
  texts: ContentText[];
  inclusions: ContentInclusion[];
  requirements: ContentRequirement[];
}

export interface ServiceContentResponse extends ServiceContentRequest {}
// export interface ServiceContentResponse {
//   success: boolean;
//   data: ServiceContentRequest;
// }
