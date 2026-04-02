import type { SaveBasicPricingResponseMapper } from './saveBasicPricingResponse.mapper.interface';

export class GenericSaveResponseMapper implements SaveBasicPricingResponseMapper {
  map(response: any): any {
    return response;
  }
}
