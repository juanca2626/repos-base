import type { ServiceType } from '../../types';
import type { SidebarStrategy } from './sidebar.strategy.types';

import { baseSidebarStrategy } from './strategies/baseSidebar.strategy';

const strategyMap: Record<ServiceType, SidebarStrategy> = {
  GENERIC: baseSidebarStrategy,
  TRAIN: baseSidebarStrategy,
  PACKAGE: baseSidebarStrategy,
};

export function resolverSidebarStrategy(serviceType: ServiceType): SidebarStrategy {
  const strategy = strategyMap[serviceType];

  if (!strategy) {
    throw new Error(`Strategy not found for service type: ${serviceType}`);
  }

  return strategy;
}
