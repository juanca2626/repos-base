export interface PackageLoadingTextDto {
  textTypeCode: string;
  html: string;
  days?: {
    dayNumber: number;
    html: string;
  }[];
  status: string;
}

export interface PackageLoadingContentOperabilityActivityDto {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedTime: string;
}

export interface PackageLoadingContentOperabilityDayDto {
  dayNumber: number;
  activities: PackageLoadingContentOperabilityActivityDto[];
}

export interface PackageLoadingContentOperabilityDto {
  scheduleId: string;
  days: PackageLoadingContentOperabilityDayDto[];
}

export interface PackageLoadingDto {
  contentOperability?: {
    items: PackageLoadingContentOperabilityDto[];
  };
  texts: PackageLoadingTextDto[];
}
