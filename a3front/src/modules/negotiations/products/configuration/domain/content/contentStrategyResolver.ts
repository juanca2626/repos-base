import { genericLoadingStrategy } from './strategies/genericLoading.strategy';
import { packageLoadingStrategy } from './strategies/packageLoading.strategy';
import { trainLoadingStrategy } from './strategies/trainLoading.strategy';

import type { ServiceType, Role } from '../../types/index';
import type { MarketingContentStrategy } from './content.strategy.interface';

type Key = `${ServiceType}_${Role}`;

const strategyMap: Record<Key, MarketingContentStrategy> = {
  GENERIC_LOADING: genericLoadingStrategy,
  PACKAGE_LOADING: packageLoadingStrategy,
  TRAIN_LOADING: trainLoadingStrategy,

  // FUTURO
  GENERIC_MARKETING: genericLoadingStrategy,
  PACKAGE_MARKETING: packageLoadingStrategy,
  TRAIN_MARKETING: trainLoadingStrategy,
};

export function resolveContentStrategy(type: ServiceType, role: Role): MarketingContentStrategy {
  const key = `${type}_${role}` as Key;
  return strategyMap[key];
}
