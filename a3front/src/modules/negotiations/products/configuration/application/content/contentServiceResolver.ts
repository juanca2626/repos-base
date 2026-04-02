import { fetchGenericLoadingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/loading/genericLoadingContent.service';
import { fetchTrainLoadingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/loading/trainLoadingContent.service';
import { fetchPackageLoadingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/loading/packageLoadingContent.service';
import { fetchGenericMarketingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/marketing/genericMarketingContent.service';
import { fetchTrainMarketingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/marketing/trainMarketingContent.service';
import { fetchPackageMarketingContent } from '@/modules/negotiations/products/configuration/infrastructure/content/services/marketing/packageMarketingContent.service';

import type { ServiceType, Role } from '../../types/index';

type Key = `${ServiceType}_${Role}`;

const serviceMap: Record<Key, any> = {
  GENERIC_LOADING: fetchGenericLoadingContent,
  TRAIN_LOADING: fetchTrainLoadingContent,
  PACKAGE_LOADING: fetchPackageLoadingContent,

  // FUTURO
  GENERIC_MARKETING: fetchGenericMarketingContent,
  TRAIN_MARKETING: fetchTrainMarketingContent,
  PACKAGE_MARKETING: fetchPackageMarketingContent,
};

export function resolveContentService(type: ServiceType, role: Role) {
  const key = `${type}_${role}` as Key;
  return serviceMap[key];
}
