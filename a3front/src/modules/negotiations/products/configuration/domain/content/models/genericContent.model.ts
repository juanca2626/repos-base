export interface GenericContentModel {
  texts: TextBlock[];
  inclusions?: InclusionBlock[];
  requirements?: RequirementBlock[];
  contentOperability?: ItemOperabilityBlock;
}

export interface TextBlock {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface InclusionBlock {
  included: string;
  inclusionCode: string;
  visibleToClient: boolean;
}

export interface RequirementBlock {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface ItemOperabilityBlock {
  items: OperabilityBlock[];
}

export interface OperabilityBlock {
  scheduleId: string;
  activities: ActivityBlock[];
  totalDuration: string;
}

export interface ActivityBlock {
  activityCode: string;
  duration: string;
  durationInMinutes: number;
  calculatedTime: string;
}
