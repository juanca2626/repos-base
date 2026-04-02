import type { GenericContentResponse } from './generic-content-response.interface';

export interface EnrichedGenericContentResponse extends GenericContentResponse {
  id: string;
  groupingKeys: {
    operatingLocationKey: string;
    supplierCategoryCode: string;
  };
}
