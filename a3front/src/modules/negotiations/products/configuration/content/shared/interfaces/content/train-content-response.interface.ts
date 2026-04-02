export interface inclusionsResponseTrain {
  inclusionCode: string;
  included: boolean;
  visibleToClient: boolean;
}

export interface requirementsResponseTrain {
  requirementCode: string;
  visibleToClient: boolean;
}

export interface textResponseTrain {
  textTypeCode: string;
  html: string;
  status: string;
}

export interface TrainContentResponse {
  inclusions: inclusionsResponseTrain[];
  requirements: requirementsResponseTrain[];
  text: textResponseTrain;
}
