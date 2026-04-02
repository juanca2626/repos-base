import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type { SaveBasicPricingResponseMapper } from '../mappers/saveBasicPricingResponse.mapper.interface';

import { GenericSaveResponseMapper } from '../mappers/GenericSaveResponse.mapper';
import { TrainSaveResponseMapper } from '../mappers/TrainSaveResponse.mapper';
import { PackageSaveResponseMapper } from '../mappers/PackageSaveResponse.mapper';

const mapperMap: Record<ServiceType, SaveBasicPricingResponseMapper> = {
  GENERIC: new GenericSaveResponseMapper(),
  TRAIN: new TrainSaveResponseMapper(),
  PACKAGE: new PackageSaveResponseMapper(),
};

export const resolveSaveBasicPricingResponseMapper = (serviceType: ServiceType) => {
  const mapper = mapperMap[serviceType];

  if (!mapper) {
    throw new Error(`Save basic pricing response mapper not found for ${serviceType}`);
  }

  return mapper;
};
