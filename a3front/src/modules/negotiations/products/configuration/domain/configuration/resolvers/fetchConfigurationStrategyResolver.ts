import { genericConfigurationStrategy } from '../strategies/genericConfiguration.strategy';
import { trainConfigurationStrategy } from '../strategies/trainConfiguration.strategy';
import { packageConfigurationStrategy } from '../strategies/packageConfiguration.strategy';

import type { FetchConfigurationStrategy } from '../strategies/fetchConfiguration.strategy.types';
import type { ServiceType } from '../../../types/index';

const strategyMap: Record<ServiceType, FetchConfigurationStrategy> = {
  GENERIC: genericConfigurationStrategy,
  TRAIN: trainConfigurationStrategy,
  PACKAGE: packageConfigurationStrategy,
};

export function resolveConfigurationStrategy(type: ServiceType): FetchConfigurationStrategy {
  return strategyMap[type];
}
