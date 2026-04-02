// import { productApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { BackendSidebarResponse } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.backend.types';
import type { SidebarService } from '@/modules/negotiations/products/configuration/domain/sidebar/sidebar.service.interface';

import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType'; // temporal

export const fetchMarketingSidebar: SidebarService = async (
  _productSupplierId: string,
  _codeOrKey: string
): Promise<ApiResponse<BackendSidebarResponse>> => {
  const { isServiceTypeTrain, isServiceTypeMultiDays, isServiceTypeGeneral } =
    useSelectedServiceType(); // temporal
  // const response = await productApi.get(`product-suppliers/${productSupplierId}/configuration-progress?codeOrKey=${codeOrKey}`);

  let response: ApiResponse<BackendSidebarResponse>;

  // temporal
  if (isServiceTypeGeneral.value) {
    response = {
      success: true,
      code: 200,
      data: {
        totalProgress: 67,
        sections: [
          {
            code: 'shared',
            name: 'Compartido',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
            ],
          },
          {
            code: 'private',
            name: 'Privado',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
            ],
          },
          {
            code: 'images',
            name: 'Imágenes',
            status: 'PENDING',
            subSections: [],
          },
        ],
      },
    };
  } else if (isServiceTypeMultiDays.value) {
    response = {
      success: true,
      code: 200,
      data: {
        totalProgress: 67,
        sections: [
          {
            code: 'HIGH',
            name: 'Temporada baja',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
            ],
          },
          {
            code: 'MEDIUM',
            name: 'Temporada media',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
            ],
          },
          {
            code: 'images',
            name: 'Imágenes',
            status: 'PENDING',
            subSections: [],
          },
        ],
      },
    };
  } else if (isServiceTypeTrain.value) {
    response = {
      success: true,
      code: 200,
      data: {
        totalProgress: 67,
        sections: [
          {
            code: 'VISTADOME',
            name: 'Vistadome',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
              {
                code: 'images',
                name: 'Imágenes',
                status: 'PENDING',
              },
            ],
          },
          {
            code: 'ANDEAN',
            name: 'Andean',
            status: 'PENDING',
            subSections: [
              {
                code: 'content',
                name: 'Contenido',
                status: 'PENDING',
              },
              {
                code: 'translations',
                name: 'Traducciones',
                status: 'PENDING',
              },
              {
                code: 'images',
                name: 'Imágenes',
                status: 'PENDING',
              },
            ],
          },
        ],
      },
    };
  } else {
    response = {
      success: true,
      code: 200,
      data: { totalProgress: 0, sections: [] },
    };
  }
  // temporal

  return response;
};
