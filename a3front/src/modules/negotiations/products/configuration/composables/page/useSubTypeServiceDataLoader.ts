import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '../../stores/useConfigurationStore';
import { useSupportResourcesStore } from '../../stores/useSupportResourcesStore';

export const useSubTypeServiceDataLoader = () => {
  const configurationStore = useConfigurationStore();
  const { productSummary, productSupplierType } = storeToRefs(configurationStore);
  const supportResourcesStore = useSupportResourcesStore();

  const isLoadingSubTypes = ref(false);

  const loadSubTypeServiceData = async () => {
    const serviceTypeId = productSummary.value?.serviceType?.id;

    if (!serviceTypeId) return;

    try {
      isLoadingSubTypes.value = true;

      await supportResourcesStore.loadSubTypes({
        serviceTypeId,
        serviceType: productSupplierType.value,
      });
    } catch (error) {
      console.error('Error loading service sub types:', error);
    } finally {
      isLoadingSubTypes.value = false;
    }
  };

  return {
    loadSubTypeServiceData,
    isLoadingSubTypes,
  };
};
