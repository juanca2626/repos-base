export interface TrainLoadingTextDto {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface TrainLoadingInclusionDto {
  inclusionCode: string;
  included: string;
  visibleToClient: boolean;
}

export interface TrainLoadingRequirementDto {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface TrainLoadingDto {
  text: TrainLoadingTextDto;
  inclusions: TrainLoadingInclusionDto[];
  requirements: TrainLoadingRequirementDto[];
}
