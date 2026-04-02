import { computed, unref } from 'vue';
import { useQuery, type UseQueryOptions } from '@tanstack/vue-query';
import type { Ref } from 'vue';

import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import {
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
  type SupplierCompleteDataResponse,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';

/**
 * Query RAÍZ para el formulario de edición de proveedor.
 *
 * Realiza UNA sola petición HTTP a `supplier/complete-data/{id}` con TODOS
 * los keys necesarios. Los composables individuales (classification, general,
 * location, contact) NO hacen peticiones propias en modo edición: leen del
 * cache que este query llena usando `useQueryClient().getQueryData()`.
 *
 * Debe montarse en `form.vue` ANTES que los componentes hijos para que el
 * cache esté disponible cuando cada composable lo consulte.
 */
export const useSupplierEditDataQuery = (
  supplierId: Ref<number | null | undefined> | number | null | undefined,
  classificationId?: Ref<number | null | undefined> | number | null | undefined,
  options?: Omit<UseQueryOptions<SupplierCompleteDataResponse>, 'queryKey' | 'queryFn'>
) => {
  const supplierIdValue = computed(() => unref(supplierId));
  const classificationIdValue = computed(() => unref(classificationId));

  return useQuery<SupplierCompleteDataResponse>({
    queryKey: computed(() => getSupplierEditQueryKey(supplierIdValue.value!)),
    queryFn: async () => {
      if (!supplierIdValue.value) throw new Error('Supplier ID is required');

      const params: Record<string, any> = {
        keys: [...SUPPLIER_EDIT_KEYS],
      };

      if (classificationIdValue.value) {
        params.supplier_classification_id = classificationIdValue.value;
      }

      return useSupplierService.showSupplierCompleteData(supplierIdValue.value, params);
    },
    enabled: computed(() => !!supplierIdValue.value),
    staleTime: 5 * 60 * 1000, // 5 min — igual que los composables anteriores
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
    networkMode: 'online',
    ...options,
  });
};
