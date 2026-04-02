import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';

export const useCommercialLocationStore = defineStore('commercialLocationStore', () => {
  const initialFormData = ref<any>({
    commercial_locations: undefined,
    commercial_address: undefined,
    fiscal_address: undefined,
    geolocation: { lat: -12.046374, lng: -77.042793 },
  });
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const locations = ref<[]>([]);
  const locationsLoaded = ref<boolean>(false);
  const zones = ref<[]>([]);
  const zonesDisabled = ref<boolean>(true);
  const zonesLoaded = ref<boolean>(false);

  const { fetchLocations } = useSupplierResourceService;

  const loadLocations = async (country_id: number, exclude_zone = 0) => {
    const { data } = await fetchLocations(country_id, exclude_zone);
    return data;
  };

  return {
    initialFormData,
    formState,
    formRef,
    locations,
    locationsLoaded,
    zones,
    zonesDisabled,
    zonesLoaded,
    loadLocations,
  };
});
