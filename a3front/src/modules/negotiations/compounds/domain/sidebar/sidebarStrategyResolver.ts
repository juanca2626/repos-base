import type { CompoundSidebarStrategy } from './sidebar.strategy.types';

import { baseCompoundSidebarStrategy } from './strategies/baseSidebar.strategy';

type CompoundStrategyType = 'DEFAULT';

const strategyMap: Record<CompoundStrategyType, CompoundSidebarStrategy> = {
  DEFAULT: baseCompoundSidebarStrategy,
};

export function resolverCompoundSidebarStrategy(
  strategyType: CompoundStrategyType = 'DEFAULT'
): CompoundSidebarStrategy {
  const strategy = strategyMap[strategyType];

  if (!strategy) {
    throw new Error(`Strategy not found for type: ${strategyType}`);
  }

  return strategy;
}
