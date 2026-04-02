import { computed, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import {
  useProductSupplierSummaryLoader,
  useSupportResourcesLoader,
  useConfigurationDataLoader,
  useSubTypeServiceDataLoader,
  useServiceDetailsDataLoader,
  useServiceCapacityConfigurationDataLoader,
  useTrainPointTypesLoader,
} from './page';
import { useNavigationStore } from '../stores/useNavegationStore';
import { useConfigurationStore } from '../stores/useConfigurationStore';

export const useServiceConfigurationPage = () => {
  const route = useRoute();

  const configurationStore = useConfigurationStore();
  configurationStore.setProductSupplier(route.params.id as string);
  const { loading: isLoadingConfigurationStore } = storeToRefs(configurationStore);

  const navigationStore = useNavigationStore();
  const { isLoading: isLoadingNavigation } = storeToRefs(navigationStore);

  const { loadSummaryData, isLoadingSummary } = useProductSupplierSummaryLoader();

  const { loadSupportResources, isLoadingResources } = useSupportResourcesLoader();

  const { loadConfigurationData, isLoadingConfiguration } = useConfigurationDataLoader();

  const { loadSubTypeServiceData, isLoadingSubTypes } = useSubTypeServiceDataLoader();

  const { loadServiceDetailsData, isLoadingServiceDetails } = useServiceDetailsDataLoader();

  const { loadServiceCapacityConfigurationData, isLoadingCapacityConfiguration } =
    useServiceCapacityConfigurationDataLoader();

  const { loadTrainPointTypes, isLoadingTrainPointTypes } = useTrainPointTypesLoader();

  const isLoading = computed(() => {
    return (
      isLoadingConfiguration.value ||
      isLoadingResources.value ||
      isLoadingSummary.value ||
      isLoadingSubTypes.value ||
      isLoadingServiceDetails.value ||
      isLoadingCapacityConfiguration.value ||
      isLoadingTrainPointTypes.value ||
      isLoadingNavigation.value ||
      isLoadingConfigurationStore.value
    );
  });

  onMounted(async () => {
    navigationStore.clearData();
    configurationStore.clear();
    configurationStore.setProductSupplier(route.params.id as string);

    await loadSummaryData();

    await Promise.all([
      loadSupportResources(),
      loadServiceDetailsData(),
      loadServiceCapacityConfigurationData(),
      loadTrainPointTypes(), // train
    ]);

    await Promise.all([loadConfigurationData()]);

    await loadSubTypeServiceData(); // generic
  });

  onUnmounted(() => {
    configurationStore.clear();
  });

  return {
    isLoading,
  };
};
