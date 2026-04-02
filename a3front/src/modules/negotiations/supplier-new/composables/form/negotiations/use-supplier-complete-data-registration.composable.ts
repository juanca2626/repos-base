import { computed } from 'vue';
import { useSupplierCompleteDataRegistrationQuery } from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';

/**
 * Composable para obtener los datos completos de recursos del proveedor en modo REGISTRO
 * (cuando NO existe un supplier_id todavía).
 *
 * Este hook llama al endpoint: GET /api/v1/neg/supplier-ms/supplier/complete-data
 * que retorna únicamente los recursos necesarios para el formulario de registro.
 *
 * @example
 * ```ts
 * const { completeData, isLoading, chains, classifications, phoneCountries } = useSupplierCompleteDataRegistration();
 * ```
 */
export function useSupplierCompleteDataRegistration() {
  const queryParams = computed(() => {
    return {
      keys: [
        'chains',
        'classifications',
        'countries_phone',
        'contacts_states',
        'commercial_resources',
        'locations',
      ],
    };
  });

  const {
    data: completeData,
    isLoading,
    isFetching,
    isError,
    error,
    refetch,
  } = useSupplierCompleteDataRegistrationQuery(queryParams, {
    retry: 1,
    staleTime: 5 * 60 * 1000, // 5 minutos - estos datos son estáticos
  });

  // Computed properties para acceder fácilmente a los recursos
  const chains = computed(() => completeData.value?.data?.data?.resources?.chains?.chains || []);

  const classifications = computed(
    () => completeData.value?.data?.data?.resources?.classifications?.supplierClassification || []
  );

  const subClassifications = computed(
    () =>
      completeData.value?.data?.data?.resources?.classifications?.supplierSubClassification || []
  );

  const phoneCountries = computed(
    () => completeData.value?.data?.data?.resources?.countries_phone?.countries_phone || []
  );

  const typeContacts = computed(
    () => completeData.value?.data?.data?.resources?.contacts_states?.typeContacts || []
  );

  const states = computed(
    () => completeData.value?.data?.data?.resources?.contacts_states?.states || []
  );

  const typeFoods = computed(
    () => completeData.value?.data?.data?.resources?.commercial_resources?.typeFoods || []
  );

  const amenities = computed(
    () => completeData.value?.data?.data?.resources?.commercial_resources?.amenities || []
  );

  const locations = computed(() => completeData.value?.data?.data?.resources?.locations || []);

  const zoneLocations = computed(
    () => completeData.value?.data?.data?.resources?.zone_locations || []
  );

  return {
    // Raw data
    completeData,
    isLoading,
    isFetching,
    isError,
    error,
    refetch,

    // Computed resources
    chains,
    classifications,
    subClassifications,
    phoneCountries,
    typeContacts,
    states,
    typeFoods,
    amenities,
    locations,
    zoneLocations,
  };
}
