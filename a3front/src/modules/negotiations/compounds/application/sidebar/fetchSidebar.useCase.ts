import type { CompoundSidebarModel } from '@/modules/negotiations/compounds/domain/sidebar/sidebar.types';

import { resolverCompoundSidebarService } from './sidebarServiceResolver';
import { resolverCompoundSidebarStrategy } from '@/modules/negotiations/compounds/domain/sidebar/sidebarStrategyResolver';

export interface FetchCompoundsSidebarParams {
  compoundId: string;
}

export async function fetchCompoundsSidebarUseCase(
  params: FetchCompoundsSidebarParams
): Promise<CompoundSidebarModel> {
  const { compoundId } = params;

  const service = resolverCompoundSidebarService(); // infrastructure
  const strategy = resolverCompoundSidebarStrategy(); // domain

  const response = await service(compoundId);

  if (!response.success) {
    throw new Error(response.error || 'Failed to fetch compounds sidebar');
  }

  return strategy(response.data);
}
