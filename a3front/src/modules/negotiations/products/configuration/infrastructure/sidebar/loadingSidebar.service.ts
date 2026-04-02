import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

import type { BackendSidebarResponse } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.backend.types';
import type { SidebarService } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.service.interface';

export const fetchLoadingSidebar: SidebarService = async (
  productSupplierId: string,
  codeOrKey: string
): Promise<ApiResponse<BackendSidebarResponse>> => {
  const response = await productApi.get(
    `product-suppliers/${productSupplierId}/configuration-progress`,
    {
      params: { codeOrKey },
    }
  );

  return response.data;
};
