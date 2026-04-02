import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { useQuery } from '@tanstack/vue-query';
import type { Ref } from 'vue';
import { computed } from 'vue';

export function useZoneLocationsQuery(
  countryId: Ref<number | null>,
  stateId: Ref<number | null>,
  cityId: Ref<number | null>
) {
  const { fetchZoneLocations } = useSupplierResourceService;

  return useQuery({
    queryKey: computed(() => ['zoneLocations', countryId.value, stateId.value, cityId.value]),
    queryFn: () => fetchZoneLocations(countryId.value!, stateId.value, cityId.value, 0),
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    enabled: computed(() => !!countryId.value),
  });
}
