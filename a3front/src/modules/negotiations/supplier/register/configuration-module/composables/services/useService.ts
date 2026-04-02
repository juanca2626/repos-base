import { ref, onMounted, reactive } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import type {
  OperationLocationData,
  OperationLocationResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import {
  joinKeyOperationLocation,
  joinOptionalLocationNames,
} from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';

export function useService() {
  const locationData = ref<OperationLocationData[]>([]);
  const isLoading = ref<boolean>(false);

  const { configSubClassification } = useSupplierFormStoreFacade();

  const selectedLocation = reactive<OperationLocationData>({} as OperationLocationData);

  const handleTabClick = (item: OperationLocationData) => {
    Object.assign(selectedLocation, item);
  };

  const fetchLocationData = async () => {
    isLoading.value = true;

    try {
      const response = await supplierApi.get('supplier/operation-locations', {
        params: {
          supplierSubClassificationId: configSubClassification.value,
        },
      });

      locationData.value = transformData(response.data.data);
    } catch (error: any) {
      handleError(error);
      console.error('Error fetching supplier location data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformData = (apiData: OperationLocationResponse[]): OperationLocationData[] => {
    return apiData.map((item) => {
      return {
        ids: joinKeyOperationLocation(
          '-',
          item.country.id,
          item.state.id,
          item.city?.id,
          item.zone?.id
        ),
        country_id: item.country.id,
        state_id: item.state.id,
        city_id: item.city?.id ?? null,
        zone_id: item.zone?.id ?? null,
        display_name: joinOptionalLocationNames(
          ', ',
          undefined,
          item.state.name,
          item.city?.name,
          item.zone?.name
        ),
      };
    });
  };

  onMounted(async () => {
    await fetchLocationData();
  });

  return {
    isLoading,
    locationData,
    selectedLocation,
    handleTabClick,
  };
}
