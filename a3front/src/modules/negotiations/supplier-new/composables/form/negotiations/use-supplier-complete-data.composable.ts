import { computed } from 'vue';
import { useQuery, useQueryClient } from '@tanstack/vue-query';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import {
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
  type SupplierCompleteDataResponse,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

/**
 * Composable de conveniencia — punto de entrada del cache compartido.
 *
 * En modo edición:
 * - Lanza UNA sola petición HTTP con TODOS los keys necesarios.
 * - Expone `completeData` como ref REACTIVO (los watchers en composables hijos
 *   se disparan cuando llega la respuesta del servidor).
 * - Expone `isLoading` para el spinner de form.vue.
 * - Expone `refetch` via invalidateQueries → 1 sola petición de refresco.
 *
 * En modo registro: query desactivada (sin supplierId).
 */
export function useSupplierCompleteData() {
  const { supplierId } = useSupplierGlobalComposable();
  const queryClient = useQueryClient();

  // Query raíz unificada — mismo queryKey que usan los composables v2.
  // Vue Query deduplica: cero HTTP extra, pero sí reactividad correcta.
  const { isLoading, isFetching, isError, error, data } = useQuery<SupplierCompleteDataResponse>({
    queryKey: computed(() =>
      supplierId.value
        ? getSupplierEditQueryKey(supplierId.value)
        : (['supplier', 'edit-complete', '__disabled__'] as const)
    ),
    queryFn: async () => {
      if (!supplierId.value) throw new Error('Supplier ID is required');

      const params: Record<string, any> = {
        keys: [...SUPPLIER_EDIT_KEYS],
        exclude_zone: 1,
      };

      return useSupplierService.showSupplierCompleteData(supplierId.value, params);
    },
    enabled: computed(() => !!supplierId.value),
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
  });

  // refetch semántico: invalida cache → Vue Query dispara 1 sola petición de refresco
  const refetch = async () => {
    if (!supplierId.value) return;
    await queryClient.invalidateQueries({
      queryKey: getSupplierEditQueryKey(supplierId.value),
    });
  };

  return {
    completeData: data, // Ref reactivo — los watchers en composables hijos se disparan correctamente
    isLoading,
    isFetching,
    isError,
    error,
    refetch,
  };
}
