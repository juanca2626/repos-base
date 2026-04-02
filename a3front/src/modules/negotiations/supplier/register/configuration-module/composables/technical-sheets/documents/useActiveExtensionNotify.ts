import { computed, ref, watch } from 'vue';
import type { ModalEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type {
  ActiveExtensionNotifyProps,
  ExtensionSummary,
  ExtensionSummaryResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface.ts';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { extensionFormDataMap } from '@/modules/negotiations/supplier/register/configuration-module/constants/extension-form-data-map';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export const useActiveExtensionNotify = (
  emit: ModalEmitTypeInterface,
  props: ActiveExtensionNotifyProps
) => {
  const extensionSummaries = ref<ExtensionSummary[]>([]);
  const isLoading = ref<boolean>(false);

  const extensionFormData = extensionFormDataMap[props.typeTechnicalSheet];

  const isMultipleExtension = computed(() => {
    return props.documentExtensionIds.length > 1;
  });

  const firstExtension = computed(() =>
    extensionSummaries.value.length > 0 ? extensionSummaries.value[0] : null
  );

  const isTransportVehicle = computed(() => {
    return props.typeTechnicalSheet === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE;
  });

  const initData = () => {
    extensionSummaries.value = [];
  };

  const handleCancel = () => {
    initData();
    emit('update:showModal', false);
  };

  const showExtensionSummary = async () => {
    try {
      isLoading.value = true;

      const { data } = await technicalSheetApi.get<ApiResponse<ExtensionSummaryResponse[]>>(
        `${extensionFormData.resource}/show-summary`,
        {
          params: {
            ids: props.documentExtensionIds,
          },
        }
      );

      setData(data.data);
    } catch (error: any) {
      handleError(error);
      console.error('Error extension show summary:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const setData = async (data: ExtensionSummaryResponse[]) => {
    data.forEach((row) => {
      extensionSummaries.value.push({
        user: row.user,
        dateTo: row.date_to,
        reason: row.reason,
        typeDocument: row.type_vehicle_driver_document ??
          row.type_vehicle_document ?? { id: -1, name: 'Unknown' },
      });
    });
  };

  watch(
    () => props.showModal,
    (value) => {
      if (value) {
        showExtensionSummary();
      }
    }
  );

  return {
    isMultipleExtension,
    isLoading,
    extensionSummaries,
    firstExtension,
    isTransportVehicle,
    handleCancel,
  };
};
