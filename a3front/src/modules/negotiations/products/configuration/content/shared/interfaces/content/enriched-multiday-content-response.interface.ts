import type { MultidayContentResponse } from './multiday-content-response.interface';

export interface EnrichedMultidayContentResponse extends MultidayContentResponse {
  id: string;
  groupingKeys: {
    programDurationCode: string;
    operationalSeasonCode: string;
  };
}
