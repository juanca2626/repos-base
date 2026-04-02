import { defineStore } from 'pinia';
import { ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { handleError } from '@/modules/negotiations/api/responseApi';
import type {
  LocationResource,
  ResourceData,
} from '@/modules/negotiations/type-unit-configurator/settings/interfaces';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type { SelectOption } from '@/modules/negotiations/type-unit-configurator/interfaces';

export const useTypeUnitSettingResourceStore = defineStore('typeUnitSettingResourceStore', () => {
  const isLoading = ref<boolean>(false);
  const typeUnitTransportLocations = ref<LocationResource[]>([]);
  const transfers = ref<SelectOption[]>([]);

  const fetchResources = async () => {
    try {
      isLoading.value = true;

      const { data } = await supportApi.get<ApiResponse<ResourceData>>('unit-settings/resources', {
        params: {
          keys: ['transfers', 'type_unit_transport_locations'],
        },
      });

      typeUnitTransportLocations.value = data.data.type_unit_transport_locations;
      transfers.value = mapItemsToOptions(data.data.transfers);
    } catch (error: any) {
      console.error('Error fetching resources:', error);
      handleError(error);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    typeUnitTransportLocations,
    transfers,
    fetchResources,
  };
});
