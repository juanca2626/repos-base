import { TrainServiceDetailsLoader } from '@/modules/negotiations/products/configuration/composables/loaders/serviceDetails/trainServiceDetailsLoader';
import { GeneralServiceDetailsLoader } from '@/modules/negotiations/products/configuration/composables/loaders/serviceDetails/generalServiceDetailsLoader';
import { MultiDaysServiceDetailsLoader } from '@/modules/negotiations/products/configuration/composables/loaders/serviceDetails/multiDaysServiceDetailsLoader';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import type { ServiceDetailsLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/serviceDetailsLoader.interface';
import type {
  ServiceDetailsResponse,
  MultiDaysServiceDetailsResponse,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { Ref } from 'vue';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type { ServiceDetailLoader } from './interfaces';
import { GenericServiceDetailLoader } from '@/modules/negotiations/products/configuration/composables/loaders/serviceDetails/genericServiceDetailLoader';

export class ServiceDetailsLoaderFactory {
  static createLoader(
    isServiceTypeTrain: Ref<boolean> | boolean,
    isServiceTypeMultiDays: Ref<boolean> | boolean,
    isServiceTypeGeneral: Ref<boolean> | boolean
  ): ServiceDetailsLoader {
    const isTrain =
      typeof isServiceTypeTrain === 'boolean' ? isServiceTypeTrain : isServiceTypeTrain.value;
    const isMultiDays =
      typeof isServiceTypeMultiDays === 'boolean'
        ? isServiceTypeMultiDays
        : isServiceTypeMultiDays.value;
    const isGeneral =
      typeof isServiceTypeGeneral === 'boolean' ? isServiceTypeGeneral : isServiceTypeGeneral.value;

    if (isTrain) {
      return new TrainServiceDetailsLoader();
    }

    if (isMultiDays) {
      return new MultiDaysServiceDetailsLoader();
    }

    if (isGeneral) {
      return new GeneralServiceDetailsLoader();
    }

    // Por defecto, usar General
    return new GeneralServiceDetailsLoader();
  }

  static ServiceDetailBySectionKeyCode(serviceType: ServiceType): ServiceDetailLoader | null {
    if (serviceType === 'GENERIC') {
      return new GenericServiceDetailLoader();
    }

    // if (serviceType === 'TRAIN') {
    //   return new TrainServiceDetailLoader();
    // }

    // if (serviceType === 'PACKAGE') {
    //   return new PackageServiceDetailLoader();
    // }

    return null;
  }

  static getServiceDetail(
    sectionKey: string,
    key: string,
    code: string
  ): ServiceDetailsResponse | MultiDaysServiceDetailsResponse | null {
    const isTrainTypeSection = sectionKey.startsWith('train-type-');
    const isMultiDaysSection = sectionKey.startsWith('multi-days-');
    const isCategorySection = sectionKey.startsWith('category-');

    if (isTrainTypeSection) {
      const trainStore = useTrainConfigurationStore();
      return trainStore.getServiceDetail(key, code);
    }

    if (isCategorySection) {
      const genericStore = useGenericConfigurationStore();
      return genericStore.getServiceDetail(key, code);
    }

    if (isMultiDaysSection) {
      const packageStore = usePackageConfigurationStore();
      return packageStore.getServiceDetail(key, code);
    }

    // Por defecto, usar Generic
    const genericStore = useGenericConfigurationStore();
    return genericStore.getServiceDetail(key, code);
  }
}
