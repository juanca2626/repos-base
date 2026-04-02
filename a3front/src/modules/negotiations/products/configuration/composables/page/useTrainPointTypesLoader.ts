import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '../../stores/useConfigurationStore';
import { useSupportResourcesStore } from '../../stores/useSupportResourcesStore';

export const useTrainPointTypesLoader = () => {
  const configurationStore = useConfigurationStore();
  const supportResourcesStore = useSupportResourcesStore();

  const { productSupplierType } = storeToRefs(configurationStore);

  const isLoadingTrainPointTypes = ref(false);

  const loadTrainPointTypes = async () => {
    if (productSupplierType.value === null) return;

    try {
      isLoadingTrainPointTypes.value = true;

      supportResourcesStore.loadPickupPoints({
        serviceType: productSupplierType.value,
        types: ['TRAIN_STATION'],
      });
    } catch (error) {
      console.error('Error loading train point types:', error);
    } finally {
      isLoadingTrainPointTypes.value = false;
    }
  };

  return {
    loadTrainPointTypes,
    isLoadingTrainPointTypes,
  };
};
