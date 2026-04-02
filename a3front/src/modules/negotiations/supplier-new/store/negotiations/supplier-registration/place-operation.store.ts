import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { v4 as uuidv4 } from 'uuid';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export const usePlaceOperationStore = defineStore('placeOperationStore', () => {
  const initialFormData = ref<any>({
    locations: [
      {
        countryStateLocations: [],
        zonesLocations: [],
        zonesLocationsDisabled: true,
        zonesLocationsLoading: false,
        supplierSubClassificationId: undefined,
        city: undefined,
        zone: undefined,
        id: uuidv4().replace(/-/g, ''),
      },
    ],
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const countryStateLocations = ref<[]>([]);
  const countryStateLocationsLoaded = ref<boolean>(false);

  const { fetchCountryStateLocations, fetchZoneLocations } = useSupplierResourceService;

  const loadCountryStateLocations = async (country_id: number) => {
    const { data } = await fetchCountryStateLocations(country_id);
    return data;
  };

  const loadZoneLocations = async (country_id: number, city_id: number) => {
    const { data } = await fetchZoneLocations(country_id, city_id);
    return data;
  };

  return {
    initialFormData,
    formState,
    formRef,
    countryStateLocations,
    countryStateLocationsLoaded,
    loadCountryStateLocations,
    loadZoneLocations,
  };
});
