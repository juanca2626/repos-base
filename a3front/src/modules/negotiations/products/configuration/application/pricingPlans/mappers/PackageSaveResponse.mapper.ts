import type { SaveBasicPricingResponseMapper } from './saveBasicPricingResponse.mapper.interface';

export class PackageSaveResponseMapper implements SaveBasicPricingResponseMapper {
  map(response: any): any {
    return response;
  }
}
