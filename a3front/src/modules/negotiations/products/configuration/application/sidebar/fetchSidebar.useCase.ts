import type { Role, ServiceType } from '../../types';
import type { SidebarModel } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.types';

import { resolverSidebarService } from './sidebarServiceResolver';
import { resolverSidebarStrategy } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebarStrategyResolver';

export interface FetchSidebarParams {
  role: Role;
  serviceType: ServiceType;
  productSupplierId: string;
  codeOrKey: string;
}

export async function fetchSidebarUseCase(params: FetchSidebarParams): Promise<SidebarModel> {
  const { role, serviceType, productSupplierId, codeOrKey } = params;

  const service = resolverSidebarService(role);
  const strategy = resolverSidebarStrategy(serviceType);

  const response = await service(productSupplierId, codeOrKey);

  if (!response.success) {
    throw new Error(response.error || 'Failed to fetch sidebar');
  }

  return strategy(response.data, codeOrKey);
}
