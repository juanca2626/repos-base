import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { BackendSidebarResponse } from './sidebar.backend.types';

export interface SidebarService {
  (productSupplierId: string, codeOrKey: string): Promise<ApiResponse<BackendSidebarResponse>>;
}
