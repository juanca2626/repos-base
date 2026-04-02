import { ref } from 'vue';
import { useRoute } from 'vue-router';
import {
  saveServiceDetails,
  updateServiceDetails,
} from '@/modules/negotiations/products/configuration/content/package/serviceDetails/services/serviceDetails.service';
import { mapApiSchedulesToScheduleData } from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-mapper.utils';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';

interface UseServiceDetailsFormActionsProps {
  currentKey: string;
  currentCode: string;
  currentItemId: string;
}

export const useServiceDetailsFormActions = (
  validateForm: () => Promise<void>,
  buildServiceDetailsRequest: () => any,
  setCompletedItem: (currentKey: string, currentCode: string, itemId: string) => void,
  props: UseServiceDetailsFormActionsProps
) => {
  const route = useRoute();
  const packageStore = usePackageConfigurationStore();
  const isLoadingButton = ref(false);

  const updateStoreAfterSave = (savedDetail: any) => {
    // Actualizar el store con la respuesta
    packageStore.updateServiceDetail(savedDetail);

    // Actualizar los schedules en el store si vienen en la respuesta
    if (savedDetail.content?.operability?.schedules) {
      const mappedScheduleData = mapApiSchedulesToScheduleData(
        savedDetail.content.operability.schedules,
        savedDetail.content.operability.mode
      );

      if (props.currentKey && props.currentCode) {
        packageStore.setServiceDetailsSchedule(
          props.currentKey,
          props.currentCode,
          mappedScheduleData
        );
      }
    }

    if (props.currentKey && props.currentCode) {
      setCompletedItem(props.currentKey, props.currentCode, props.currentItemId);
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

      const existingServiceDetail = packageStore.getServiceDetail(
        request.groupingKeys.programDurationCode,
        request.groupingKeys.operationalSeasonCode
      );

      if (existingServiceDetail) {
        const savedDetail = await updateServiceDetails(productSupplierId, request);
        updateStoreAfterSave(savedDetail);
      } else {
        const savedDetail = await saveServiceDetails(productSupplierId, request);
        updateStoreAfterSave(savedDetail);
      }
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
