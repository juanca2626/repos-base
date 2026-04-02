import type { TrainContentResponse } from './train-content-response.interface';

export interface EnrichedTrainContentResponse extends TrainContentResponse {
  id: string;
  groupingKeys: {
    operatingLocationKey: string;
    trainTypeCode: string;
  };
}
