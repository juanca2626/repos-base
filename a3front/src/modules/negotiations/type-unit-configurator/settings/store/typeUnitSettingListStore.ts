import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type {
  TypeUnitTransportTransfer,
  TypeUnitTransportTransferResponse,
} from '@/modules/negotiations/type-unit-configurator/settings/interfaces';
import { buildPeriodDateRange } from '@/modules/negotiations/type-unit-configurator/helpers/periodHelper';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';
import { handleError } from '@/modules/negotiations/api/responseApi';

export const useTypeUnitSettingListStore = defineStore('typeUnitSettingListStore', () => {
  const resource = 'unit-settings/transfers';
  const isLoading = ref<boolean>(false);

  const { showNotificationError } = useNotifications();

  const initTransferData: TypeUnitTransportTransfer = {
    id: null,
    typeUnitTransportSettingId: null,
    transferId: null,
    settingDetails: [],
  };

  const transferData = reactive<TypeUnitTransportTransfer>({
    ...initTransferData,
  });

  const resetTransferData = () => {
    Object.assign(transferData, structuredClone(initTransferData));
  };

  const fetchTransferData = async (
    periodYear: number,
    typeUnitTransportLocationId: number,
    transferId: number
  ) => {
    isLoading.value = true;

    const period = buildPeriodDateRange(periodYear);

    try {
      const response = await supportApi.get<ApiResponse<TypeUnitTransportTransferResponse>>(
        `${resource}/show`,
        {
          params: {
            typeUnitTransportLocationId: typeUnitTransportLocationId,
            dateFrom: period.dateFrom,
            dateTo: period.dateTo,
            transferId: transferId,
          },
        }
      );

      if (!response.data.data) {
        resetTransferData();
        showNotificationError('No se encontraron registros con los filtros especificados.');
        return;
      }

      transformTransferData(response.data.data);
    } catch (error: any) {
      console.error('Error fetching data:', error);
      handleError(error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformTransferData = (responseData: TypeUnitTransportTransferResponse) => {
    Object.assign(transferData, {
      id: responseData.id,
      typeUnitTransportSettingId: responseData.type_unit_transport_setting_id,
      transferId: responseData.transfer.id,
      settingDetails: responseData.setting_details.map((row) => {
        return {
          id: row.id,
          typeUnitTransport: row.type_unit_transport,
          minimumCapacity: row.minimum_capacity,
          maximumCapacity: row.maximum_capacity,
          representativeQuantity: row.representative_quantity,
          trunkCarQuantity: row.trunk_car_quantity,
          trunkRepresentativeQuantity: row.trunk_representative_quantity,
          quantityGuides: row.quantity_guides,
          quantityUnitsRequired: row.quantity_units_required,
        };
      }),
    });
  };

  return {
    isLoading,
    transferData,
    resetTransferData,
    fetchTransferData,
  };
});
