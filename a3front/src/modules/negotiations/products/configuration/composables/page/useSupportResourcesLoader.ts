import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '../../stores/useConfigurationStore';
import { useSupportResourcesStore } from '../../stores/useSupportResourcesStore';

export const useSupportResourcesLoader = () => {
  const configurationStore = useConfigurationStore();
  const { productSupplierType } = storeToRefs(configurationStore);
  const supportResourcesStore = useSupportResourcesStore();

  const isLoadingResources = ref(false);

  const loadSupportResources = async () => {
    try {
      isLoadingResources.value = true;

      await supportResourcesStore.loadResources({
        serviceType: productSupplierType.value,
      });
    } catch (error) {
      console.error('Error loading support resources:', error);
    } finally {
      isLoadingResources.value = false;
    }
  };

  return {
    loadSupportResources,
    isLoadingResources,
  };
};
