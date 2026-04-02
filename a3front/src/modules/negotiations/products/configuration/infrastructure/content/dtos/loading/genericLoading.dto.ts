export interface GenericLoadingTextDto {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface GenericLoadingInclusionDto {
  inclusionCode: string;
  included: string;
  visibleToClient: boolean;
}

export interface GenericLoadingRequirementDto {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface GenericLoadingContentOperabilityActivityDto {
  duration: string;
  activityCode: string;
  durationInMinutes: number;
  calculatedTime: string;
}

export interface GenericLoadingContentOperabilityItemDto {
  scheduleId: string;
  totalDuration: string;
  activities: GenericLoadingContentOperabilityActivityDto[];
}

export interface GenericLoadingDto {
  contentOperability: {
    items: GenericLoadingContentOperabilityItemDto[];
  };
  texts: GenericLoadingTextDto[];
  inclusions: GenericLoadingInclusionDto[];
  requirements: GenericLoadingRequirementDto[];
}
