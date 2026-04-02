import type { Role } from '../../types/index';

import { fetchLoadingSidebar } from '@/modules/negotiations/products/configuration/infrastructure/sidebar/loadingSidebar.service';
import { fetchMarketingSidebar } from '@/modules/negotiations/products/configuration/infrastructure/sidebar/marketingSidebar.service';
import type { SidebarService } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.service.interface';

const roleServiceMap: Record<Role, SidebarService> = {
  LOADING: fetchLoadingSidebar,
  MARKETING: fetchMarketingSidebar,
};

export function resolverSidebarService(role: Role): SidebarService {
  const service = roleServiceMap[role];

  if (!service) {
    throw new Error(`Service not found for role: ${role}`);
  }

  return service;
}
