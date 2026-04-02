export interface TrainMarketingTextDto {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface TrainMarketingInclusionDto {
  inclusionCode: string;
  included: string;
  visibleToClient: boolean;
}

export interface TrainMarketingRequirementDto {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface TrainMarketingDto {
  texts: TrainMarketingTextDto[];
  inclusions: TrainMarketingInclusionDto[];
  requirements: TrainMarketingRequirementDto[];
}
