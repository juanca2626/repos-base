import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { BackendCompoundSidebarResponse } from './sidebar.backend.types';

export interface CompoundSidebarService {
  (compoundId: string): Promise<ApiResponse<BackendCompoundSidebarResponse>>;
}
