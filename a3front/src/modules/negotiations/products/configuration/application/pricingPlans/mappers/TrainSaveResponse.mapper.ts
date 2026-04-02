import type { SaveBasicPricingResponseMapper } from './saveBasicPricingResponse.mapper.interface';

export class TrainSaveResponseMapper implements SaveBasicPricingResponseMapper {
  map(response: any): any {
    return response;
  }
}
