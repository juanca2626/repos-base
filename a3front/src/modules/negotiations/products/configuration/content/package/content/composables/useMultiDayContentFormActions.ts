import { ref } from 'vue';
import { useRoute } from 'vue-router';
import type { MultiDayContentRequest } from '../interfaces';
import { saveServiceContent } from '../services/serviceContent.service';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import type { EnrichedMultidayContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/enriched-multiday-content-response.interface';
export interface UseMultiDayContentFormActionsProps {
  currentKey: string;
  currentCode: string;
  currentItemId: string;
}

export interface UseMultiDayContentFormActionsParams {
  buildContentRequest: () => MultiDayContentRequest;
  props: UseMultiDayContentFormActionsProps;
  setCompletedItem: (currentKey: string, currentCode: string, currentItemId: string) => void;
}

export const useMultiDayContentFormActions = ({
  buildContentRequest,
  props,
  setCompletedItem,
}: UseMultiDayContentFormActionsParams) => {
  const route = useRoute();
  const packageStore = usePackageConfigurationStore();
  const isLoadingButton = ref(false);

  const updateStoreAfterSave = (savedResponse: EnrichedMultidayContentResponse) => {
    const { currentKey, currentCode, currentItemId } = props;
    if (!currentKey || !currentCode || !currentItemId) return;

    packageStore.updateServiceContent(savedResponse);
    setCompletedItem(currentKey, currentCode, currentItemId);
  };

  const handleSave = async () => {
    isLoadingButton.value = true;
    try {
      const request = buildContentRequest();
      const productSupplierId = route.params.id as string;
      const serviceDetailsId = packageStore.getServiceDetailId(props.currentKey, props.currentCode);

      if (!productSupplierId) {
        throw new Error('Product supplier ID no encontrado en la ruta');
      }
      if (!serviceDetailsId) {
        throw new Error('Service detail ID no encontrado');
      }

      const savedResponse = await saveServiceContent(productSupplierId, serviceDetailsId, request);

      const enrichedContent = {
        id: serviceDetailsId,
        groupingKeys: {
          programDurationCode: props.currentKey,
          operationalSeasonCode: props.currentCode,
        },
        ...savedResponse,
      };

      updateStoreAfterSave(enrichedContent);
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSaveAndAdvance = async () => {
    try {
      await handleSave();
      // if (props.currentKey && props.currentCode) {
      //   setReadMode();
      //   formProgressStore.navigateToNextIncompleteItem(props.currentCode);
      // } else {
      //   setReadMode();
      //   saveAndAdvance();
      // }
    } catch (error) {
      console.error('Error al guardar contenido:', error);
      throw error;
    }
  };

  return {
    isLoadingButton,
    handleSave,
    handleSaveAndAdvance,
    updateStoreAfterSave,
  };
};
