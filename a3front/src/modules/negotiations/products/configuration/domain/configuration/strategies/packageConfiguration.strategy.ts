import type { BackendPackageConfigurationItem } from '../types/configuration.backend.types';

import type { FetchConfigurationModel } from '../models/fetchConfiguration.model';

export const packageConfigurationStrategy = (
  data: BackendPackageConfigurationItem[]
): FetchConfigurationModel => {
  return {
    items: data.map((item) => ({
      id: item.id,
      key: item.programDurationCode,
      name: `Programa ${item.programDurationCode}`,
      type: 'PACKAGE',
      raw: item,
    })),
  };
};
