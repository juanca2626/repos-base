import { ref } from 'vue';
import { useRoute } from 'vue-router';
import type { UseServiceDetailsFormProps } from '@/modules/negotiations/products/configuration/content/shared/interfaces/serviceDetails';
import { saveServiceDetails } from '../services/serviceDetails.service';
import { mapApiSchedulesToScheduleData } from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-mapper.utils';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';

export const useServiceDetailsFormActions = (
  validateForm: () => Promise<void>,
  buildServiceDetailsRequest: () => any,
  setCompletedItem: (currentKey: string, currentCode: string, itemId: string) => void,
  props: UseServiceDetailsFormProps
) => {
  const route = useRoute();
  const configurationStore = useConfigurationStore();
  const isLoadingButton = ref(false);

  const updateStoreAfterSave = (savedDetail: any) => {
    // Actualizar el store con la respuesta
    configurationStore.updateServiceDetail(savedDetail);

    // Actualizar los schedules en el store si vienen en la respuesta
    if (savedDetail.content?.operability?.schedules) {
      const mappedScheduleData = mapApiSchedulesToScheduleData(
        savedDetail.content.operability.schedules,
        savedDetail.content.operability.mode
      );

      if (props.currentKey && props.currentCode) {
        configurationStore.setServiceDetailsSchedule(
          props.currentKey,
          props.currentCode,
          mappedScheduleData
        );
      }
    }

    if (props.currentKey && props.currentCode) {
      setCompletedItem(props.currentKey, props.currentCode, 'service-details');
    }
  };

  const handleSave = async () => {
    try {
      await validateForm();
      isLoadingButton.value = true;

      const request = buildServiceDetailsRequest();
      const productSupplierId = route.params.id as string;

      if (!productSupplierId) {
        throw new Error('Product supplier ID no encontrado en la ruta');
      }

      const savedDetail = await saveServiceDetails(productSupplierId, request);
      updateStoreAfterSave(savedDetail);
    } catch (error: any) {
      throw error;
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSaveAndAdvance = async () => {
    try {
      await handleSave();
    } catch (error) {
      console.error('Error al guardar:', error);
    }
  };

  return {
    isLoadingButton,
    handleSave,
    handleSaveAndAdvance,
  };
};
