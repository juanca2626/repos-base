import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';

import { ServiceTypeEnum } from '@/modules/negotiations/products/configuration/enums/ServiceType.enum';

export const serviceTypeRegistry = {
  [ServiceTypeEnum.GENERIC]: {
    configurationStore: useGenericConfigurationStore,
  },

  [ServiceTypeEnum.TRAIN]: {
    configurationStore: useTrainConfigurationStore,
  },

  [ServiceTypeEnum.PACKAGE]: {
    configurationStore: usePackageConfigurationStore,
  },
};
