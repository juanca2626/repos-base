import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useConfigurationStore } from '../../stores/useConfigurationStore';

export const useConfigurationDataLoader = () => {
  const route = useRoute();
  const configurationStore = useConfigurationStore();
  const isLoadingConfiguration = ref(false);

  const loadConfigurationData = async () => {
    const productSupplierId = route.params.id as string;

    if (!productSupplierId) return;

    try {
      await configurationStore.loadConfiguration();
    } catch (error) {
      console.error('Error loading configuration data:', error);
    } finally {
      isLoadingConfiguration.value = false;
    }
  };

  return {
    loadConfigurationData,
    isLoadingConfiguration,
  };
};
