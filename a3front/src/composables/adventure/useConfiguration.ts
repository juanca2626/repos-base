import { useConfigurationStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useConfiguration() {
  const configurationStore = useConfigurationStore();
  const { isLoading, error, getConfiguration } = storeToRefs(configurationStore);
  const { fetchAll, update } = configurationStore;

  return {
    isLoading,
    error,
    configuration: getConfiguration,
    fetchConfiguration: fetchAll,
    updateConfiguration: update,
  };
}
