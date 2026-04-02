export interface PackageContentModel {
  contentOperability?: ItemOperabilityBlock;
  texts: TextBlock[];
}

export interface ItemOperabilityBlock {
  items: OperabilityBlock[];
}

export interface TextBlock {
  textTypeCode: string;
  html: string;
  status: string;
  days?: { dayNumber: number; html: string }[];
}

export interface InclusionBlock {
  code: string;
  included: string;
  visibleToClient: boolean;
}

export interface RequirementBlock {
  code: string;
  visibleToClient: boolean;
}

export interface OperabilityBlock {
  scheduleId: string;
  days: ContentOperabilityDay[];
}

export interface ContentOperabilityDay {
  dayNumber: number;
  activities: ContentOperabilityActivity[];
}

export interface ContentOperabilityActivity {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedTime: string;
}
