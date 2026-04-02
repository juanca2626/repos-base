export interface PackageMarketingTextDto {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface PackageMarketingContentOperabilityActivityDto {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedTime: string;
}

export interface PackageMarketingContentOperabilityDayDto {
  dayNumber: number;
  activities: PackageMarketingContentOperabilityActivityDto[];
}

export interface PackageMarketingContentOperabilityDto {
  scheduleId: string;
  days: PackageMarketingContentOperabilityDayDto[];
}

export interface PackageMarketingDto {
  contentOperability: PackageMarketingContentOperabilityDto[];
  texts: PackageMarketingTextDto[];
}
