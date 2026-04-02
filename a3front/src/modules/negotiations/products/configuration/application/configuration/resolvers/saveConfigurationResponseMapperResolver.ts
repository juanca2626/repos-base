import type { ServiceType } from '../../../types/index';

import { GenericSaveResponseMapper } from '../mappers/GenericSaveResponse.mapper';
import { TrainSaveResponseMapper } from '../mappers/TrainSaveResponse.mapper';
import { PackageSaveResponseMapper } from '../mappers/PackageSaveResponse.mapper';

const mapperMap = {
  GENERIC: new GenericSaveResponseMapper(),
  TRAIN: new TrainSaveResponseMapper(),
  PACKAGE: new PackageSaveResponseMapper(),
};

export const resolveSaveResponseMapper = (serviceType: ServiceType) => {
  const mapper = mapperMap[serviceType];

  if (!mapper) {
    throw new Error(`Mapper not found for ${serviceType}`);
  }

  return mapper;
};
