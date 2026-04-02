import type { ServiceType } from '@/modules/negotiations/products/configuration/types';

import { resolveSaveBasicPricingStrategy } from '@/modules/negotiations/products/configuration/application/pricingPlans/resolvers/saveBasicPricingResolver';
import { resolveSaveBasicPricingResponseMapper } from '@/modules/negotiations/products/configuration/application/pricingPlans/resolvers/saveBasicPricingResponseMapperResponse';
import { mapBasicPricingCatalogs } from './mappers/mapBasicPricingCatalogs';
import type {
  SaveBasicPricingRequest,
  SaveBasicPricingRequestBeforeMapping,
} from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveBasicPricingRequest.types';
import type { BasicPricingCatalogs } from './mappers/mapBasePricingCatalogs.mapper.interface';

export async function saveBasicPricingUseCase(
  serviceType: ServiceType,
  productSupplierId: string,
  serviceDetailId: string,
  ratePlanId: string | null,
  payload: SaveBasicPricingRequestBeforeMapping,
  catalogs: BasicPricingCatalogs
): Promise<any> {
  const strategy = resolveSaveBasicPricingStrategy(serviceType);

  const mappedPayload: SaveBasicPricingRequest = mapBasicPricingCatalogs(payload, catalogs);

  const response = await strategy.execute(
    productSupplierId,
    serviceDetailId,
    ratePlanId,
    mappedPayload
  );

  const mapper = resolveSaveBasicPricingResponseMapper(serviceType);

  return mapper.map(response ?? []);
}
