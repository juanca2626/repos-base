import { ref, type Ref } from 'vue';
import { message } from 'ant-design-vue';
import { useRoute } from 'vue-router';
import { useServiceContentRequest } from './useServiceContentRequest';
import { saveServiceContent } from '../services/serviceContent.service';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import type { EnrichedGenericContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/enriched-generic-content-response.interface';
import type { GenericContentResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/generic-content-response.interface';
interface UseServiceContentFormActionProps {
  currentKey: string;
  currentCode: string;
  currentItemId: string;
}

export const useServiceContentFormAction = (
  formState: any,
  groupedSchedules: Ref<any[]>,
  inclusions: Ref<Inclusion[]>,
  requirements: Ref<Requirement[]>,
  props: UseServiceContentFormActionProps,
  setCompletedItem: (key: string, code: string, item: string) => void
) => {
  const route = useRoute();
  const genericStore = useGenericConfigurationStore();
  const isLoadingButton = ref<boolean>(false);

  const updateStoreAfterSave = (savedContent: EnrichedGenericContentResponse) => {
    genericStore.updateServiceContent(savedContent);
    setCompletedItem(props.currentKey, props.currentCode, props.currentItemId);
  };

  const handleSaveAndAdvance = async () => {
    try {
      isLoadingButton.value = true;

      const productSupplierId = route.params.id as string;
      if (!productSupplierId) {
        throw new Error('Product supplier ID no encontrado en la ruta');
      }

      const serviceDetailsId = genericStore.getServiceDetailId(props.currentKey, props.currentCode);

      if (!serviceDetailsId) {
        throw new Error('Service details ID no encontrado');
      }

      // Usar .value al guardar para enviar los inclusions/requirements actuales (tras cargar en editar)
      const { buildRequest } = useServiceContentRequest(
        formState,
        groupedSchedules.value,
        inclusions.value,
        requirements.value
      );

      const request = buildRequest();

      const response = await saveServiceContent(productSupplierId, serviceDetailsId, request);

      const enrichedContent = {
        id: serviceDetailsId,
        groupingKeys: {
          operatingLocationKey: props.currentKey,
          supplierCategoryCode: props.currentCode,
        },
        ...(response as GenericContentResponse),
      };

      updateStoreAfterSave(enrichedContent);
    } catch (error) {
      console.error('Error al guardar:', error);
      message.error('Ocurrió un error al guardar el contenido. Intenta nuevamente.');
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
