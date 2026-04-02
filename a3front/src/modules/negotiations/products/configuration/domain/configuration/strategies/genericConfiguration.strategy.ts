import type { BackendGenericConfigurationItem } from '../types/configuration.backend.types';

import type { FetchConfigurationModel } from '../models/fetchConfiguration.model';

export const genericConfigurationStrategy = (
  data: BackendGenericConfigurationItem[]
): FetchConfigurationModel => {
  return {
    items: data.map((item) => ({
      id: item.id,
      key: item.operatingLocation.key,
      name: item.operatingLocation.state.name,
      type: 'GENERIC',
      raw: item,
    })),
  };
};
