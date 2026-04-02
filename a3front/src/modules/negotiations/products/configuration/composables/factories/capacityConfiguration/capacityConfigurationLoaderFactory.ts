import { TrainCapacityConfigurationLoader } from '../../loaders/capacityConfiguration/trainCapacityConfigurationLoader';
import { GeneralCapacityConfigurationLoader } from '../../loaders/capacityConfiguration/generalCapacityConfigurationLoader';
import { MultiDaysCapacityConfigurationLoader } from '../../loaders/capacityConfiguration/multiDaysCapacityConfigurationLoader';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import type { CapacityConfigurationLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/capacityConfigurationLoader.interface';
import type { Ref } from 'vue';
import type { CapacityConfigurationResponse } from '../../../content/shared/interfaces/configuration/capacity-configuration.interface';

export class CapacityConfigurationLoaderFactory {
  static createLoader(
    isServiceTypeTrain: Ref<boolean> | boolean,
    isServiceTypeMultiDays: Ref<boolean> | boolean,
    isServiceTypeGeneral: Ref<boolean> | boolean
  ): CapacityConfigurationLoader {
    const isTrain =
      typeof isServiceTypeTrain === 'boolean' ? isServiceTypeTrain : isServiceTypeTrain.value;
    const isMultiDays =
      typeof isServiceTypeMultiDays === 'boolean'
        ? isServiceTypeMultiDays
        : isServiceTypeMultiDays.value;
    const isGeneral =
      typeof isServiceTypeGeneral === 'boolean' ? isServiceTypeGeneral : isServiceTypeGeneral.value;

    if (isTrain) {
      return new TrainCapacityConfigurationLoader();
    }

    if (isMultiDays) {
      return new MultiDaysCapacityConfigurationLoader();
    }

    if (isGeneral) {
      return new GeneralCapacityConfigurationLoader();
    }

    // Por defecto, usar General
    return new GeneralCapacityConfigurationLoader();
  }

  static getCapacityConfiguration(
    sectionKey: string,
    currentKey: string,
    currentCode: string
  ): CapacityConfigurationResponse | null {
    const isTrainTypeSection = sectionKey.startsWith('train-type-');
    const isMultiDaysSection = sectionKey.startsWith('multi-days-');
    const isCategorySection = sectionKey.startsWith('category-');

    if (isTrainTypeSection) {
      const trainStore = useTrainConfigurationStore();
      return trainStore.getCapacityConfiguration(currentKey, currentCode);
    }

    if (isCategorySection) {
      const genericStore = useGenericConfigurationStore();
      return genericStore.getCapacityConfiguration(currentKey, currentCode);
    }

    if (isMultiDaysSection) {
      const packageStore = usePackageConfigurationStore();
      return packageStore.getCapacityConfiguration(currentKey, currentCode);
    }

    const genericStore = useGenericConfigurationStore();
    return genericStore.getCapacityConfiguration(currentKey, currentCode);
  }
}
