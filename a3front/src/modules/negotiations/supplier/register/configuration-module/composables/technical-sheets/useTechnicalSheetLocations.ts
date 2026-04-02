import { ref, reactive } from 'vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type {
  OperationLocationData,
  OperationLocationResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import {
  joinKeyOperationLocation,
  joinOptionalLocationNames,
} from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useTechnicalSheetLocations() {
  const locationData = ref<OperationLocationData[]>([]);
  const isLoading = ref<boolean>(false);

  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const selectedLocation = reactive<OperationLocationData>({} as OperationLocationData);

  const handleTabClick = (item: OperationLocationData) => {
    Object.assign(selectedLocation, item);
  };

  const resetSelectedLocation = () => {
    Object.keys(selectedLocation).forEach((key) => {
      delete selectedLocation[key as keyof OperationLocationData];
    });
  };

  const fetchLocationData = async () => {
    isLoading.value = true;

    try {
      const response = await technicalSheetApi.get(
        'supplier-transport-vehicles/operating-locations',
        {
          params: {
            subClassificationSupplierId: subClassificationSupplierId.value,
          },
        }
      );

      locationData.value = transformData(response.data.data);
    } catch (error: any) {
      handleError(error);
      console.error('Error fetching technical sheet location data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformData = (apiData: OperationLocationResponse[]): OperationLocationData[] => {
    return apiData.map((item) => {
      return {
        supplier_branch_office_id: item.supplier_branch_office_id,
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

  return {
    isLoading,
    locationData,
    selectedLocation,
    handleTabClick,
    fetchLocationData,
    resetSelectedLocation,
  };
}
