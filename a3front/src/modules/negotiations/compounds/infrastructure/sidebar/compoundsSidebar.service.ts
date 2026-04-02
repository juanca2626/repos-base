import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { BackendCompoundSidebarResponse } from '@/modules/negotiations/compounds/domain/sidebar/sidebar.backend.types';
import type { CompoundSidebarService } from '@/modules/negotiations/compounds/domain/sidebar/sidebar.service.interface';

export const fetchCompoundsSidebar: CompoundSidebarService = async (
  _compoundId: string
): Promise<ApiResponse<BackendCompoundSidebarResponse>> => {
  const response: ApiResponse<BackendCompoundSidebarResponse> = {
    success: true,
    code: 200,
    data: {
      totalProgress: 0,
      sections: [
        // SECCIÓN con subitems → ícono de bus en modo colapsado
        {
          code: 'compound-structure',
          name: 'Estructura para compuestos',
          status: 'IN_PROGRESS',
          subSections: [
            { code: 'datos-config', name: 'Datos y Configuración', status: 'PENDING' },
            { code: 'estructura', name: 'Estructura', status: 'PENDING' },
            { code: 'calculadora', name: 'Calculadora de costos', status: 'PENDING' },
          ],
        },
        // ÍTEMS planos → ícono check circle en modo colapsado
        {
          code: 'general-info',
          name: 'Información general',
          status: 'COMPLETED',
          subSections: [],
        },
        {
          code: 'included-services',
          name: 'Servicios incluidos',
          status: 'PENDING',
          subSections: [],
        },
        {
          code: 'restrictions',
          name: 'Restricciones',
          status: 'PENDING',
          subSections: [],
        },
        // SECCIÓN con subitems → ícono de bus en modo colapsado
        {
          code: 'pricing',
          name: 'Precios y tarifas',
          status: 'PENDING',
          subSections: [
            { code: 'pricing-base', name: 'Tarifas base', status: 'PENDING' },
            { code: 'pricing-extra', name: 'Cargos adicionales', status: 'PENDING' },
          ],
        },
      ],
    },
  };

  return response;
};
