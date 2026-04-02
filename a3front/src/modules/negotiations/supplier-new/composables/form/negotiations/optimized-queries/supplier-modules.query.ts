import { computed, unref } from 'vue';
import { useQuery, type UseQueryOptions } from '@tanstack/vue-query';
import type { Ref } from 'vue';

import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

export interface SupplierModulesParams {
  supplier_id?: number | null;
  supplier_sub_classification_id?: number | null;
}

export interface ModuleItem {
  key: string;
  label: string;
  icon?: any;
  isActive?: boolean;
  isComplete?: boolean;
  disabled?: boolean;
}

export interface SubPanel {
  key: string;
  label: string;
  items: ModuleItem[];
}

export interface PanelModule {
  key: string;
  label: string;
  icon?: any;
  subPanels: SubPanel[];
}

export interface ModuleOptions {
  supplier?: {
    status?: Record<string, string>;
    progress?: Record<string, number>;
  };
  modules?: {
    status?: Record<string, string>;
    progress?: Record<string, number>;
  };
}

export interface SupplierModulesResponse {
  success: boolean;
  data: {
    modules: Record<string, PanelModule>;
    options: ModuleOptions;
  };
}

export const supplierModulesQueryKeys = {
  all: ['supplier-modules'] as const,

  detail: (params?: SupplierModulesParams) => [...supplierModulesQueryKeys.all, params] as const,

  empty: () => [...supplierModulesQueryKeys.all, 'empty'] as const,
};

export const useSupplierModulesQuery = (
  params?: Ref<SupplierModulesParams | undefined> | SupplierModulesParams,
  options?: Omit<UseQueryOptions<SupplierModulesResponse>, 'queryKey' | 'queryFn'>
) => {
  const paramsValue = computed(() => unref(params));

  return useQuery({
    queryKey: computed(() => {
      const p = paramsValue.value;
      if (!p?.supplier_id) {
        return supplierModulesQueryKeys.empty();
      }
      return supplierModulesQueryKeys.detail(p);
    }),
    queryFn: async () => {
      const p = paramsValue.value;

      const response = await useSupplierService.showModules({
        supplier_id: p?.supplier_id ?? null,
        supplier_sub_classification_id: p?.supplier_sub_classification_id ?? null,
      });

      return response as SupplierModulesResponse;
    },
    staleTime: 5 * 60 * 1000, // 5 minutos - evita llamadas duplicadas
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false, // Cambiar a false para evitar llamadas múltiples
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};

export const useSupplierModulesEmptyQuery = (
  options?: Omit<UseQueryOptions<SupplierModulesResponse>, 'queryKey' | 'queryFn'>
) => {
  return useSupplierModulesQuery(
    { supplier_id: null, supplier_sub_classification_id: null },
    options
  );
};

export const useSupplierModulesQueries = () => {
  return {
    queryKeys: supplierModulesQueryKeys,
    useModules: useSupplierModulesQuery,
    useModulesEmpty: useSupplierModulesEmptyQuery,
  };
};

export const extractPanelModules = (
  data: SupplierModulesResponse | undefined,
  previousState?: any[]
): any[] => {
  if (!data?.data?.modules) return [];

  const modules = Object.values(data.data.modules);
  const options = extractModuleOptions(data);

  // Crear un mapa del estado anterior por key para búsqueda rápida
  const previousItemsMap = new Map<string, boolean>();
  if (previousState) {
    previousState.forEach((panel) => {
      panel.subPanels?.forEach((subPanel: any) => {
        subPanel.items?.forEach((item: any) => {
          if (item.isComplete) {
            previousItemsMap.set(item.key, true);
          }
        });
      });
    });
  }

  // Marcar items como completados basándose en el progress del backend O el estado anterior

  return modules.map((panel: any) => {
    const processedSubPanels =
      panel.subPanels?.map((subPanel: any) => {
        const processedItems =
          subPanel.items?.map((item: any) => {
            // Buscar el progress del item en el backend
            // El backend retorna las keys SIN el prefijo "supplier-" (ej: "classification", "general_information")
            const progress = options.supplier?.progress?.[item.key];

            // Verificar si ya estaba completo anteriormente (preservar estado local)
            const wasComplete = previousItemsMap.get(item.key) || false;

            // IMPORTANTE: Preservar el estado de wasComplete si era true, independientemente del progress
            // Esto asegura que los items completados no se desmarcuen al navegar entre páginas
            // Solo marcar como completo si:
            // 1. El progress del backend es 100, O
            // 2. Ya estaba marcado como completo anteriormente (wasComplete), O
            // 3. El backend retorna isComplete: true directamente en el item (ej: general_information con progress 86%)
            const isComplete = progress === 100 || wasComplete || item.isComplete === true;

            return {
              ...item,
              isComplete,
              isActive: false, // En modo edición, ningún item debe estar activo inicialmente
            };
          }) || [];

        // Calcular complete/total para el subPanel
        const totalItems = processedItems.length;
        const completedItems = processedItems.filter((item: any) => item.isComplete).length;

        return {
          ...subPanel,
          items: processedItems,
          complete: completedItems,
          total: totalItems,
        };
      }) || [];

    // Calcular el progreso total del panel basado en items completados
    const totalSubPanelItems = processedSubPanels.reduce(
      (sum: number, sp: any) => sum + (sp.total || 0),
      0
    );
    const completedSubPanelItems = processedSubPanels.reduce(
      (sum: number, sp: any) => sum + (sp.complete || 0),
      0
    );
    const panelProgress =
      totalSubPanelItems > 0 ? Math.round((completedSubPanelItems / totalSubPanelItems) * 100) : 0;

    return {
      ...panel,
      subPanels: processedSubPanels,
      progress: panelProgress,
    };
  });
};

export const extractModuleOptions = (data: SupplierModulesResponse | undefined): ModuleOptions => {
  return data?.data?.options || {};
};

export const isModuleComplete = (
  data: SupplierModulesResponse | undefined,
  moduleType: 'supplier' | 'modules',
  moduleKey: string
): boolean => {
  const options = extractModuleOptions(data);
  const progress = options[moduleType]?.progress?.[`${moduleType}-${moduleKey}`];
  return progress === 100;
};

export const getModuleProgress = (
  data: SupplierModulesResponse | undefined,
  moduleType: 'supplier' | 'modules',
  moduleKey: string
): number => {
  const options = extractModuleOptions(data);
  return options[moduleType]?.progress?.[`${moduleType}-${moduleKey}`] || 0;
};

export const getModuleStatus = (
  data: SupplierModulesResponse | undefined,
  moduleType: 'supplier' | 'modules',
  moduleKey: string
): string => {
  const options = extractModuleOptions(data);
  return options[moduleType]?.status?.[`${moduleType}-${moduleKey}`] || '';
};
