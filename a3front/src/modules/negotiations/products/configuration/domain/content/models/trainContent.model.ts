export interface TrainContentModel {
  text: TextBlock;
  inclusions?: InclusionBlock[];
  requirements?: RequirementBlock[];
}

export interface TextBlock {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface InclusionBlock {
  inclusionCode: string;
  included: string;
  visibleToClient: boolean;
}

export interface RequirementBlock {
  requirementCode: string;
  visibleToClient: boolean;
}
