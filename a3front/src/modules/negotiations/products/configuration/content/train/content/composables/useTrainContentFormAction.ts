import { ref, type Ref } from 'vue';
import { useRoute } from 'vue-router';
import { useTrainContentRequest } from './useTrainContentRequest';
import { saveTrainServiceContent } from '../services/trainContent.service';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import type { EnrichedTrainContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/enriched-train-content-response.interface';
interface UseTrainContentFormActionProps {
  currentKey: string;
  currentCode: string;
  currentItemId: string;
}

export const useTrainContentFormAction = (
  textRemark: Ref<string>,
  inclusions: Inclusion[] | Ref<Inclusion[]>,
  requirements: Requirement[] | Ref<Requirement[]>,
  props: UseTrainContentFormActionProps,
  setCompletedItem: (key: string, code: string, item: string) => void
) => {
  const route = useRoute();
  const trainStore = useTrainConfigurationStore();
  const isLoadingButton = ref<boolean>(false);

  const updateStoreAfterSave = (savedContent: EnrichedTrainContentResponse) => {
    if (props.currentKey && props.currentCode) {
      trainStore.updateServiceContent(savedContent);
      setCompletedItem(props.currentKey, props.currentCode, props.currentItemId);
    }
  };

  const handleSaveAndAdvance = async () => {
    try {
      isLoadingButton.value = true;

      const textValue = typeof textRemark === 'string' ? textRemark : textRemark.value;
      const inclusionsValue = Array.isArray(inclusions) ? inclusions : inclusions.value;
      const requirementsValue = Array.isArray(requirements) ? requirements : requirements.value;

      const { buildRequest } = useTrainContentRequest(
        textValue,
        inclusionsValue,
        requirementsValue
      );

      const request = buildRequest();

      const productSupplierId = route.params.id as string;
      if (!productSupplierId) {
        throw new Error('Product supplier ID no encontrado en la ruta');
      }

      const serviceDetailsId = trainStore.getServiceDetailId(props.currentKey, props.currentCode);

      if (!serviceDetailsId) {
        throw new Error('Service details ID no encontrado');
      }

      const response = await saveTrainServiceContent(productSupplierId, serviceDetailsId, request);

      const enrichedContent = {
        id: serviceDetailsId,
        groupingKeys: {
          operatingLocationKey: props.currentKey,
          trainTypeCode: props.currentCode,
        },
        ...response,
      };

      updateStoreAfterSave(enrichedContent);
    } catch (error) {
      console.error('Error al guardar:', error);
      throw error;
    } finally {
      isLoadingButton.value = false;
    }
  };

  return {
    isLoadingButton,
    handleSaveAndAdvance,
  };
};
