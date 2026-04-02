import type { BackendTrainConfigurationItem } from '@/modules/negotiations/products/configuration/domain/configuration/types/configuration.backend.types';

import type { FetchConfigurationModel } from '../models/fetchConfiguration.model';

export const trainConfigurationStrategy = (
  data: BackendTrainConfigurationItem[]
): FetchConfigurationModel => {
  return {
    items: data.map((item) => ({
      id: item.id,
      key: item.operatingLocation.key,
      name: item.operatingLocation.city?.name ?? item.operatingLocation.state.name,
      type: 'TRAIN',
      raw: item,
    })),
  };
};
