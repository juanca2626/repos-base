export interface GenericMarketingTextDto {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface GenericMarketingInclusionDto {
  inclusionCode: string;
  included: string;
  visibleToClient: boolean;
}

export interface GenericMarketingRequirementDto {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface GenericMarketingContentOperabilityActivityDto {
  duration: string;
  activityCode: string;
  durationInMinutes: number;
  calculatedSchedule: string;
}

export interface GenericMarketingContentOperabilityItemDto {
  scheduleId: string;
  totalDuration: string;
  activities: GenericMarketingContentOperabilityActivityDto[];
}

export interface GenericMarketingDto {
  contentOperability: {
    items: GenericMarketingContentOperabilityItemDto[];
  };
  texts: GenericMarketingTextDto[];
  inclusions: GenericMarketingInclusionDto[];
  requirements: GenericMarketingRequirementDto[];
}
