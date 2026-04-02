import type { CompoundSidebarService } from '@/modules/negotiations/compounds/domain/sidebar/sidebar.service.interface';
import { fetchCompoundsSidebar } from '@/modules/negotiations/compounds/infrastructure/sidebar/compoundsSidebar.service';

export function resolverCompoundSidebarService(): CompoundSidebarService {
  return fetchCompoundsSidebar;
}
