export interface TrainContentText {
  html: string;
  status: string;
}

export interface TrainContentInclusion {
  inclusionCode: string;
  included: boolean;
  visibleToClient: boolean;
}

export interface TrainContentRequirement {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface TrainContentRequest {
  text: TrainContentText;
  inclusions: TrainContentInclusion[];
  requirements: TrainContentRequirement[];
}

export interface TrainContentResponse {
  success: boolean;
  data: TrainContentRequest;
}
