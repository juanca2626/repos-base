import { computed, unref } from 'vue';
import { useQuery, useMutation, useQueryClient, type UseQueryOptions } from '@tanstack/vue-query';
import type { Ref } from 'vue';

import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { directSupportApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';

export interface PersonLimit {
  min: number;
  max: number;
  _id: string;
}

export interface AppliesTo {
  code: string;
  name: string;
  personLimit: PersonLimit;
  _id: string;
}

export interface PolicySegmentation {
  code: string;
  name: string;
  _id: string;
}

export interface SupportData {
  _id: string;
  code: string;
  appliesTo: AppliesTo[];
  policySegmentation: PolicySegmentation[];
  createdAt: string;
  updatedAt: string;
  __v: number;
}

export interface SupplierPoliciesResponse {
  success: boolean;
  data: SupportData;
}

export interface SupplierPolicyListOption {
  _id: string;
  name: string;
  [key: string]: any;
}

export interface SupplierPolicyListOptionsResponse {
  success: boolean;
  data: SupplierPolicyListOption[];
}

export const supplierPoliciesQueryKeys = {
  all: ['supplier', 'policies'] as const,

  supportsByCode: (code: string = 'default') =>
    [...supplierPoliciesQueryKeys.all, 'supports', code] as const,

  listOptionsBySupplier: (supplierId: string | number) =>
    [...supplierPoliciesQueryKeys.all, 'list-options', supplierId] as const,
};

export const useSupplierPoliciesQuery = (
  code?: Ref<string | undefined> | string,
  options?: Omit<UseQueryOptions<SupplierPoliciesResponse>, 'queryKey' | 'queryFn'>
): ReturnType<typeof useQuery<SupplierPoliciesResponse>> => {
  const codeValue = computed(() => unref(code) || 'default');

  return useQuery({
    queryKey: computed(() => supplierPoliciesQueryKeys.supportsByCode(codeValue.value)),
    queryFn: async () => {
      const response = await useSupplierService.showSupportsByCode(codeValue.value);
      return response;
    },
    enabled: true,
    staleTime: 5 * 60 * 1000, // 5 minutos
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};

export const useSupplierPoliciesQueries = (): {
  queryKeys: typeof supplierPoliciesQueryKeys;
  usePolicies: typeof useSupplierPoliciesQuery;
} => {
  return {
    queryKeys: supplierPoliciesQueryKeys,
    usePolicies: useSupplierPoliciesQuery,
  };
};

/**
 * Extrae businessGroups y segmentations del response
 * businessGroups se llena con appliesTo
 * segmentations se llena con policySegmentation
 */
export const extractPoliciesResources = (data: SupplierPoliciesResponse | undefined) => {
  return {
    businessGroups: data?.data.appliesTo || [],
    segmentations: data?.data.policySegmentation || [],
  };
};

/**
 * Mapea los datos de appliesTo al formato esperado por mapItemsToOptions
 * Convierte { code, name, _id, personLimit } a { id, name, personLimit }
 */
export const mapAppliesToToSelectFormat = (appliesTo: AppliesTo[]) => {
  return appliesTo.map((item) => ({
    id: item.code,
    name: item.name,
    personLimit: item.personLimit,
  }));
};

/**
 * Mapea código de segmentación a su ID numérico del enum
 */
const segmentationCodeToId: Record<string, number> = {
  markets: 1,
  clients: 2,
  series: 3,
  events: 4,
  'service-types': 5,
  serviceTypes: 5,
  service_types: 5,
  seasons: 6,
};

/**
 * Mapea los datos de policySegmentation al formato esperado por mapItemsToOptions
 * Convierte { code, name, _id } a { id: number, name }
 */
export const mapPolicySegmentationToSelectFormat = (policySegmentation: PolicySegmentation[]) => {
  return policySegmentation.map((item) => ({
    id: segmentationCodeToId[item.code] || item.code,
    name: item.name,
  }));
};

/**
 * Elimina una política por su ID
 * DELETE /api/v1/policies/:id
 */
async function deletePolicy(id: string): Promise<{ success: boolean; message?: string }> {
  const response = await directSupportApi.delete(`policies/${id}`);
  return response.data;
}

/**
 * Hook para eliminar una política usando Vue Query mutation
 * @param options - Opciones adicionales para la mutación
 */
export const useDeletePolicyMutation = (options?: {
  onSuccess?: (data: any, id: string) => void;
  onError?: (error: any, id: string) => void;
}) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => deletePolicy(id),
    onSuccess: (data, id) => {
      // Invalidar queries relacionadas con políticas
      queryClient.invalidateQueries({ queryKey: supplierPoliciesQueryKeys.all });
      options?.onSuccess?.(data, id);
    },
    onError: (error, id) => {
      options?.onError?.(error, id);
    },
  });
};

/**
 * Query para obtener las opciones de políticas de un proveedor específico
 * GET /api/v1/suppliers/:supplierId/policies
 */
export const useSupplierPolicyListOptionsQuery = (
  supplierId: Ref<string | number | null | undefined> | string | number | null | undefined,
  options?: Omit<UseQueryOptions<SupplierPolicyListOptionsResponse>, 'queryKey' | 'queryFn'>
): ReturnType<typeof useQuery<SupplierPolicyListOptionsResponse>> => {
  const supplierIdValue = computed(() => unref(supplierId));

  return useQuery({
    queryKey: computed(() =>
      supplierPoliciesQueryKeys.listOptionsBySupplier(supplierIdValue.value as string | number)
    ),
    queryFn: async () => {
      if (!supplierIdValue.value) {
        throw new Error('Supplier ID is required');
      }
      try {
        const response = await useSupplierPoliciesService.showSupplierPolicyListOptions(
          supplierIdValue.value
        );
        return response;
      } catch (error: any) {
        // Manejar errores 404 de manera silenciosa para que no se muestre el error técnico
        // El watcher en el composable manejará la UI apropiadamente
        if (error?.response?.status === 404) {
          // Retornar una respuesta vacía en lugar de lanzar el error
          return {
            success: true,
            data: [],
          } as SupplierPolicyListOptionsResponse;
        }
        // Para otros errores, lanzar normalmente
        throw error;
      }
    },
    enabled: computed(() => !!supplierIdValue.value),
    staleTime: 2 * 60 * 1000, // 2 minutos
    gcTime: 5 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};
